<?php declare(strict_types = 1);

namespace Modules\Backend\Projects\Models;

use App\Models\Backend\BackendModel;
use Modules\Backend\Tasks\Models\TasksModel;
use Modules\Backend\Notes\Models\NotesModel;

/** Persistenza dei progetti e della bozza iniziale, separata dal backbone. */
class ProjectsModel extends BackendModel
{
    protected ?string $module = 'projects';
    protected bool $hasSoftDelete = true;
    protected array $showAllAllowedFields = ['column', 'order', 'page', 'rows', 'searchFields', 'searchDates', 'trash_filter'];
    protected array $showAllSearchAllowedFields = ['name'];
    protected array $showAllSearchAllowedDates = ['created_at', 'updated_at'];
    protected array $allowedOrderColumns = ['id', 'name', 'created_at', 'updated_at'];
    protected array $addAllowedFields = ['name', 'description', 'parent_project_uuid', 'local_path', 'local_url', 'production_url', 'execute_name', 'tasks', 'notes'];
    protected array $editAllowedFields = ['uuid', 'name', 'description', 'parent_project_uuid', 'local_path', 'local_url', 'production_url', 'execute_name'];
    protected array $toCompare = ['name', 'description', 'parent_project_uuid', 'local_path', 'local_url', 'production_url', 'execute_name'];
    protected TasksModel $tasksModel;
    protected NotesModel $notesModel;

    /**
     * Inizializza connessione, query del BackendModel e modelli dei dati collegati.
     * @return void
     */
    protected function initModel(): void
    {
        parent::initModel();
        $select = $this->selectSql();
        $this->getDataQuery = $select . ' where 1 = 1';
        $this->getNumRowsQuery = 'select count(*) as count from projects where 1 = 1';
        $this->getUUIDQuery = $select . ' where projects.uuid = ? limit 1';
        $this->tasksModel = model(TasksModel::class);
        $this->notesModel = model(NotesModel::class);
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
            if ( ! is_string($data[$field]) && ! is_int($data[$field])): return $this->validationFailure([$field => lang('backend/projects.messages.invalidData')]); endif;
        endforeach;
        foreach (['searchFields' => ['name'], 'searchDates' => ['created_at-from', 'created_at-to', 'updated_at-from', 'updated_at-to']] as $field => $allowed):
            if ( ! is_array($data[$field]) || array_diff(array_keys($data[$field]), $allowed) !== []): return $this->validationFailure([$field => lang('backend/projects.messages.invalidData')]); endif;
            foreach ($data[$field] as $key => $value):
                if ( ! is_string($value)): return $this->validationFailure([$field . '.' . $key => lang('backend/projects.messages.invalidData')]); endif;
            endforeach;
        endforeach;
        $validator = service('validation')->reset()->setRules($this->showAllValidationRules() + $this->showAllSearchValidationRules());
        if ( ! $validator->run($data)): return $this->validationFailure($validator->getErrors()); endif;
        foreach (['created_at', 'updated_at'] as $date):
            if ( ! empty($data['searchDates'][$date . '-from']) && ! empty($data['searchDates'][$date . '-to']) && $data['searchDates'][$date . '-from'] > $data['searchDates'][$date . '-to']): return $this->validationFailure(['searchDates.' . $date . '-to' => lang('backend/projects.messages.invalidDateRange')]); endif;
        endforeach;
        return ['result' => true, 'data' => $data];
    }

    /**
     * Restituisce le regole dei campi del progetto; i dati di sistema non sono campi del form.
     * @return array<string,array> Regole di inserimento.
     */
    public function addValidationRules(): array
    {
        return [
            'name' => ['label' => lang('backend/projects.labels.name'), 'rules' => ['required', 'string', 'max_length[255]', 'regex_match[/^[^\x00-\x1F\x7F]+$/u]', 'is_unique[projects.name]']],
            'description' => ['label' => lang('backend/projects.labels.description'), 'rules' => ['permit_empty', 'string', 'max_length[10000]', 'regex_match[/^[^\x00]*$/u]']],
            'parent_project_uuid' => ['label' => lang('backend/projects.labels.parentProject'), 'rules' => ['permit_empty', 'string', 'regex_match[/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i]']],
            'local_path' => ['label' => lang('backend/projects.labels.localPath'), 'rules' => ['permit_empty', 'string', 'max_length[1024]', 'regex_match[/^[^\x00-\x1F\x7F]*$/u]']],
            'local_url' => ['label' => lang('backend/projects.labels.localUrl'), 'rules' => ['permit_empty', 'string', 'max_length[2048]', 'valid_url_strict[http,https]']],
            'production_url' => ['label' => lang('backend/projects.labels.productionUrl'), 'rules' => ['permit_empty', 'string', 'max_length[2048]', 'valid_url_strict[http,https]']],
            'execute_name' => ['label' => lang('backend/projects.labels.executeName'), 'rules' => ['permit_empty', 'string', 'max_length[100]', 'regex_match[/^[a-zA-Z0-9][a-zA-Z0-9._-]*$/]', 'is_unique[projects.execute_name]']],
        ];
    }

    /**
     * Costruisce regole di modifica senza interpolare UUID non ancora validati.
     * @param array $posts Dati ricevuti dal form.
     * @return array<string,array> Regole con controllo dell'unicità sul record corrente.
     */
    public function editValidationRules(array $posts): array
    {
        $uuid = is_string($posts['uuid'] ?? null) && $this->validUuid($posts['uuid']) ? $posts['uuid'] : '';
        $rules = $this->addValidationRules();
        $rules['name']['rules'][4] = 'is_unique[projects.name,uuid,' . $uuid . ']';
        $rules['execute_name']['rules'][4] = 'is_unique[projects.execute_name,uuid,' . $uuid . ']';
        return $this->delValidationRules() + $rules;
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
     * Valida il progetto e ogni elemento annidato della bozza prima di iniziare le scritture.
     * @param array $posts Payload completo non attendibile.
     * @param bool $editing True per il form principale di modifica, che non accetta bozze annidate.
     * @param array $taskNotePermissions Permessi server per le note annidate dei task.
     * @return array Esito con data normalizzato oppure errori indicizzati per campo.
     */
    private function validatePayload(array $posts, bool $editing, array $taskNotePermissions = []): array
    {
        $allowed = $editing ? $this->editAllowedFields : $this->addAllowedFields;
        if (array_diff(array_keys($posts), $allowed) !== []): return $this->validationFailure(['payload' => lang('backend/projects.messages.invalidData')]); endif;
        $data = $this->checkAllowedFields($posts, $allowed);
        foreach (array_diff($allowed, ['tasks', 'notes']) as $field):
            if (array_key_exists($field, $data) && ! is_string($data[$field])): return $this->validationFailure([$field => lang('backend/projects.messages.invalidData')]); endif;
            $data[$field] = trim($data[$field] ?? '');
        endforeach;
        if ($editing && ! $this->validUuid($data['uuid'])): return $this->validationFailure(['uuid' => lang('backend/projects.messages.invalidData')]); endif;
        $validator = service('validation')->reset()->setRules($editing ? $this->editValidationRules($data) : $this->addValidationRules());
        if ( ! $validator->run($data)): return $this->validationFailure($validator->getErrors()); endif;
        if ( ! $editing):
            foreach (['tasks' => $this->tasksModel, 'notes' => $this->notesModel] as $field => $model):
                $items = array_key_exists($field, $data) ? $data[$field] : [];
                if ( ! is_array($items) || ! array_is_list($items) || count($items) > 100): return $this->validationFailure([$field => lang('backend/projects.messages.invalidData')]); endif;
                $data[$field] = [];
                foreach ($items as $index => $item):
                    if ( ! is_array($item)): return $this->validationFailure([$field . '.' . $index => lang('backend/projects.messages.invalidData')]); endif;
                    $checked = $field === 'tasks'
                        ? model(\Modules\Backend\Tasks\Models\TaskModalModel::class)->validateDraftPayload($item, $taskNotePermissions)
                        : $model->validatePayload($item);
                    if ( ! $checked['result']):
                        $errors = [];
                        foreach ($checked['errors'] as $key => $error): $errors[$field . '.' . $index . '.' . $key] = $error; endforeach;
                        return $this->validationFailure($errors);
                    endif;
                    $data[$field][] = $checked['data'];
                endforeach;
            endforeach;
        endif;
        return ['result' => true, 'data' => $data];
    }

    /**
     * Crea il progetto con task, passi e note nella stessa transazione.
     * @param array $posts Campi e bozza del progetto.
     * @param string|null $adminUuid UUID dell'amministratore autenticato, stabilito dal controller.
     * @param array $taskNotePermissions Permessi server delle note dei task e dei passi.
     * @return array Esito con uuid e row persistiti oppure errori; nessuna scrittura parziale.
     */
    public function add(array $posts, ?string $adminUuid = null, array $taskNotePermissions = []): array
    {
        $validated = $this->validatePayload($posts, false, $taskNotePermissions);
        if ( ! $validated['result']): return $validated; endif;
        $data = $validated['data'];
        try {
            $this->db->transBegin();
            $this->validateParent($data['parent_project_uuid'], null);
            $uuid = $this->generateUUID();
            $now = date('Y-m-d H:i:s');
            $this->execute('insert into projects (uuid, name, description, parent_project_uuid, local_path, local_url, production_url, execute_name, created_by_admin_uuid, created_at) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [$uuid, $data['name'], $this->nullable($data['description']), $this->nullable($data['parent_project_uuid']), $this->nullable($data['local_path']), $this->nullable($data['local_url']), $this->nullable($data['production_url']), $this->nullable($data['execute_name']), $adminUuid, $now]);
            foreach ($data['tasks'] as $task): $this->tasksModel->insertForProject($task, $uuid, $adminUuid); endforeach;
            foreach ($data['notes'] as $note): $this->notesModel->insertForProject($note, $uuid, $adminUuid); endforeach;
            return $this->commitProject($uuid, 'addSuccess');
        } catch (\Throwable $exception) {
            return $this->rollback($exception, 'addError');
        }
    }

    /**
     * Confronta i dati con hasDataChanged e aggiorna i soli campi editabili verificando origine e stato del progetto sotto lock.
     * @param array $posts Campi del form di modifica, compreso uuid.
     * @return array Esito e record aggiornato oppure errori.
     */
    public function edit(array $posts): array
    {
        $validated = $this->validatePayload($posts, true);
        if ( ! $validated['result']): return $validated; endif;
        $data = $validated['data'];
        try {
            $this->db->transBegin();
            $original = $this->lockProject($data['uuid']);
            if ($original->deleted_at !== null): throw new \DomainException(lang('backend/projects.messages.cannotModifyDeleted')); endif;
            $this->validateParent($data['parent_project_uuid'], $data['uuid']);
            if ( ! $this->hasDataChanged($data, $original)):
                $this->db->transCommit();
                return ['result' => false, 'changed' => false, 'message' => lang('backend/projects.messages.noDataChanged')];
            endif;
            $this->execute('update projects set name = ?, description = ?, parent_project_uuid = ?, local_path = ?, local_url = ?, production_url = ?, execute_name = ?, updated_at = ? where uuid = ?', [$data['name'], $this->nullable($data['description']), $this->nullable($data['parent_project_uuid']), $this->nullable($data['local_path']), $this->nullable($data['local_url']), $this->nullable($data['production_url']), $this->nullable($data['execute_name']), date('Y-m-d H:i:s'), $data['uuid']]);
            return $this->commitProject($data['uuid'], 'editSuccess');
        } catch (\Throwable $exception) {
            return $this->rollback($exception, 'editError');
        }
    }

    /**
     * Sposta nel cestino un progetto senza applicazioni o progetti derivati ancora attivi.
     * @param array $posts Identificativo validato dal controller.
     * @return array Esito dell'operazione.
     */
    public function softDelete(array $posts): array
    {
        return $this->changeDeletion($posts, 'softDelete');
    }

    /**
     * Ripristina un progetto solo se la sua derivazione è ancora valida e attiva.
     * @param array $posts Identificativo validato dal controller.
     * @return array Esito dell'operazione.
     */
    public function restoreDelete(array $posts): array
    {
        return $this->changeDeletion($posts, 'restoreDelete');
    }

    /**
     * Elimina definitivamente un progetto cestinato senza altri vincoli, eliminando anche le sue annotazioni.
     * @param array $posts Identificativo validato dal controller.
     * @return array Esito dell'operazione.
     */
    public function hardDelete(array $posts): array
    {
        return $this->changeDeletion($posts, 'hardDelete');
    }

    /**
     * Applica le transizioni di cestino sotto lock, preservando le note nel cestino e rimuovendole solo nella cancellazione definitiva.
     * @param array $posts Payload contenente uuid.
     * @param string $operation Operazione interna ammessa dal chiamante.
     * @return array Esito con uuid e nome per l'audit.
     */
    private function changeDeletion(array $posts, string $operation): array
    {
        if ( ! is_string($posts['uuid'] ?? null) || ! $this->validUuid($posts['uuid'])): return $this->validationFailure(['uuid' => lang('backend/projects.messages.invalidData')]); endif;
        try {
            $this->db->transBegin();
            $row = $this->lockProject($posts['uuid']);
            $now = date('Y-m-d H:i:s');
            if ($operation === 'softDelete'):
                if ($row->deleted_at !== null): throw new \DomainException(lang('backend/projects.messages.cannotModifyDeleted')); endif;
                $dependencies = $this->db->query('select (select count(*) from applications where project_uuid = ? and deleted_at is null) + (select count(*) from projects where parent_project_uuid = ? and deleted_at is null) as total', [$row->uuid, $row->uuid])->getRow();
                if ((int) $dependencies->total > 0): throw new \DomainException(lang('backend/projects.messages.activeDependants')); endif;
                $this->execute('update projects set deleted_at = ?, updated_at = ? where uuid = ?', [$now, $now, $row->uuid]);
            elseif ($operation === 'restoreDelete'):
                if ($row->deleted_at === null): throw new \DomainException(lang('backend/projects.messages.notTrashed')); endif;
                $this->validateParent($row->parent_project_uuid ?? '', $row->uuid);
                $this->execute('update projects set deleted_at = null, restored_at = ?, updated_at = ? where uuid = ?', [$now, $now, $row->uuid]);
            else:
                if ($row->deleted_at === null): throw new \DomainException(lang('backend/projects.messages.notTrashed')); endif;
                $references = $this->db->query('select (select count(*) from projects where parent_project_uuid = ?) + (select count(*) from applications where project_uuid = ?) + (select count(*) from tasks where project_uuid = ?) + (select count(*) from operation_runs where project_uuid = ?) as total', array_fill(0, 4, $row->uuid))->getRow();
                if ((int) $references->total > 0): throw new \DomainException(lang('backend/projects.messages.hasReferences')); endif;
                $this->execute('delete from notes where project_uuid = ?', [$row->uuid]);
                $this->execute('delete from projects where uuid = ?', [$row->uuid]);
            endif;
            if ( ! $this->db->transStatus()): throw new \RuntimeException('Project deletion transaction failed.'); endif;
            $this->db->transCommit();
            return ['result' => true, 'uuid' => $row->uuid, 'name' => $row->name, 'message' => lang('backend/projects.messages.' . $operation . 'Success')];
        } catch (\Throwable $exception) {
            return $this->rollback($exception, 'deleteError');
        }
    }

    /**
     * Elenca i progetti tecnici attivi selezionabili come origine.
     * @param string|null $excludedUuid Record da escludere nel form di modifica.
     * @return list<object> Coppie uuid e name ordinate per nome.
     */
    public function getParentOptions(?string $excludedUuid = null): array
    {
        return $this->db->query('select uuid, name from projects where deleted_at is null and (? is null or uuid <> ?) order by name', [$excludedUuid, $excludedUuid])->getResult();
    }

    /**
     * Conta i progetti attivi e cestinati, indipendentemente dalla ricerca corrente.
     * @return array{active:int,trashed:int,all:int} Conteggi non filtrati dalla ricerca.
     */
    public function getTrashCounts(): array
    {
        $row = $this->db->query('select count(*) as total, count(case when deleted_at is not null then 1 end) as trashed from projects where 1 = 1')->getRow();
        return ['active' => (int) $row->total - (int) $row->trashed, 'trashed' => (int) $row->trashed, 'all' => (int) $row->total];
    }

    /**
     * Conta i progetti attivi per la navigazione di Manager.
     * @return int Numero di progetti attivi.
     */
    public function activeCount(): int
    {
        return (int) $this->db->query('select count(*) as total from projects where deleted_at is null')->getRow()->total;
    }

    /**
     * Compone la proiezione comune per lista e dettaglio.
     * @return string SQL statico, senza dati dell'utente interpolati.
     */
    private function selectSql(): string
    {
        return 'select projects.*, (select parent.name from projects parent where parent.uuid = projects.parent_project_uuid) as parent_project_name,
            (select count(*) from applications where applications.project_uuid = projects.uuid and applications.deleted_at is null) as application_count,
            (select count(*) from tasks where tasks.project_uuid = projects.uuid and tasks.deleted_at is null and tasks.archived_at is null) as task_count,
            (select count(*) from notes where notes.project_uuid = projects.uuid and notes.deleted_at is null) as note_count from projects';
    }

    /**
     * Verifica esistenza, disponibilità e assenza di cicli lungo la derivazione, bloccando le righe lette.
     * @param string $parentUuid Origine proposta, vuota quando non presente.
     * @param string|null $projectUuid Progetto corrente da non incontrare nel percorso.
     * @return void
     * @throws \DomainException Se l'origine non è utilizzabile o crea un ciclo.
     */
    private function validateParent(string $parentUuid, ?string $projectUuid): void
    {
        $seen = $projectUuid !== null ? [$projectUuid => true] : [];
        while ($parentUuid !== ''):
            if (isset($seen[$parentUuid])): throw new \DomainException(lang('backend/projects.messages.invalidParent')); endif;
            $seen[$parentUuid] = true;
            $row = $this->db->query('select uuid, parent_project_uuid, deleted_at from projects where uuid = ? for update', [$parentUuid])->getRow();
            if ( ! $row || $row->deleted_at !== null): throw new \DomainException(lang('backend/projects.messages.invalidParent')); endif;
            $parentUuid = $row->parent_project_uuid ?? '';
        endwhile;
    }

    /**
     * Blocca il progetto su cui applicare una mutazione.
     * @param string $uuid UUID già validato.
     * @return object Riga persistita.
     * @throws \DomainException Se il progetto non esiste.
     */
    private function lockProject(string $uuid): object
    {
        $row = $this->db->query('select * from projects where uuid = ? for update', [$uuid])->getRow();
        if ( ! $row): throw new \DomainException(lang('backend/projects.messages.notFound')); endif;
        return $row;
    }

    /**
     * Rilegge il progetto prima del commit e chiude la transazione solo se tutte le query sono riuscite.
     * @param string $uuid UUID inserito o aggiornato.
     * @param string $message Chiave del messaggio di successo.
     * @return array Esito e record completo.
     */
    private function commitProject(string $uuid, string $message): array
    {
        if ( ! $this->db->transStatus()): throw new \RuntimeException('Project transaction failed.'); endif;
        $record = parent::getByUUID($uuid);
        if ( ! $record['result']): throw new \RuntimeException('Saved project not available.'); endif;
        $this->db->transCommit();
        return ['result' => true, 'uuid' => $uuid, 'row' => $record['row'], 'message' => lang('backend/projects.messages.' . $message)];
    }

    /**
     * Annulla la transazione e non espone SQL, percorsi interni o errori del driver al client.
     * @param \Throwable $exception Errore originale da registrare nel log.
     * @param string $message Chiave del messaggio generico.
     * @return array{result:false,message:string} Errore applicativo.
     */
    private function rollback(\Throwable $exception, string $message): array
    {
        $this->db->transRollback();
        $this->db->resetTransStatus();
        log_message('error', 'Projects - ' . $exception->getMessage());
        return ['result' => false, 'message' => $exception instanceof \DomainException ? $exception->getMessage() : lang('backend/projects.messages.' . $message)];
    }

    /**
     * Esegue una query preparata verificandone anche il risultato booleano.
     * @param string $sql SQL con placeholder.
     * @param array $parameters Valori da associare ai placeholder.
     * @return void
     */
    private function execute(string $sql, array $parameters): void
    {
        if ($this->db->query($sql, $parameters) === false): throw new \RuntimeException('Project query failed.'); endif;
    }

    /**
     * Converte i campi facoltativi vuoti nel NULL previsto dallo schema.
     * @param string $value Valore normalizzato.
     * @return string|null Valore persistibile.
     */
    private function nullable(string $value): ?string
    {
        return $value !== '' ? $value : null;
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
     * Uniforma gli errori di validazione del progetto e della bozza.
     * @param array $errors Errori indicizzati per campo, anche annidato.
     * @return array{result:false,errors:array,message:string} Esito non salvabile.
     */
    private function validationFailure(array $errors): array
    {
        return ['result' => false, 'errors' => $errors, 'message' => lang('backend/projects.messages.validationErrors')];
    }
}
