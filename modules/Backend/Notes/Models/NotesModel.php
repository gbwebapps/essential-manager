<?php declare(strict_types = 1);

namespace Modules\Backend\Notes\Models;

use App\Models\Backend\BackendModel;

/** Gestisce note testuali reali senza consentire cambi di contesto tramite campi POST. */
class NotesModel extends BackendModel
{
    protected ?string $module = 'notes';
    protected bool $hasSoftDelete = true;
    protected ?string $getUUIDQuery = 'select * from notes where uuid = ? limit 1';
    protected array $toCompare = ['content'];
    protected array $addAllowedFields = ['content'];
    protected array $editAllowedFields = ['uuid', 'content'];

    /**
     * Definisce tutte le regole del contenuto, trattato come testo e non come HTML.
     * @return array<string, array> Regole per l'inserimento.
     */
    public function addValidationRules(): array
    {
        return ['content' => ['label' => lang('backend/notes.labels.content'), 'rules' => ['required', 'string', 'max_length[10000]', 'regex_match[/^[^\x00-\x08\x0B\x0C\x0E-\x1F\x7F-\x9F<>]+$/u]']]];
    }

    /**
     * Aggiunge il controllo UUID alle regole della nota.
     * @param array $posts Dati inviati; nessun valore viene interpolato nelle regole.
     * @return array<string, array> Regole per la modifica.
     */
    public function editValidationRules(array $posts): array
    {
        return ['uuid' => ['rules' => ['required', 'string', 'regex_match[/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i]']]] + $this->addValidationRules();
    }

    /**
     * Controlla tipi e whitelist anche per i payload annidati della bozza.
     * @param array $posts Contenuto ed eventuale UUID.
     * @param bool $editing True quando si aggiorna una nota esistente.
     * @return array Esito con data normalizzato oppure errors.
     */
    public function validatePayload(array $posts, bool $editing = false): array
    {
        $allowed = $editing ? $this->editAllowedFields : $this->addAllowedFields;
        $errors = [];
        if (array_diff(array_keys($posts), $allowed) !== []): $errors['payload'] = lang('backend/notes.messages.invalidData'); endif;
        $data = $this->checkAllowedFields($posts, $allowed);
        foreach ($allowed as $field):
            if (isset($data[$field]) && ! is_string($data[$field])):
                $errors[$field] = lang('backend/notes.messages.invalidData');
            elseif (isset($data[$field])):
                $data[$field] = trim($data[$field], " \t\n\r");
            endif;
        endforeach;
        if ($errors === []):
            $validator = service('validation')->reset()->setRules($editing ? $this->editValidationRules($data) : $this->addValidationRules());
            if ( ! $validator->run($data)): $errors = $validator->getErrors(); endif;
        endif;
        return $errors !== [] ? ['result' => false, 'errors' => $errors, 'message' => lang('backend/notes.messages.invalidData')] : ['result' => true, 'data' => $data];
    }

    /**
     * Carica le note attivi assegnati direttamente al proprietario. Contesto: progetto.
     * @param string $projectUuid UUID del proprietario controllato dal controller.
     * @return array Record attivi.
     */
    public function getForProject(string $projectUuid): array
    {
        return $this->getForContext('project_uuid', $projectUuid);
    }

    /**
     * Carica le note attivi assegnati direttamente al proprietario. Contesto: applicazione.
     * @param string $applicationUuid UUID del proprietario controllato dal controller.
     * @return array Record attivi.
     */
    public function getForApplication(string $applicationUuid): array
    {
        return $this->getForContext('application_uuid', $applicationUuid);
    }

    /**
     * Motore condiviso del metodo getForProject per progetto e applicazione.
     * @param string $ownerColumn Colonna interna ammessa dalla whitelist ownerTable.
     * @param string $ownerUuid UUID del proprietario.
     * @return array Risultato persistito o errore senza scritture parziali.
     */
    private function getForContext(string $ownerColumn, string $ownerUuid): array
    {
        $this->ownerTable($ownerColumn);
        return $this->db->query("select * from notes where {$ownerColumn} = ? and deleted_at is null order by created_at desc, id desc", [$ownerUuid])->getResult();
    }

    /**
     * Inserisce nota nella transazione aperta dal proprietario. Contesto: progetto.
     * @param array $data Campi normalizzati per inserimento, oppure payload da validare per salvataggio.
     * @param string $projectUuid UUID del proprietario controllato dal controller.
     * @param string|null $adminUuid Amministratore autenticato stabilito dal server.
     * @return string UUID inserito; nessun commit autonomo.
     */
    public function insertForProject(array $data, string $projectUuid, ?string $adminUuid): string
    {
        return $this->insertForContext($data, 'project_uuid', $projectUuid, $adminUuid);
    }

    /**
     * Inserisce nota nella transazione aperta dal proprietario. Contesto: applicazione.
     * @param array $data Campi normalizzati per inserimento, oppure payload da validare per salvataggio.
     * @param string $applicationUuid UUID del proprietario controllato dal controller.
     * @param string|null $adminUuid Amministratore autenticato stabilito dal server.
     * @return string UUID inserito; nessun commit autonomo.
     */
    public function insertForApplication(array $data, string $applicationUuid, ?string $adminUuid): string
    {
        return $this->insertForContext($data, 'application_uuid', $applicationUuid, $adminUuid);
    }

    /**
     * Motore condiviso del metodo insertForProject per progetto e applicazione.
     * @param string $ownerColumn Colonna interna ammessa dalla whitelist ownerTable.
     * @param string $ownerUuid UUID del proprietario.
     * @param array $data Campi già normalizzati dal validatore; partecipa alla transazione del chiamante.
     * @param string|null $adminUuid Amministratore autenticato.
     * @return string Risultato persistito o errore senza scritture parziali.
     */
    private function insertForContext(array $data, string $ownerColumn, string $ownerUuid, ?string $adminUuid): string
    {
        $this->ownerTable($ownerColumn);
        $uuid = $this->generateUUID();
        $this->execute("insert into notes (uuid, {$ownerColumn}, content, created_by_admin_uuid, created_at) values (?, ?, ?, ?, ?)", [$uuid, $ownerUuid, $data['content'], $adminUuid, date('Y-m-d H:i:s')]);
        return $uuid;
    }

    /**
     * Valida e salva nota verificando appartenenza e stato del proprietario. Contesto: progetto.
     * @param array $posts Campi normalizzati per inserimento, oppure payload da validare per salvataggio.
     * @param string $projectUuid UUID del proprietario controllato dal controller.
     * @param string|null $adminUuid Amministratore autenticato stabilito dal server.
     * @return array Esito e record persistito oppure errori.
     */
    public function saveForProject(array $posts, string $projectUuid, ?string $adminUuid): array
    {
        return $this->saveForContext($posts, 'project_uuid', $projectUuid, $adminUuid);
    }

    /**
     * Valida e salva nota verificando appartenenza e stato del proprietario. Contesto: applicazione.
     * @param array $posts Campi normalizzati per inserimento, oppure payload da validare per salvataggio.
     * @param string $applicationUuid UUID del proprietario controllato dal controller.
     * @param string|null $adminUuid Amministratore autenticato stabilito dal server.
     * @return array Esito e record persistito oppure errori.
     */
    public function saveForApplication(array $posts, string $applicationUuid, ?string $adminUuid): array
    {
        return $this->saveForContext($posts, 'application_uuid', $applicationUuid, $adminUuid);
    }

    /**
     * Motore condiviso del metodo saveForProject per progetto e applicazione.
     * @param string $ownerColumn Colonna interna ammessa dalla whitelist ownerTable.
     * @param string $ownerUuid UUID del proprietario.
     * @param array $posts Payload non attendibile validato prima delle scritture.
     * @param string|null $adminUuid Amministratore autenticato.
     * @return array Risultato persistito o errore senza scritture parziali.
     */
    private function saveForContext(array $posts, string $ownerColumn, string $ownerUuid, ?string $adminUuid): array
    {
        $this->ownerTable($ownerColumn);
        if (array_key_exists('uuid', $posts) && ! is_string($posts['uuid'])): return ['result' => false, 'errors' => ['uuid' => lang('backend/notes.messages.invalidData')], 'message' => lang('backend/notes.messages.invalidData')]; endif;
        $editing = is_string($posts['uuid'] ?? null) && $posts['uuid'] !== '';
        if ( ! $editing): unset($posts['uuid']); endif;
        $validated = $this->validatePayload($posts, $editing);
        if ( ! $validated['result']): return $validated; endif;
        $data = $validated['data'];
        try {
            $this->db->transBegin();
            $this->lockNoteContext($ownerColumn,$ownerUuid);
            if ($editing):
                $row = $this->db->query("select * from notes where uuid = ? and {$ownerColumn} = ? and deleted_at is null for update", [$data['uuid'], $ownerUuid])->getRow();
                if ( ! $row): throw new \DomainException(lang('backend/notes.messages.notFound')); endif;
                $uuid = $data['uuid'];
                if ( ! $this->hasDataChanged($data, $row)):
                    $this->db->transCommit();
                    return ['result' => false, 'changed' => false, 'message' => lang('backend/notes.messages.noDataChanged')];
                endif;
                $this->execute("update notes set content = ?, updated_at = ? where uuid = ? and {$ownerColumn} = ?", [$data['content'], date('Y-m-d H:i:s'), $uuid, $ownerUuid]);
            else:
                $uuid = $this->insertForContext($data, $ownerColumn, $ownerUuid, $adminUuid);
            endif;
            if ( ! $this->db->transStatus()): throw new \RuntimeException('Note transaction failed.'); endif;
            $record = parent::getByUUID($uuid);
            if ( ! $record['result']): throw new \RuntimeException('Saved note not available.'); endif;
            $this->db->transCommit();
            return ['result' => true, 'record' => $record['row'], 'message' => lang('backend/notes.messages.saveSuccess')];
        } catch (\Throwable $exception) {
            $this->db->transRollback();
            $this->db->resetTransStatus();
            log_message('error', 'Notes::saveForProject - ' . $exception->getMessage());
            return ['result' => false, 'message' => $exception instanceof \DomainException ? $exception->getMessage() : lang('backend/notes.messages.saveError')];
        }
    }

    /**
     * @param string $uuid Task autorizzato.
     * @return array Note attive del task.
     */
    public function getForTask(string $uuid): array { return $this->getForContext('task_uuid',$uuid); }
    /**
     * @param string $uuid Passo autorizzato.
     * @return array Note attive del passo.
     */
    public function getForStep(string $uuid): array { return $this->getForContext('step_uuid',$uuid); }
    /**
     * @param array $data Contenuto validato.
     * @param string $uuid Task.
     * @param string|null $admin Identità server.
     * @return string UUID inserito nella transazione del task.
     */
    public function insertForTask(array $data,string $uuid,?string $admin): string { return $this->insertForContext($data,'task_uuid',$uuid,$admin); }
    /**
     * @param array $posts Contenuto e UUID.
     * @param string $uuid Task.
     * @param string|null $admin Identità server.
     * @return array Esito con confronto modifiche.
     */
    public function saveForTask(array $posts,string $uuid,?string $admin): array { return $this->saveForContext($posts,'task_uuid',$uuid,$admin); }
    /**
     * @param array $posts Contenuto e UUID.
     * @param string $uuid Passo.
     * @param string|null $admin Identità server.
     * @return array Esito con appartenenza verificata.
     */
    public function saveForStep(array $posts,string $uuid,?string $admin): array { return $this->saveForContext($posts,'step_uuid',$uuid,$admin); }

    /**
     * @param string $taskUuid Task leggibile dal chiamante.
     * @return array Note del task e dei suoi passi, incluse quelle nel cestino.
     */
    public function getTaskNotes(string $taskUuid): array
    {
        return $this->db->query('select notes.*,step.title as step_title from notes left join task_steps step on step.uuid=notes.step_uuid where notes.task_uuid=? or step.task_uuid=? order by notes.created_at desc,notes.id desc',[$taskUuid,$taskUuid])->getResult();
    }

    /**
     * Elimina definitivamente una nota solo se appartiene al task autorizzato o a un suo passo.
     * @param string $uuid UUID della nota.
     * @param string $taskUuid Contesto verificato dal controller.
     * @return array Esito atomico senza cancellazioni di altri contesti.
     */
    public function deleteTaskNote(string $uuid, string $taskUuid): array
    {
        try {
            $this->db->transBegin();
            model(\Modules\Backend\Tasks\Models\TasksModel::class)->lockTask($taskUuid, false);
            $row = $this->db->query('select uuid from notes where uuid=? and (task_uuid=? or step_uuid in (select uuid from task_steps where task_uuid=?)) for update', [$uuid, $taskUuid, $taskUuid])->getRow();
            if ( ! $row): throw new \DomainException(lang('backend/notes.messages.notFound')); endif;
            $this->execute('delete from notes where uuid=?', [$uuid]);
            if ( ! $this->db->transStatus()): throw new \RuntimeException('Note deletion failed.'); endif;
            $this->db->transCommit();
            return ['result'=>true, 'uuid'=>$uuid, 'message'=>lang('backend/tasks.messages.noteDeleted')];
        } catch (\Throwable $error) {
            $this->db->transRollback(); $this->db->resetTransStatus();
            log_message('error', 'Task note deletion - '.$error->getMessage());
            return ['result'=>false, 'message'=>$error instanceof \DomainException ? $error->getMessage() : lang('backend/notes.messages.saveError')];
        }
    }

    /**
     * @param string $column Contesto interno ammesso.
     * @param string $uuid Proprietario.
     * @return void Blocca task e passo nella stessa transazione della nota.
     */
    private function lockNoteContext(string $column,string $uuid): void
    {
        if ($column==='task_uuid'): model(\Modules\Backend\Tasks\Models\TasksModel::class)->lockTask($uuid);return; endif;
        if ($column==='step_uuid'):
            $step=$this->db->query('select task_uuid from task_steps where uuid=?',[$uuid])->getRow();
            if ( ! $step): throw new \DomainException(lang('backend/notes.messages.notFound')); endif;
            model(\Modules\Backend\Tasks\Models\TasksModel::class)->lockTask($step->task_uuid);
            if ( ! $this->db->query('select uuid from task_steps where uuid=? and task_uuid=? for update',[$uuid,$step->task_uuid])->getRow()): throw new \DomainException(lang('backend/notes.messages.notFound')); endif;
            return;
        endif;
        $table=$this->ownerTable($column);
        if ( ! $this->db->query("select uuid from {$table} where uuid=? and deleted_at is null for update",[$uuid])->getRow()): throw new \DomainException(lang('backend/notes.messages.projectUnavailable')); endif;
    }

    /**
     * Traduce soltanto le colonne di contesto definite dal codice nei rispettivi proprietari.
     * @param string $column project_uuid oppure application_uuid, mai un identificatore libero dal client.
     * @return string Nome statico della tabella del proprietario.
     * @throws \InvalidArgumentException Se il chiamante interno usa un contesto non previsto.
     */
    private function ownerTable(string $column): string
    {
        return match ($column) {
            'project_uuid' => 'projects',
            'application_uuid' => 'applications',
            'task_uuid' => 'tasks',
            'step_uuid' => 'task_steps',
            default => throw new \InvalidArgumentException('Invalid work owner.'),
        };
    }

    /**
     * Esegue una query preparata o interrompe la transazione in caso di errore.
     * @param string $sql Query con placeholder.
     * @param array $parameters Valori da associare.
     * @return void
     */
    private function execute(string $sql, array $parameters): void
    {
        if ($this->db->query($sql, $parameters) === false): throw new \RuntimeException('Note query failed.'); endif;
    }
}
