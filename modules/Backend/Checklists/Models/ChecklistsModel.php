<?php declare(strict_types = 1);

namespace Modules\Backend\Checklists\Models;

use App\Models\Backend\BackendModel;

/**
 * Modelli riutilizzabili di passi; nessuna modifica retroattiva ai task che li hanno copiati.
 */
class ChecklistsModel extends BackendModel
{
    protected ?string $module = 'checklists';
    protected bool $hasSoftDelete = true;
    protected array $showAllAllowedFields = ['column', 'order', 'page', 'rows', 'searchFields', 'searchDates', 'trash_filter'];
    protected array $showAllSearchAllowedFields = ['name'];
    protected array $showAllSearchAllowedDates = ['created_at', 'updated_at'];
    protected array $allowedOrderColumns = ['id', 'name', 'created_at', 'updated_at'];
    protected array $toCompare = ['name', 'description'];

    /**
     * @return void Prepara le query statiche consumate dal motore globale BackendModel.
     */
    protected function initModel(): void
    {
        parent::initModel();
        $select = 'select checklists.*, (select count(*) from checklist_items where checklist_uuid = checklists.uuid) as step_count from checklists';
        $this->getDataQuery = $select . ' where 1 = 1';
        $this->getNumRowsQuery = 'select count(*) as count from checklists where 1 = 1';
        $this->getUUIDQuery = $select . ' where checklists.uuid = ? limit 1';
    }

    /**
     * Definisce le regole di paginazione, ordinamento e cestino, inclusi i select del form.
     * @return array<string,array> Regole per tutti i parametri della lista.
     */
    public function showAllValidationRules(): array
    {
        return [
            'column' => ['rules' => ['required', 'string', 'in_list[id,name,created_at,updated_at]']],
            'order' => ['rules' => ['required', 'string', 'in_list[asc,desc]']],
            'page' => ['rules' => ['required', 'is_natural_no_zero', 'less_than_equal_to[1000000]']],
            'rows' => ['rules' => ['required', 'in_list[5,10,15,20]']],
            'trash_filter' => ['rules' => ['required', 'string', 'in_list[active,trashed,all]']],
        ];
    }

    /**
     * Definisce le regole dei campi testuali e di ogni estremo delle date di ricerca.
     * @return array<string,array> Regole dei filtri ammessi.
     */
    public function showAllSearchValidationRules(): array
    {
        $rules = ['searchFields.name' => ['rules' => ['permit_empty', 'string', 'max_length[255]']]];
        foreach (['created_at-from', 'created_at-to', 'updated_at-from', 'updated_at-to'] as $field):
            $rules['searchDates.' . $field] = ['rules' => ['permit_empty', 'string', 'valid_date[Y-m-d H:i:s]']];
        endforeach;
        return $rules;
    }

    /**
     * Valida tipi e whitelist dei filtri prima di usare il motore globale getData.
     * @param array $posts Parametri non attendibili inviati dalla lista.
     * @return array Esito con data normalizzato oppure errors.
     */
    public function validateList(array $posts): array
    {
        $data = $this->checkAllowedFields($posts, $this->showAllAllowedFields);
        $data += ['column' => 'id', 'order' => 'desc', 'page' => '1', 'rows' => '5', 'trash_filter' => 'active', 'searchFields' => [], 'searchDates' => []];
        foreach (['column', 'order', 'page', 'rows', 'trash_filter'] as $field):
            if ( ! is_string($data[$field]) && ! is_int($data[$field])): return $this->validationFailure([$field => lang('backend/checklists.messages.invalidData')]); endif;
        endforeach;
        foreach (['searchFields' => ['name'], 'searchDates' => ['created_at-from', 'created_at-to', 'updated_at-from', 'updated_at-to']] as $field => $allowed):
            if ( ! is_array($data[$field]) || array_diff(array_keys($data[$field]), $allowed) !== []): return $this->validationFailure([$field => lang('backend/checklists.messages.invalidData')]); endif;
            foreach ($data[$field] as $key => $value):
                if ( ! is_string($value)): return $this->validationFailure([$field . '.' . $key => lang('backend/checklists.messages.invalidData')]); endif;
            endforeach;
        endforeach;
        $validator = service('validation')->reset()->setRules($this->showAllValidationRules() + $this->showAllSearchValidationRules());
        if ( ! $validator->run($data)): return $this->validationFailure($validator->getErrors()); endif;
        foreach (['created_at', 'updated_at'] as $date):
            if ( ! empty($data['searchDates'][$date . '-from']) && ! empty($data['searchDates'][$date . '-to']) && $data['searchDates'][$date . '-from'] > $data['searchDates'][$date . '-to']): return $this->validationFailure(['searchDates.' . $date . '-to' => lang('backend/checklists.messages.invalidDateRange')]); endif;
        endforeach;
        return ['result' => true, 'data' => $data];
    }

    /**
     * Valida l'identificativo di lettura, eliminazione, ripristino e ricarica.
     * @return array<string,array> Regole del campo UUID.
     */
    public function delValidationRules(): array
    {
        return ['uuid' => ['rules' => ['required', 'string', 'regex_match[/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i]']]];
    }

    /**
     * Conta i checklist attivi e cestinati, indipendentemente dalla ricerca corrente.
     * @return array{active:int,trashed:int,all:int} Conteggi non filtrati dalla ricerca.
     */
    public function getTrashCounts(): array
    {
        $row = $this->db->query('select count(*) as total, count(case when deleted_at is not null then 1 end) as trashed from checklists where 1 = 1')->getRow();
        return ['active' => (int) $row->total - (int) $row->trashed, 'trashed' => (int) $row->trashed, 'all' => (int) $row->total];
    }

    /**
     * Conta i checklist attivi per la navigazione di Manager.
     * @return int Numero di checklist attivi.
     */
    public function activeCount(): int
    {
        return (int) $this->db->query('select count(*) as total from checklists where deleted_at is null')->getRow()->total;
    }

    /**
     * Esegue una query preparata verificandone anche il risultato booleano.
     * @param string $sql SQL con placeholder.
     * @param array $parameters Valori da associare ai placeholder.
     * @return void
     */
    private function execute(string $sql, array $parameters): void
    {
        if ($this->db->query($sql, $parameters) === false): throw new \RuntimeException('Checklist query failed.'); endif;
    }

    /**
     * Verifica il formato degli identificativi prima di usarli nelle regole o nelle query.
     * @param string $uuid Identificativo ricevuto.
     * @return bool True per un UUID valido.
     */
    private function validUuid(string $uuid): bool
    {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $uuid) === 1;
    }

    /**
     * Uniforma gli errori di validazione del checklist.
     * @param array $errors Errori indicizzati per campo, anche annidato.
     * @return array{result:false,errors:array,message:string} Esito non salvabile.
     */
    private function validationFailure(array $errors): array
    {
        return ['result' => false, 'errors' => $errors, 'message' => lang('backend/checklists.messages.validationErrors')];
    }

    /**
     * @return array Regole dei campi visibili, senza vincoli HTML o dati di sistema editabili.
     */
    public function addValidationRules(): array
    {
        return [
            'name' => ['label' => lang('backend/checklists.labels.name'), 'rules' => ['required', 'string', 'max_length[100]', 'regex_match[/^(?=.*[\p{L}\p{N}])[^\x00-\x1F\x7F-\x9F<>]+$/u]', 'is_unique[checklists.name]']],
            'description' => ['label' => lang('backend/checklists.labels.description'), 'rules' => ['permit_empty', 'string', 'max_length[10000]', 'regex_match[/^[^\x00-\x08\x0B\x0C\x0E-\x1F\x7F-\x9F<>]*$/u]']],
        ];
    }

    /**
     * @param array $posts Form ricevuto.
     * @return array Regole con unicità riferita a un UUID già verificato.
     */
    public function editValidationRules(array $posts): array
    {
        $rules = $this->addValidationRules();
        $uuid = is_string($posts['uuid'] ?? null) && $this->validUuid($posts['uuid']) ? $posts['uuid'] : '';
        $rules['name']['rules'][4] = 'is_unique[checklists.name,uuid,' . $uuid . ']';
        return $this->delValidationRules() + $rules;
    }

    /**
     * Aggrega gli errori di tutti i campi e dei passi prima di avviare scritture.
     * @param array $posts Form non attendibile.
     * @param bool $editing True per modifica; accetta solo UUID dei passi persistiti.
     * @return array Dati normalizzati oppure errori indicizzati.
     */
    public function validatePayload(array $posts, bool $editing = false): array
    {
        $errors = [];
        $allowed = $editing ? ['uuid', 'name', 'description', 'steps'] : ['name', 'description', 'steps'];
        if (array_diff(array_keys($posts), $allowed)): $errors['payload'] = lang('backend/checklists.messages.invalidData'); endif;
        $data = array_intersect_key($posts, array_flip($allowed));
        foreach (['name', 'description'] as $field):
            if (isset($data[$field]) && ! is_string($data[$field])): $errors[$field] = lang('backend/checklists.messages.invalidData'); $data[$field] = ''; endif;
            $data[$field] = trim($data[$field] ?? '');
        endforeach;
        $validator = service('validation')->reset()->setRules($editing ? $this->editValidationRules($data) : $this->addValidationRules());
        if ( ! $validator->run($data)): $errors += $validator->getErrors(); endif;
        if (($data['steps'] ?? null) === []): $errors['steps'] = lang('backend/checklists.messages.stepsRequired'); endif;
        // I passi usano gli stessi limiti e le stesse regole del task che li riceverà.
        $stepPayload = ['title' => 'Checklist', 'description' => '', 'status' => 'todo', 'steps' => $data['steps'] ?? null];
        if ($editing): $stepPayload['uuid'] = $data['uuid'] ?? ''; endif;
        $checked = model(\Modules\Backend\Tasks\Models\TasksModel::class)->validatePayload($stepPayload, $editing);
        // Riusa le regole dei passi senza introdurre campi di stato nella checklist.
        if ( ! $checked['result']):
            foreach ($checked['errors'] as $field => $error): if ($field === 'steps' || str_starts_with($field, 'steps.')): $errors[$field] = $error; endif; endforeach;
        endif;
        if ($errors): return $this->validationFailure($errors); endif;
        $data['steps'] = array_map(static fn(array $step): array => ['uuid' => trim($step['uuid'] ?? ''), 'title' => trim($step['title'])], $data['steps']);
        return ['result' => true, 'data' => $data];
    }

    /**
     * @param string $uuid Identificativo validato.
     * @return array Record con passi ordinati.
     */
    public function getByUUID(string $uuid): array
    {
        $result = parent::getByUUID($uuid);
        if ($result['result']): $result['row']->steps = $this->db->query('select uuid, title, position from checklist_items where checklist_uuid = ? order by position', [$uuid])->getResult(); endif;
        return $result;
    }

    /**
     * @return array Modelli attivi disponibili; il controller/vista verifica checklists_apply.
     */
    public function options(): array
    {
        return $this->db->query('select uuid, name from checklists where deleted_at is null order by name')->getResult();
    }

    /**
     * @param array $posts Form completo.
     * @param string|null $adminUuid Identità server.
     * @return array Esito atomico.
     */
    public function add(array $posts, ?string $adminUuid = null): array
    {
        return $this->save($posts, false, $adminUuid);
    }

    /**
     * @param array $posts Form completo.
     * @return array Esito o nessuna modifica, inclusi ordine e contenuto dei passi.
     */
    public function edit(array $posts): array
    {
        return $this->save($posts, true, null);
    }

    /**
     * Salva intestazione e passi nella stessa transazione preservando gli UUID durante il riordino.
     * @param array $posts Dati non attendibili.
     * @param bool $editing Modalità stabilita dal chiamante.
     * @param string|null $adminUuid Autore autenticato.
     * @return array Esito con UUID o errori; nessuna scrittura parziale.
     */
    private function save(array $posts, bool $editing, ?string $adminUuid): array
    {
        $checked = $this->validatePayload($posts, $editing);
        if ( ! $checked['result']): return $checked; endif;
        $data = $checked['data'];
        try {
            $this->db->transBegin();
            $uuid = $editing ? $data['uuid'] : $this->generateUUID();
            $now = date('Y-m-d H:i:s');
            $existing = [];
            if ($editing):
                $row = $this->lockChecklist($uuid);
                if ($row->deleted_at !== null): throw new \DomainException(lang('backend/checklists.messages.cannotModifyDeleted')); endif;
                $existing = $this->db->query('select uuid, title from checklist_items where checklist_uuid = ? order by position for update', [$uuid])->getResult();
                $byUuid = array_column($existing, null, 'uuid');
                $stepsChanged = count($existing) !== count($data['steps']);
                foreach ($data['steps'] as $index => $step):
                    if ($step['uuid'] !== '' && ! isset($byUuid[$step['uuid']])): return $this->abortInvalidStep(); endif;
                    if (($existing[$index]->uuid ?? '') !== $step['uuid'] || ($existing[$index]->title ?? '') !== $step['title']): $stepsChanged = true; endif;
                endforeach;
                if ( ! $this->hasDataChanged($data, $row) && ! $stepsChanged):
                    $this->db->transCommit();
                    return ['result' => false, 'changed' => false, 'message' => lang('backend/checklists.messages.noDataChanged')];
                endif;
                $this->execute('update checklists set name = ?, description = ?, updated_at = ? where uuid = ?', [$data['name'], $data['description'] !== '' ? $data['description'] : null, $now, $uuid]);
            else:
                $this->execute('insert into checklists (uuid, name, description, created_by_admin_uuid, created_at) values (?, ?, ?, ?, ?)', [$uuid, $data['name'], $data['description'] !== '' ? $data['description'] : null, $adminUuid, $now]);
            endif;
            $retained = array_column($data['steps'], 'uuid');
            foreach ($existing as $step):
                if (in_array($step->uuid, $retained, true)): continue; endif;
                // Eventuali vecchi riferimenti restano solo provenienza: non devono vincolare le copie.
                $this->execute('update task_steps set checklist_item_uuid = null where checklist_item_uuid = ?', [$step->uuid]);
                $this->execute('delete from checklist_items where uuid = ? and checklist_uuid = ?', [$step->uuid, $uuid]);
            endforeach;
            $this->execute('update checklist_items set position = position + 1000 where checklist_uuid = ?', [$uuid]);
            foreach ($data['steps'] as $index => $step):
                if ($step['uuid'] !== ''):
                    $this->execute('update checklist_items set title = ?, position = ?, updated_at = ? where uuid = ? and checklist_uuid = ?', [$step['title'], $index + 1, $now, $step['uuid'], $uuid]);
                else:
                    $this->execute('insert into checklist_items (uuid, checklist_uuid, title, position, created_at) values (?, ?, ?, ?, ?)', [$this->generateUUID(), $uuid, $step['title'], $index + 1, $now]);
                endif;
            endforeach;
            if ( ! $this->db->transStatus()): throw new \RuntimeException('Checklist transaction failed.'); endif;
            $this->db->transCommit();
            return ['result' => true, 'uuid' => $uuid, 'message' => lang('backend/checklists.messages.' . ($editing ? 'editSuccess' : 'addSuccess'))];
        } catch (\Throwable $error) { return $this->rollback($error); }
    }

    /**
     * @return array Errore di appartenenza, con rollback prima di restituire errori di campo.
     */
    private function abortInvalidStep(): array
    {
        $this->db->transRollback();
        return $this->validationFailure(['steps' => lang('backend/tasks.messages.foreignStep')]);
    }

    /**
     * @param array $posts UUID verificato.
     * @return array Esito del cestino.
     */
    public function softDelete(array $posts): array { return $this->changeDeletion($posts, 'softDelete'); }
    /**
     * @param array $posts UUID verificato.
     * @return array Esito del ripristino.
     */
    public function restoreDelete(array $posts): array { return $this->changeDeletion($posts, 'restoreDelete'); }
    /**
     * @param array $posts UUID verificato.
     * @return array Esito definitivo; i task già creati restano indipendenti.
     */
    public function hardDelete(array $posts): array { return $this->changeDeletion($posts, 'hardDelete'); }

    /**
     * @param array $posts UUID ricevuto.
     * @param string $action Azione interna.
     * @return array Transizione atomica del modello.
     */
    private function changeDeletion(array $posts, string $action): array
    {
        if (array_diff(array_keys($posts), ['uuid']) || ! is_string($posts['uuid'] ?? null) || ! $this->validUuid($posts['uuid'])): return $this->validationFailure(['uuid' => lang('backend/checklists.messages.invalidData')]); endif;
        try {
            $this->db->transBegin();
            $row = $this->lockChecklist($posts['uuid']);
            $now = date('Y-m-d H:i:s');
            if ($action === 'softDelete'):
                if ($row->deleted_at !== null): throw new \DomainException(lang('backend/checklists.messages.cannotModifyDeleted')); endif;
                $this->execute('update checklists set deleted_at = ?, updated_at = ? where uuid = ?', [$now, $now, $row->uuid]);
            else:
                if ($row->deleted_at === null): throw new \DomainException(lang('backend/checklists.messages.notTrashed')); endif;
                if ($action === 'restoreDelete'):
                    $this->execute('update checklists set deleted_at = null, restored_at = ?, updated_at = ? where uuid = ?', [$now, $now, $row->uuid]);
                else:
                    $this->execute('update task_steps set checklist_item_uuid = null where checklist_item_uuid in (select uuid from checklist_items where checklist_uuid = ?)', [$row->uuid]);
                    $this->execute('delete from checklist_items where checklist_uuid = ?', [$row->uuid]);
                    $this->execute('delete from checklists where uuid = ?', [$row->uuid]);
                endif;
            endif;
            if ( ! $this->db->transStatus()): throw new \RuntimeException('Checklist deletion failed.'); endif;
            $this->db->transCommit();
            return ['result' => true, 'uuid' => $row->uuid, 'name' => $row->name, 'message' => lang('backend/checklists.messages.' . $action . 'Success')];
        } catch (\Throwable $error) { return $this->rollback($error); }
    }

    /**
     * @param string $uuid UUID validato.
     * @return object Riga bloccata nella transazione chiamante.
     */
    private function lockChecklist(string $uuid): object
    {
        $row = $this->db->query('select * from checklists where uuid = ? for update', [$uuid])->getRow();
        if ( ! $row): throw new \DomainException(lang('backend/checklists.messages.notFound')); endif;
        return $row;
    }

    /**
     * @param \Throwable $error Errore originale.
     * @return array Errore pubblico senza dettagli SQL, dopo rollback.
     */
    private function rollback(\Throwable $error): array
    {
        $this->db->transRollback(); $this->db->resetTransStatus();
        log_message('error', 'Checklists - ' . $error->getMessage());
        return ['result' => false, 'message' => $error instanceof \DomainException ? $error->getMessage() : lang('backend/checklists.messages.saveError')];
    }
}
