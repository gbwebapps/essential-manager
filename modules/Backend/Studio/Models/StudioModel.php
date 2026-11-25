<?php declare(strict_types = 1);

namespace Modules\Backend\Studio\Models;

use App\Models\Backend\BackendModel;
use Modules\Backend\Tasks\Models\TasksModel;

/** Letture del contesto Studio; scritture affidate ai model comuni Tasks e Notes. */
class StudioModel extends BackendModel
{
    public const PAGE_SIZE = 10;
    protected ?string $module = 'studio';

    /** @param bool $archived Seleziona il solo archivio. @return int Numero dei task della sezione, indipendente dalla ricerca. */
    public function taskCount(bool $archived = false): int
    {
        return $this->db->table('tasks')->where('is_studio', 1)->where('deleted_at', null)->where('archived_at ' . ($archived ? 'is not null' : 'is null'), null, false)->countAllResults();
    }

    /** @return int Numero totale delle note autonome attive. */
    public function noteCount(): int
    {
        return $this->db->table('notes')->where('is_studio', 1)->where('deleted_at', null)->countAllResults();
    }

    /**
     * Valida lo stato della lista prima di qualsiasi lettura o mutazione associata.
     * @param mixed $payload JSON ricevuto dal browser oppure stato interno in array.
     * @return array Stato normalizzato o errori aggregati. Colonne, contesto e limite non provengono dal client.
     */
    public function validateList(mixed $payload): array
    {
        if (is_string($payload) && strlen($payload) <= 4096):
            try { $payload = json_decode($payload, true, 8, JSON_THROW_ON_ERROR); }
            catch (\JsonException) { $payload = null; }
        endif;
        if ( ! is_array($payload)):
            return ['result'=>false, 'errors'=>['list'=>lang('backend/tasks.messages.invalidData')]];
        endif;
        $errors = [];
        if (array_diff(array_keys($payload), ['page','search','direction','opened','archive'])):
            $errors['list'] = lang('backend/tasks.messages.invalidData');
        endif;
        $validation = service('validation');
        $validation->reset();
        $validation->setRules([
            'page'=>['rules'=>['required','is_natural_no_zero','less_than_equal_to[1000000]']],
            'search'=>['rules'=>['permit_empty','string','max_length[150]']],
            'direction'=>['rules'=>['required','string','in_list[asc,desc]']],
            'archive'=>['rules'=>['required','string','in_list[active,archived]']],
        ]);
        $payload['archive'] = $payload['archive'] ?? 'active';
        $fields=$payload;
        foreach (['page','search','direction','archive'] as $field):
            if (array_key_exists($field,$fields) && ! is_string($fields[$field]) && ! ($field==='page' && is_int($fields[$field]))):
                $errors[$field]=lang('backend/tasks.messages.invalidData'); $fields[$field]='';
            endif;
        endforeach;
        if ( ! $validation->run($fields)): $errors += $validation->getErrors(); endif;
        // La ricerca non accetta markup, delimitatori o caratteri esclusi dalla UI; SQL resta parametrizzato.
        $search = html_entity_decode($fields['search'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if (preg_match('/[<>\x22\x25\x27\x5C\x60\x00-\x1F\x7F]/u', $search) !== 0):
            $errors['search'] = lang('backend/studio.invalidSearch');
        endif;
        if (isset($payload['search']) && ! is_string($payload['search'])): $errors['search']=lang('backend/tasks.messages.invalidData'); endif;
        $opened = $payload['opened'] ?? [];
        if ( ! is_array($opened) || ! array_is_list($opened) || count($opened) > self::PAGE_SIZE):
            $errors['opened'] = lang('backend/tasks.messages.invalidData');
        else:
            foreach ($opened as $uuid):
                if ( ! is_string($uuid) || ! preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/iD', $uuid)):
                    $errors['opened'] = lang('backend/tasks.messages.invalidData'); break;
                endif;
            endforeach;
        endif;
        if ($errors): return ['result'=>false,'errors'=>$errors]; endif;
        return ['result'=>true,'state'=>['page'=>(int)$payload['page'], 'search'=>trim($payload['search'] ?? ''), 'direction'=>$payload['direction'], 'archive'=>$payload['archive'], 'opened'=>array_values(array_unique($opened))]];
    }

    /**
     * Ricerca letterale nel titolo dei task o nel contenuto delle note e ordina per creazione.
     * @param string $kind tasks oppure notes, imposto dal controller.
     * @param array $state Stato già validato da validateList().
     * @return array Record della pagina, stato corretto dopo eventuali cancellazioni e dati del pager comune.
     */
    public function listing(string $kind, array $state): array
    {
        if ( ! in_array($kind, ['tasks','notes'], true)): throw new \InvalidArgumentException('Invalid Studio list.'); endif;
        $query=$this->db->table($kind)->where('is_studio',1)->where('deleted_at',null);
        if ($kind === 'tasks'): $query->where('archived_at ' . (($state['archive'] ?? 'active') === 'archived' ? 'is not null' : 'is null'), null, false); endif;
        if ($state['search'] !== ''): $query->like($kind==='tasks'?'title':'content', $state['search']); endif;
        $total=$query->countAllResults(false);
        $state['page']=min($state['page'],max(1,(int)ceil($total/self::PAGE_SIZE)));
        $query->select($kind . '.*');
        if ($kind==='tasks'):
            $query->select('(select count(*) from task_steps where task_uuid=tasks.uuid) step_count,
                (select count(*) from task_steps where task_uuid=tasks.uuid and completed_at is not null) completed_count,
                (select count(*) from notes n where n.deleted_at is null and (n.task_uuid=tasks.uuid or n.step_uuid in (select uuid from task_steps where task_uuid=tasks.uuid))) note_count', false);
        endif;
        $records=$query->orderBy('created_at',$state['direction'])->orderBy('id',$state['direction'])->get(self::PAGE_SIZE,($state['page']-1)*self::PAGE_SIZE)->getResult();
        if ($kind === 'tasks'): model(StudioReferencesModel::class)->attach($records); endif;
        return ['records'=>$records, 'state'=>$state, 'pagination'=>['page'=>$state['page'],'limit'=>self::PAGE_SIZE,'totalRows'=>$total]];
    }

    /** @return array Conteggi del solo Studio, indipendenti dalla ricerca della lista. */
    public function overview(): array
    {
        return array_map('intval', $this->db->query("select count(*) total,
            coalesce(sum(status<>'completed'),0) open, coalesce(sum(status='in_progress'),0) in_progress,
            coalesce(sum((select count(*) from task_steps where task_uuid=tasks.uuid and completed_at is null)),0) remaining_steps
            from tasks where is_studio=1 and deleted_at is null and archived_at is null")->getRowArray());
    }

    /** @param string $uuid UUID validato. @return object|null Task attivo appartenente esclusivamente allo Studio. */
    public function getTask(string $uuid): ?object
    {
        $result=model(TasksModel::class)->getByUUID($uuid);
        return $result['result'] && (int)$result['row']->is_studio===1 && $result['row']->deleted_at===null ? $result['row'] : null;
    }

    /**
     * Riusa le cancellazioni comuni in una sola transazione dopo aver verificato il contesto.
     * @param string $uuid UUID del task verificato dal controller.
     * @return array Esito definitivo; i riferimenti operativi continuano a bloccare la cancellazione.
     */
    public function deleteTask(string $uuid): array
    {
        try {
            $this->db->transBegin();
            $tasks=model(TasksModel::class);
            $row=$this->db->query('select uuid from tasks where uuid=? and is_studio=1 and deleted_at is null for update',[$uuid])->getRow();
            if ( ! $row): throw new \DomainException(lang('backend/studio.notFound')); endif;
            foreach (['softDelete','hardDelete'] as $action):
                $result=$tasks->changeDeletion($uuid,$action);
                if ( ! $result['result']): $this->db->transRollback(); return $result; endif;
            endforeach;
            if ( ! $this->db->transStatus()): throw new \RuntimeException('Studio task deletion failed.'); endif;
            $this->db->transCommit();
            return $result;
        } catch (\Throwable $error) {
            $this->db->transRollback(); $this->db->resetTransStatus();
            log_message('error','Studio::deleteTask - '.$error->getMessage());
            return ['result'=>false,'message'=>$error instanceof \DomainException ? $error->getMessage() : lang('backend/tasks.messages.saveError')];
        }
    }
}
