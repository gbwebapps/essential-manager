<?php declare(strict_types = 1);

namespace Modules\Backend\Applications\Models;

use App\Models\Backend\BackendModel;
use Modules\Backend\Tasks\Models\TasksModel;
use Modules\Backend\Notes\Models\NotesModel;

/** Persistenza delle applicazioni e della bozza iniziale, separata dal backbone. */
class ApplicationsModel extends BackendModel
{
    protected ?string $module = 'applications';
    protected bool $hasSoftDelete = true;
    protected array $showAllAllowedFields = ['column', 'order', 'page', 'rows', 'searchFields', 'searchDates', 'trash_filter'];
    protected array $showAllSearchAllowedFields = ['name', 'project_uuid'];
    protected array $showAllSearchAllowedDates = ['created_at', 'updated_at'];
    protected array $allowedOrderColumns = ['id', 'name', 'created_at', 'updated_at'];
    protected array $addAllowedFields = ['name', 'description', 'project_uuid', 'local_path', 'local_url', 'production_url', 'execute_name', 'tasks', 'notes'];
    protected array $editAllowedFields = ['uuid', 'name', 'description', 'project_uuid', 'local_path', 'local_url', 'production_url', 'execute_name'];
    protected array $toCompare = ['name', 'description', 'project_uuid', 'local_path', 'local_url', 'production_url', 'execute_name'];
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
        $this->getNumRowsQuery = 'select count(*) as count from applications where 1 = 1';
        $this->getUUIDQuery = $select . ' where applications.uuid = ? limit 1';
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
        $rules = ['searchFields.name' => ['rules' => ['permit_empty', 'string', 'max_length[255]']], 'searchFields.project_uuid' => ['rules' => ['permit_empty', 'string', 'regex_match[/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i]']]];
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
            if ( ! is_string($data[$field]) && ! is_int($data[$field])): return $this->validationFailure([$field => lang('backend/applications.messages.invalidData')]); endif;
        endforeach;
        foreach (['searchFields' => ['name', 'project_uuid'], 'searchDates' => ['created_at-from', 'created_at-to', 'updated_at-from', 'updated_at-to']] as $field => $allowed):
            if ( ! is_array($data[$field]) || array_diff(array_keys($data[$field]), $allowed) !== []): return $this->validationFailure([$field => lang('backend/applications.messages.invalidData')]); endif;
            foreach ($data[$field] as $key => $value):
                if ( ! is_string($value)): return $this->validationFailure([$field . '.' . $key => lang('backend/applications.messages.invalidData')]); endif;
            endforeach;
        endforeach;
        $validator = service('validation')->reset()->setRules($this->showAllValidationRules() + $this->showAllSearchValidationRules());
        if ( ! $validator->run($data)): return $this->validationFailure($validator->getErrors()); endif;
        foreach (['created_at', 'updated_at'] as $date):
            if ( ! empty($data['searchDates'][$date . '-from']) && ! empty($data['searchDates'][$date . '-to']) && $data['searchDates'][$date . '-from'] > $data['searchDates'][$date . '-to']): return $this->validationFailure(['searchDates.' . $date . '-to' => lang('backend/applications.messages.invalidDateRange')]); endif;
        endforeach;
        return ['result' => true, 'data' => $data];
    }

    /**
     * Restituisce le regole dei campi dell’applicazione; i dati di sistema non sono campi del form.
     * @return array<string,array> Regole di inserimento.
     */
    public function addValidationRules(): array
    {
        return [
            'name' => ['label' => lang('backend/applications.labels.name'), 'rules' => ['required', 'string', 'max_length[255]', 'regex_match[/^[^\x00-\x1F\x7F]+$/u]', 'is_unique[applications.name]']],
            'description' => ['label' => lang('backend/applications.labels.description'), 'rules' => ['permit_empty', 'string', 'max_length[10000]', 'regex_match[/^[^\x00]*$/u]']],
            'project_uuid' => ['label' => lang('backend/applications.labels.project'), 'rules' => ['required', 'string', 'regex_match[/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i]']],
            'local_path' => ['label' => lang('backend/applications.labels.localPath'), 'rules' => ['permit_empty', 'string', 'max_length[1024]', 'regex_match[/^[^\x00-\x1F\x7F]*$/u]']],
            'local_url' => ['label' => lang('backend/applications.labels.localUrl'), 'rules' => ['permit_empty', 'string', 'max_length[2048]', 'valid_url_strict[http,https]']],
            'production_url' => ['label' => lang('backend/applications.labels.productionUrl'), 'rules' => ['permit_empty', 'string', 'max_length[2048]', 'valid_url_strict[http,https]']],
            'execute_name' => ['label' => lang('backend/applications.labels.executeName'), 'rules' => ['permit_empty', 'string', 'max_length[100]', 'regex_match[/^[a-zA-Z0-9][a-zA-Z0-9._-]*$/]', 'is_unique[applications.execute_name]']],
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
        $rules['name']['rules'][4] = 'is_unique[applications.name,uuid,' . $uuid . ']';
        $rules['execute_name']['rules'][4] = 'is_unique[applications.execute_name,uuid,' . $uuid . ']';
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
     * Valida l’applicazione e ogni elemento annidato della bozza prima di iniziare le scritture.
     * @param array $posts Payload completo non attendibile.
     * @param bool $editing True per il form principale di modifica, che non accetta bozze annidate.
     * @param array $taskNotePermissions Permessi server per le note annidate dei task.
     * @return array Esito con data normalizzato oppure errori indicizzati per campo.
     */
    private function validatePayload(array $posts, bool $editing, array $taskNotePermissions = []): array
    {
        $allowed = $editing ? $this->editAllowedFields : $this->addAllowedFields;
        if (array_diff(array_keys($posts), $allowed) !== []): return $this->validationFailure(['payload' => lang('backend/applications.messages.invalidData')]); endif;
        $data = $this->checkAllowedFields($posts, $allowed);
        foreach (array_diff($allowed, ['tasks', 'notes']) as $field):
            if (array_key_exists($field, $data) && ! is_string($data[$field])): return $this->validationFailure([$field => lang('backend/applications.messages.invalidData')]); endif;
            $data[$field] = trim($data[$field] ?? '');
        endforeach;
        if ($editing && ! $this->validUuid($data['uuid'])): return $this->validationFailure(['uuid' => lang('backend/applications.messages.invalidData')]); endif;
        $validator = service('validation')->reset()->setRules($editing ? $this->editValidationRules($data) : $this->addValidationRules());
        if ( ! $validator->run($data)): return $this->validationFailure($validator->getErrors()); endif;
        if ( ! $editing):
            foreach (['tasks' => $this->tasksModel, 'notes' => $this->notesModel] as $field => $model):
                $items = array_key_exists($field, $data) ? $data[$field] : [];
                if ( ! is_array($items) || ! array_is_list($items) || count($items) > 100): return $this->validationFailure([$field => lang('backend/applications.messages.invalidData')]); endif;
                $data[$field] = [];
                foreach ($items as $index => $item):
                    if ( ! is_array($item)): return $this->validationFailure([$field . '.' . $index => lang('backend/applications.messages.invalidData')]); endif;
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
     * Crea l’applicazione con task, passi e note nella stessa transazione.
     * @param array $posts Campi e bozza dell’applicazione.
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
            $this->validateProject($data['project_uuid']);
            $uuid = $this->generateUUID();
            $now = date('Y-m-d H:i:s');
            $this->execute('insert into applications (uuid, name, description, project_uuid, local_path, local_url, production_url, execute_name, created_by_admin_uuid, created_at) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [$uuid, $data['name'], $this->nullable($data['description']), $this->nullable($data['project_uuid']), $this->nullable($data['local_path']), $this->nullable($data['local_url']), $this->nullable($data['production_url']), $this->nullable($data['execute_name']), $adminUuid, $now]);
            foreach ($data['tasks'] as $task): $this->tasksModel->insertForApplication($task, $uuid, $adminUuid); endforeach;
            foreach ($data['notes'] as $note): $this->notesModel->insertForApplication($note, $uuid, $adminUuid); endforeach;
            return $this->commitApplication($uuid, 'addSuccess');
        } catch (\Throwable $exception) {
            return $this->rollback($exception, 'addError');
        }
    }

    /**
     * Confronta i dati con hasDataChanged e aggiorna i soli campi editabili verificando origine e stato dell’applicazione sotto lock.
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
            $original = $this->lockApplication($data['uuid']);
            if ($original->deleted_at !== null): throw new \DomainException(lang('backend/applications.messages.cannotModifyDeleted')); endif;
            $this->validateProject($data['project_uuid']);
            if ( ! $this->hasDataChanged($data, $original)):
                $this->db->transCommit();
                return ['result' => false, 'changed' => false, 'message' => lang('backend/applications.messages.noDataChanged')];
            endif;
            $this->execute('update applications set name = ?, description = ?, project_uuid = ?, local_path = ?, local_url = ?, production_url = ?, execute_name = ?, updated_at = ? where uuid = ?', [$data['name'], $this->nullable($data['description']), $this->nullable($data['project_uuid']), $this->nullable($data['local_path']), $this->nullable($data['local_url']), $this->nullable($data['production_url']), $this->nullable($data['execute_name']), date('Y-m-d H:i:s'), $data['uuid']]);
            return $this->commitApplication($data['uuid'], 'editSuccess');
        } catch (\Throwable $exception) {
            return $this->rollback($exception, 'editError');
        }
    }

    /**
     * Sposta nel cestino l’applicazione conservando task, passi, note e operazioni collegati.
     * @param array $posts Identificativo validato dal controller.
     * @return array Esito dell'operazione.
     */
    public function softDelete(array $posts): array
    {
        return $this->changeDeletion($posts, 'softDelete');
    }

    /**
     * Ripristina l’applicazione solo se il suo progetto di origine è ancora attivo.
     * @param array $posts Identificativo validato dal controller.
     * @return array Esito dell'operazione.
     */
    public function restoreDelete(array $posts): array
    {
        return $this->changeDeletion($posts, 'restoreDelete');
    }

    /**
     * Elimina definitivamente un’applicazione cestinata senza altri vincoli, eliminando anche le sue annotazioni.
     * @param array $posts Identificativo validato dal controller.
     * @return array Esito dell'operazione.
     */
    public function hardDelete(array $posts): array
    {
        return $this->changeDeletion($posts, 'hardDelete');
    }

    /**
     * Gestisce cestino e ripristino; la cancellazione definitiva rimuove le annotazioni e richiede assenza di altri vincoli.
     * @param array $posts Payload contenente l'UUID dell'applicazione.
     * @param string $operation Nome interno dell'operazione autorizzata dal controller.
     * @return array Esito con identificativo e nome per il registro attività.
     */
    private function changeDeletion(array $posts, string $operation): array
    {
        if ( ! is_string($posts['uuid'] ?? null) || ! $this->validUuid($posts['uuid'])): return $this->validationFailure(['uuid' => lang('backend/applications.messages.invalidData')]); endif;
        try {
            $this->db->transBegin();
            $row = $this->lockApplication($posts['uuid']);
            $now = date('Y-m-d H:i:s');
            if ($operation === 'softDelete'):
                if ($row->deleted_at !== null): throw new \DomainException(lang('backend/applications.messages.cannotModifyDeleted')); endif;
                $this->execute('update applications set deleted_at = ?, updated_at = ? where uuid = ?', [$now, $now, $row->uuid]);
            elseif ($operation === 'restoreDelete'):
                if ($row->deleted_at === null): throw new \DomainException(lang('backend/applications.messages.notTrashed')); endif;
                $this->validateProject($row->project_uuid);
                $this->execute('update applications set deleted_at = null, restored_at = ?, updated_at = ? where uuid = ?', [$now, $now, $row->uuid]);
            else:
                if ($row->deleted_at === null): throw new \DomainException(lang('backend/applications.messages.notTrashed')); endif;
                $references = $this->db->query('select (select count(*) from tasks where application_uuid = ?) + (select count(*) from operation_runs where application_uuid = ?) as total', array_fill(0, 2, $row->uuid))->getRow();
                if ((int) $references->total > 0): throw new \DomainException(lang('backend/applications.messages.hasReferences')); endif;
                $this->execute('delete from notes where application_uuid = ?', [$row->uuid]);
                $this->execute('delete from applications where uuid = ?', [$row->uuid]);
            endif;
            if ( ! $this->db->transStatus()): throw new \RuntimeException('Application deletion transaction failed.'); endif;
            $this->db->transCommit();
            return ['result' => true, 'uuid' => $row->uuid, 'name' => $row->name, 'message' => lang('backend/applications.messages.' . $operation . 'Success')];
        } catch (\Throwable $exception) {
            return $this->rollback($exception, 'deleteError');
        }
    }

    /**
     * Elenca i progetti attivi ai quali può appartenere un'applicazione.
     * @return list<object> Coppie uuid e name ordinate per nome.
     */
    public function getProjectOptions(): array
    {
        return $this->db->query('select uuid, name from projects where deleted_at is null order by name')->getResult();
    }

    /**
     * Conta le applicazioni attive e cestinate, indipendentemente dalla ricerca corrente.
     * @return array{active:int,trashed:int,all:int} Conteggi non filtrati dalla ricerca.
     */
    public function getTrashCounts(): array
    {
        $row = $this->db->query('select count(*) as total, count(case when deleted_at is not null then 1 end) as trashed from applications where 1 = 1')->getRow();
        return ['active' => (int) $row->total - (int) $row->trashed, 'trashed' => (int) $row->trashed, 'all' => (int) $row->total];
    }

    /**
     * Elenca le applicazioni attive del progetto per le sue viste di dettaglio e modifica.
     * @param string $projectUuid UUID del progetto già validato dal chiamante.
     * @return list<object> Applicazioni persistite ordinate per nome.
     */
    public function getForProject(string $projectUuid): array
    {
        return $this->db->query($this->selectSql() . ' where applications.project_uuid = ? and applications.deleted_at is null order by applications.name', [$projectUuid])->getResult();
    }

    /**
     * Conta le applicazioni attive per la navigazione di Manager.
     * @return int Numero delle applicazioni attive.
     */
    public function activeCount(): int
    {
        return (int) $this->db->query('select count(*) as total from applications where deleted_at is null')->getRow()->total;
    }

    /**
     * Compone la proiezione per lista, dettaglio e applicazioni del progetto.
     * @return string SQL statico senza input interpolati.
     */
    private function selectSql(): string
    {
        return 'select applications.*, origin.name as project_name,
            (select count(*) from tasks where tasks.application_uuid = applications.uuid and tasks.deleted_at is null and tasks.archived_at is null) as task_count,
            (select count(*) from notes where notes.application_uuid = applications.uuid and notes.deleted_at is null) as note_count
            from applications inner join projects origin on origin.uuid = applications.project_uuid';
    }

    /**
     * Richiede un progetto padre esistente e attivo, bloccandolo durante la transazione.
     * @param string $projectUuid UUID del progetto di origine già validato.
     * @return void
     * @throws \DomainException Se il progetto è assente o nel cestino.
     */
    private function validateProject(string $projectUuid): void
    {
        $row = $this->db->query('select uuid from projects where uuid = ? and deleted_at is null for update', [$projectUuid])->getRow();
        if ( ! $row): throw new \DomainException(lang('backend/applications.messages.invalidParent')); endif;
    }

    /**
     * Blocca l’applicazione su cui applicare una mutazione.
     * @param string $uuid UUID già validato.
     * @return object Riga persistita.
     * @throws \DomainException Se l’applicazione non esiste.
     */
    private function lockApplication(string $uuid): object
    {
        $row = $this->db->query('select * from applications where uuid = ? for update', [$uuid])->getRow();
        if ( ! $row): throw new \DomainException(lang('backend/applications.messages.notFound')); endif;
        return $row;
    }

    /**
     * Rilegge l’applicazione prima del commit e chiude la transazione solo se tutte le query sono riuscite.
     * @param string $uuid UUID inserito o aggiornato.
     * @param string $message Chiave del messaggio di successo.
     * @return array Esito e record completo.
     */
    private function commitApplication(string $uuid, string $message): array
    {
        if ( ! $this->db->transStatus()): throw new \RuntimeException('Application transaction failed.'); endif;
        $record = parent::getByUUID($uuid);
        if ( ! $record['result']): throw new \RuntimeException('Saved application not available.'); endif;
        $this->db->transCommit();
        return ['result' => true, 'uuid' => $uuid, 'row' => $record['row'], 'message' => lang('backend/applications.messages.' . $message)];
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
        log_message('error', 'Applications - ' . $exception->getMessage());
        return ['result' => false, 'message' => $exception instanceof \DomainException ? $exception->getMessage() : lang('backend/applications.messages.' . $message)];
    }

    /**
     * Esegue una query preparata verificandone anche il risultato booleano.
     * @param string $sql SQL con placeholder.
     * @param array $parameters Valori da associare ai placeholder.
     * @return void
     */
    private function execute(string $sql, array $parameters): void
    {
        if ($this->db->query($sql, $parameters) === false): throw new \RuntimeException('Application query failed.'); endif;
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
     * Uniforma gli errori di validazione dell’applicazione e della bozza.
     * @param array $errors Errori indicizzati per campo, anche annidato.
     * @return array{result:false,errors:array,message:string} Esito non salvabile.
     */
    private function validationFailure(array $errors): array
    {
        return ['result' => false, 'errors' => $errors, 'message' => lang('backend/applications.messages.validationErrors')];
    }
}
