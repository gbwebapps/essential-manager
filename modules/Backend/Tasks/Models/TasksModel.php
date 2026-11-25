<?php declare(strict_types = 1);

namespace Modules\Backend\Tasks\Models;

use App\Models\Backend\BackendModel;

/** Valida e salva task e passi, mantenendo UUID e appartenenza al progetto lato server. */
class TasksModel extends BackendModel
{
    protected ?string $module = 'tasks';
    protected bool $hasSoftDelete = true;
    protected ?string $getUUIDQuery = 'select * from tasks where uuid = ? limit 1';
    protected array $showAllAllowedFields=['column','order','page','rows','searchFields','searchDates','trash_filter'];
    protected array $showAllSearchAllowedFields=['title'];
    protected array $showAllSearchAllowedDates=['created_at','updated_at'];
    protected array $allowedOrderColumns=['id','title','status','created_at','updated_at'];
    protected array $toCompare = ['title', 'description', 'status'];
    protected array $addAllowedFields = ['title', 'description', 'status', 'steps'];
    protected array $editAllowedFields = ['uuid', 'title', 'description', 'status', 'steps'];

    /**
     * Restituisce le regole di tutti i campi scalari del task.
     * @return array<string, array> Regole native di CodeIgniter.
     */
    public function addValidationRules(): array
    {
        return [
            'title' => ['label' => lang('backend/tasks.labels.title'), 'rules' => ['required', 'string', 'max_length[100]', 'regex_match[/^(?=.*[\p{L}\p{N}])[^\x00-\x1F\x7F-\x9F<>]+$/u]']],
            'description' => ['label' => lang('backend/tasks.labels.description'), 'rules' => ['permit_empty', 'string', 'max_length[10000]', 'regex_match[/^[^\x00-\x08\x0B\x0C\x0E-\x1F\x7F-\x9F<>]*$/u]']],
            'status' => ['label' => lang('backend/tasks.labels.status'), 'rules' => ['required', 'string', 'in_list[todo,in_progress,completed]']],
        ];
    }

    /**
     * Aggiunge il controllo dell'UUID alle regole del task.
     * @param array $posts Dati ricevuti; l'UUID non viene interpolato in SQL o nelle regole.
     * @return array<string, array> Regole per la modifica.
     */
    public function editValidationRules(array $posts): array
    {
        return ['uuid' => ['rules' => ['required', 'string', 'regex_match[/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i]']]] + $this->addValidationRules();
    }

    /**
     * Valida forma, tipi, limiti e UUID di ogni passo, senza leggere dati del browser come autoritativi.
     * @param array $posts Campi del task e lista ordinata steps.
     * @param bool $editing True per una modifica di un task esistente.
     * @return array{result:bool,data?:array,errors?:array,message?:string} Dati normalizzati oppure errori.
     */
    public function validatePayload(array $posts, bool $editing = false): array
    {
        $errors = [];
        $allowed = $editing ? $this->editAllowedFields : $this->addAllowedFields;
        if (array_diff(array_keys($posts), $allowed) !== []):
            $errors['payload'] = lang('backend/tasks.messages.invalidData');
        endif;
        $data = $this->checkAllowedFields($posts, $allowed);
        foreach (['uuid', 'title', 'description', 'status'] as $field):
            if (isset($data[$field]) && ! is_string($data[$field])):
                $errors[$field] = lang('backend/tasks.messages.invalidData');
            elseif (isset($data[$field])):
                $data[$field] = trim($data[$field], " \t\n\r");
            endif;
        endforeach;
        $data['description'] = $data['description'] ?? '';
        if ( ! array_key_exists('steps', $data)): $data['steps'] = []; endif;
        if ( ! is_array($data['steps']) || ! array_is_list($data['steps']) || count($data['steps']) > 100):
            $errors['steps'] = lang('backend/tasks.messages.invalidSteps');
        else:
            $seen = [];
            foreach ($data['steps'] as $index => $step):
                if ( ! is_array($step) || array_diff(array_keys($step), ['uuid', 'title']) !== [] || ! is_string($step['title'] ?? null) || (isset($step['uuid']) && ! is_string($step['uuid']))):
                    $errors['steps.' . $index . '.title'] = lang('backend/tasks.messages.invalidData');
                    continue;
                endif;
                $title = trim($step['title'], " \t\n\r");
                $uuid = trim($step['uuid'] ?? '');
                if ($title === '' || mb_strlen($title) > 255 || preg_match('/^(?=.*[\p{L}\p{N}])[^\x00-\x1F\x7F-\x9F<>]+$/u', $title) !== 1):
                    $errors['steps.' . $index . '.title'] = lang('backend/tasks.messages.invalidStep');
                endif;
                if ($uuid !== '' && ( ! $editing || ! $this->validUuid($uuid) || isset($seen[$uuid]))):
                    $errors['steps.' . $index . '.uuid'] = lang('backend/tasks.messages.invalidStep');
                endif;
                if ($uuid !== ''): $seen[$uuid] = true; endif;
                $data['steps'][$index] = ['uuid' => $uuid, 'title' => $title];
            endforeach;
        endif;
        $validator = service('validation')->reset()->setRules($editing ? $this->editValidationRules($data) : $this->addValidationRules());
        if ( ! $validator->run($data)): $errors += $validator->getErrors(); endif;
        if ($errors !== []): return $this->validationFailure($errors); endif;
        return ['result' => true, 'data' => $data];
    }

    /**
     * Inizializza le query del motore globale di lista e dettaglio.
     * @return void
     */
    protected function initModel(): void
    {
        parent::initModel();
        $this->restrictContexts(true, true);
        $this->getUUIDQuery = $this->taskSelect() . ' where tasks.uuid = ? limit 1';
    }

    /**
     * Restringe la lista ai contesti autorizzati dal controller, mai da flag del browser.
     * @param bool $projects Accesso ai task dei progetti.
     * @param bool $applications Accesso ai task delle applicazioni.
     * @param bool $archived True per il solo archivio; false per il lavoro operativo.
     * @return void
     */
    public function restrictContexts(bool $projects, bool $applications, bool $archived = false): void
    {
        $scope = $projects && $applications ? '(tasks.project_uuid is not null or tasks.application_uuid is not null)' : ($projects ? 'tasks.project_uuid is not null' : ($applications ? 'tasks.application_uuid is not null' : '1 = 0'));
        $scope .= ' and tasks.is_studio = 0 and tasks.archived_at is ' . ($archived ? 'not null' : 'null');
        $this->getDataQuery = $this->taskSelect() . ' where ' . $scope;
        $this->getNumRowsQuery = 'select count(*) as count from tasks where ' . $scope;
    }

    /**
     * Valida ricerca, stato, contesti e paginazione prima del BackendModel.
     * @param array $posts Filtri non attendibili della lista.
     * @return array Esito con data normalizzato oppure errors.
     */
    public function validateList(array $posts): array
    {
        if (array_diff(array_keys($posts), $this->showAllAllowedFields)): return $this->validationFailure(['payload' => lang('backend/tasks.messages.invalidData')]); endif;
        $data = $posts + ['column'=>'id','order'=>'desc','page'=>'1','rows'=>'5','trash_filter'=>'active','searchFields'=>[],'searchDates'=>[]];
        foreach (['column','order','page','rows','trash_filter'] as $field):
            if ( ! is_string($data[$field]) && ! is_int($data[$field])): return $this->validationFailure([$field=>lang('backend/tasks.messages.invalidData')]); endif;
        endforeach;
        if ( ! is_array($data['searchFields']) || ! is_array($data['searchDates'])): return $this->validationFailure(['payload'=>lang('backend/tasks.messages.invalidData')]); endif;
        if (array_diff(array_keys($data['searchFields']), ['title','status','project_uuid','application_uuid']) || array_diff(array_keys($data['searchDates']), ['created_at-from','created_at-to','updated_at-from','updated_at-to'])): return $this->validationFailure(['payload'=>lang('backend/tasks.messages.invalidData')]); endif;
        foreach (array_merge($data['searchFields'],$data['searchDates']) as $value):
            if ( ! is_string($value)): return $this->validationFailure(['payload'=>lang('backend/tasks.messages.invalidData')]); endif;
        endforeach;
        $rules = [
            'column'=>['rules'=>['required','in_list[id,title,status,created_at,updated_at]']],
            'order'=>['rules'=>['required','in_list[asc,desc]']],
            'page'=>['rules'=>['required','is_natural_no_zero','less_than_equal_to[1000000]']],
            'rows'=>['rules'=>['required','in_list[5,10,15,20]']],
            'trash_filter'=>['rules'=>['required','in_list[active,trashed,all]']],
            'searchFields.title'=>['rules'=>['permit_empty','string','max_length[255]','regex_match[/^[^\x00-\x1F\x7F-\x9F<>]*$/u]']],
            'searchFields.status'=>['rules'=>['permit_empty','string','in_list[open,todo,in_progress,completed]']],
        ];
        foreach (['project_uuid','application_uuid'] as $key): $rules['searchFields.'.$key]=['rules'=>['permit_empty','string',$this->uuidRule()]]; endforeach;
        foreach (['created_at','updated_at'] as $column):
            foreach (['from','to'] as $side): $rules['searchDates.'.$column.'-'.$side]=['rules'=>['permit_empty','string','valid_date[Y-m-d H:i:s]']]; endforeach;
            if ( ! empty($data['searchDates'][$column.'-from']) && ! empty($data['searchDates'][$column.'-to']) && $data['searchDates'][$column.'-from']>$data['searchDates'][$column.'-to']): return $this->validationFailure(['searchDates.'.$column.'-to'=>lang('backend/tasks.messages.invalidData')]); endif;
        endforeach;
        $validator=service('validation')->reset()->setRules($rules);
        return $validator->run($data) ? ['result'=>true,'data'=>$data] : $this->validationFailure($validator->getErrors());
    }

    /**
     * @param array $fields Filtri validati.
     * @param array $params Bind SQL.
     * @return string Clausole con soli identificatori statici.
     */
    protected function buildFilters(array $fields, array &$params): string
    {
        $sql=parent::buildFilters(array_intersect_key($fields,['title'=>true]),$params);
        if ( ! empty($fields['status'])):
            if ($fields['status']==='open'): $sql.=" and tasks.status <> 'completed'";
            else: $sql.=' and tasks.status = ?'; $params[]=$fields['status']; endif;
        endif;
        if ( ! empty($fields['project_uuid'])):
            $sql.=' and (tasks.project_uuid = ? or tasks.application_uuid in (select uuid from applications where project_uuid = ?))';
            $params[]=$fields['project_uuid'];$params[]=$fields['project_uuid'];
        endif;
        if ( ! empty($fields['application_uuid'])): $sql.=' and tasks.application_uuid = ?';$params[]=$fields['application_uuid']; endif;
        return $sql;
    }

    /**
     * @param bool $projects Contesto progetti autorizzato.
     * @param bool $apps Contesto applicazioni autorizzato.
     * @return array Opzioni reali attive, senza dati dimostrativi.
     */
    public function ownerOptions(bool $projects, bool $apps): array
    {
        $options=[];
        if ($projects): foreach ($this->db->query('select uuid,name from projects where deleted_at is null order by name')->getResult() as $row): $options[]=(object)['value'=>'project:'.$row->uuid,'label'=>$row->name,'kind'=>'project']; endforeach; endif;
        if ($apps): foreach ($this->db->query('select a.uuid,a.name,p.name as project_name from applications a join projects p on p.uuid=a.project_uuid where a.deleted_at is null and p.deleted_at is null order by a.name')->getResult() as $row): $options[]=(object)['value'=>'application:'.$row->uuid,'label'=>$row->name.' — '.$row->project_name,'kind'=>'application']; endforeach; endif;
        return $options;
    }

    /**
     * @return array Regola comune per UUID e parametri nascosti.
     */
    public function delValidationRules(): array
    {
        return ['uuid'=>['rules'=>['required','string',$this->uuidRule()]]];
    }

    /**
     * Raccoglie insieme gli errori del contesto, del task e dei passi.
     * @param array $posts Campi non attendibili del form completo.
     * @param object|null $original Task persistito, soltanto per Edit.
     * @return array Esito con dati normalizzati oppure tutti gli errori dei campi.
     */
    public function validateForm(array $posts, ?object $original = null): array
    {
        $editing = $original !== null;
        $errors = [];
        $context = $this->parseContext($posts['context'] ?? null);
        if ($context === null):
            $errors['context'] = lang('backend/tasks.messages.chooseContext');
        elseif ($editing):
            if ($context !== self::ownerContext($original)): $errors['context'] = lang('backend/tasks.messages.contextFixed'); endif;
        endif;
        $reference = null;
        if (array_key_exists('reference', $posts)):
            $reference = model(\Modules\Backend\Studio\Models\StudioReferencesModel::class)->parse($posts['reference']);
            if ($context !== ['is_studio', '1'] || $reference === null || (($original->reference->value ?? '') !== $posts['reference'] && ! model(\Modules\Backend\Studio\Models\StudioReferencesModel::class)->available($reference))): $errors['reference'] = lang('backend/studio.invalidReference'); endif;
            unset($posts['reference']);
        elseif ($context === ['is_studio', '1'] && ! $editing):
            $reference = ['', ''];
        endif;
        unset($posts['context']);
        if ( ! $editing && array_key_exists('uuid', $posts)):
            $errors['payload'] = lang('backend/tasks.messages.invalidData');
            unset($posts['uuid']);
        endif;
        $checkedTask = $this->validatePayload($posts, $editing);
        if ( ! $checkedTask['result']): $errors += $checkedTask['errors']; endif;
        if ($errors !== []): return $this->validationFailure($errors); endif;
        return ['result' => true, 'data' => $checkedTask['data'], 'context' => $context, 'reference' => $reference];
    }

    /**
     * Salva task e passi solo dopo la validazione completa del form.
     * @param array $posts Form Add non attendibile.
     * @param string|null $adminUuid Identità stabilita dal server.
     * @param bool $canComplete Permesso effettivo di completamento.
     * @return array Esito atomico dell'inserimento.
     */
    public function add(array $posts, ?string $adminUuid, bool $canComplete): array
    {
        $checked = $this->validateForm($posts);
        if ( ! $checked['result']): return $checked; endif;
        return $this->saveForContext($checked['data'], $checked['context'][0], $checked['context'][1], $adminUuid, $canComplete, $checked['reference']);
    }

    /**
     * Valida insieme tutti i campi Edit, mantenendo immutabile il contesto salvato.
     * @param array $posts Form Edit non attendibile.
     * @param string|null $adminUuid Identità stabilita dal server.
     * @param bool $canComplete Permesso effettivo di completamento.
     * @return array Esito del confronto e del salvataggio.
     */
    public function edit(array $posts, ?string $adminUuid, bool $canComplete): array
    {
        if ( ! is_string($posts['uuid'] ?? null) || ! $this->validUuid($posts['uuid'])): return $this->validationFailure(['uuid' => lang('backend/tasks.messages.invalidData')]); endif;
        $record = $this->getByUUID($posts['uuid']);
        if ( ! $record['result']): return $record; endif;
        $checked = $this->validateForm($posts, $record['row']);
        if ( ! $checked['result']): return $checked; endif;
        return $this->saveForContext($checked['data'], $checked['context'][0], $checked['context'][1], $adminUuid, $canComplete, $checked['reference']);
    }


    /**
     * @param string $uuid Identificativo del task.
     * @param bool $active Richiede task e contesto attivi.
     * @return object Riga bloccata; chiamare dentro una transazione.
     */
    public function lockTask(string $uuid, bool $active=true): object
    {
        $initial=$this->db->query('select project_uuid,application_uuid,is_studio from tasks where uuid=?',[$uuid])->getRow();
        if ( ! $initial): throw new \DomainException(lang('backend/tasks.messages.notFound')); endif;
        [$column,$ownerUuid]=self::ownerContext($initial);
        if ($active): $this->lockOwner($column,$ownerUuid); endif;
        $row=$this->db->query('select * from tasks where uuid=? for update',[$uuid])->getRow();
        if ($active && $row !== null && $row->archived_at !== null): throw new \DomainException(lang('backend/tasks.archive.readOnly')); endif;
        if ( ! $row || $row->{$column}!==$ownerUuid || ($active && $row->deleted_at!==null)): throw new \DomainException(lang('backend/tasks.messages.notFound')); endif;
        return $row;
    }

    /**
     * @param string $uuid Task.
     * @param string|null $stepUuid Passo oppure null per l'intero task.
     * @param bool $complete Stato richiesto.
     * @param string $adminUuid Identità corrente.
     * @return array Esito e record persistito.
     */
    public function changeCompletion(string $uuid, ?string $stepUuid, bool $complete, string $adminUuid): array
    {
        try {
            $this->db->transBegin();$task=$this->lockTask($uuid);
            $steps=$this->db->query('select * from task_steps where task_uuid=? order by position for update',[$uuid])->getResult();
            if ( ! $steps):
                if ($stepUuid !== null): throw new \DomainException(lang('backend/tasks.messages.foreignStep')); endif;
                if (($task->status === 'completed') === $complete): return $this->unchangedTransaction(); endif;
                $now = date('Y-m-d H:i:s');
                $this->execute('update tasks set status=?,completed_at=?,updated_at=? where uuid=?', [$complete ? 'completed' : 'in_progress', $complete ? $now : null, $now, $uuid]);
                if ($complete): $this->clearCurrent($uuid); endif;
                return $this->finishTask($uuid);
            endif;
            $targets=$stepUuid===null?$steps:array_values(array_filter($steps,static fn(object $step):bool=>$step->uuid===$stepUuid));
            if ( ! $targets): throw new \DomainException(lang('backend/tasks.messages.foreignStep')); endif;
            if ($stepUuid===null && ! $complete):
                if ($task->status!=='completed'): return $this->unchangedTransaction(); endif;
                $targets=[end($steps)];
            endif;
            $changed=false;$now=date('Y-m-d H:i:s');
            foreach ($targets as $step):
                if (($step->completed_at!==null)===$complete): continue; endif;
                $this->execute('update task_steps set completed_at=?,completed_by_admin_uuid=?,updated_at=? where uuid=? and task_uuid=?',[$complete?$now:null,$complete?$adminUuid:null,$now,$step->uuid,$uuid]);$changed=true;
            endforeach;
            if ( ! $changed): return $this->unchangedTransaction(); endif;
            $this->syncProgress($uuid,true);
            return $this->finishTask($uuid);
        } catch (\Throwable $error) { return $this->failTask($error); }
    }

    /**
     * Cestina un task dalla scheda del proprietario, conservando passi e note.
     * @param string $uuid UUID del task già validato dal controller.
     * @param string $column project_uuid oppure application_uuid, scelto dal codice.
     * @param string $ownerUuid UUID del proprietario autorizzato.
     * @return array Esito senza modifiche a task di altri contesti.
     */
    public function deleteForOwner(string $uuid, string $column, string $ownerUuid): array
    {
        if ( ! in_array($column, ['project_uuid', 'application_uuid', 'is_studio'], true)): throw new \InvalidArgumentException('Invalid task owner.'); endif;
        try {
            $this->db->transBegin();
            $this->lockOwner($column, $ownerUuid);
            $task = $this->lockTask($uuid, false);
            if ($task->{$column} !== $ownerUuid): throw new \DomainException(lang('backend/tasks.messages.notFound')); endif;
            if ($task->deleted_at !== null): return $this->unchangedTransaction(); endif;
            $now = date('Y-m-d H:i:s');
            $this->execute('update tasks set deleted_at=?, updated_at=? where uuid=?', [$now, $now, $uuid]);
            $this->clearCurrent($uuid);
            if ( ! $this->db->transStatus()): throw new \RuntimeException('Context task deletion failed.'); endif;
            $this->db->transCommit();
            return ['result' => true, 'uuid' => $uuid, 'message' => lang('backend/tasks.messages.softDeleteSuccess')];
        } catch (\Throwable $error) { return $this->failTask($error); }
    }

    /**
     * Archivia o recupera un task senza alterare stato, passi o note.
     * @param string $uuid UUID già validato e autorizzato dal controller.
     * @param bool $archive True per archiviare; false per recuperare il task.
     * @return array Esito atomico; tutte le selezioni correnti del task archiviato vengono liberate.
     */
    public function changeArchive(string $uuid, bool $archive): array
    {
        try {
            $this->db->transBegin();
            $owner = $this->db->query('select project_uuid,application_uuid,is_studio from tasks where uuid=?', [$uuid])->getRow();
            if ( ! $owner): throw new \DomainException(lang('backend/tasks.messages.notFound')); endif;
            [$column, $ownerUuid] = self::ownerContext($owner);
            $this->lockOwner($column, $ownerUuid);
            $task = $this->lockTask($uuid, false);
            if ($task->deleted_at !== null): throw new \DomainException(lang('backend/tasks.messages.contextInactive')); endif;
            if (($task->archived_at !== null) === $archive): return $this->unchangedTransaction(); endif;
            $now = date('Y-m-d H:i:s');
            $this->execute('update tasks set archived_at=?,updated_at=? where uuid=?', [$archive ? $now : null, $now, $uuid]);
            if ($archive): $this->clearCurrent($uuid); endif;
            $result = $this->finishTask($uuid);
            $result['message'] = lang('backend/tasks.archive.' . ($archive ? 'saved' : 'recovered'));
            return $result;
        } catch (\Throwable $error) { return $this->failTask($error); }
    }

    /**
     * @param string $uuid Task.
     * @param string $action softDelete, restoreDelete oppure hardDelete, fissato dal controller.
     * @return array Esito atomico; le note seguono la cancellazione definitiva del task.
     */
    public function changeDeletion(string $uuid, string $action): array
    {
        try {
            $this->db->transBegin();
            // Mantiene l'ordine dei lock proprietario -> task anche nel ripristino.
            if ($action==='restoreDelete'):
                $owner=$this->db->query('select project_uuid,application_uuid,is_studio from tasks where uuid=?',[$uuid])->getRow();
                if ( ! $owner): throw new \DomainException(lang('backend/tasks.messages.notFound')); endif;
                [$column,$ownerUuid]=self::ownerContext($owner);$this->lockOwner($column,$ownerUuid);
            endif;
            $task=$this->lockTask($uuid,false);$now=date('Y-m-d H:i:s');
            if ($action==='softDelete'):
                if ($task->deleted_at!==null): return $this->unchangedTransaction(); endif;
                $this->execute('update tasks set deleted_at=?,updated_at=? where uuid=?',[$now,$now,$uuid]);
                $this->clearCurrent($uuid);
            elseif ($action==='restoreDelete'):
                if ($task->deleted_at===null): return $this->unchangedTransaction(); endif;
                $this->execute('update tasks set deleted_at=null,restored_at=?,updated_at=? where uuid=?',[$now,$now,$uuid]);
            elseif ($action==='hardDelete'):
                if ($task->deleted_at===null): throw new \DomainException(lang('backend/tasks.messages.notTrashed')); endif;
                $refs=$this->db->query('select count(*) as total from operation_runs where task_uuid=? or step_uuid in (select uuid from task_steps where task_uuid=?)',[$uuid,$uuid])->getRow();
                if ((int)$refs->total>0): throw new \DomainException(lang('backend/tasks.messages.hasReferences')); endif;
                $this->execute('delete from notes where task_uuid=? or step_uuid in (select uuid from task_steps where task_uuid=?)',[$uuid,$uuid]);
                $this->clearCurrent($uuid);$this->execute('delete from task_steps where task_uuid=?',[$uuid]);$this->execute('delete from tasks where uuid=?',[$uuid]);
            else: throw new \LogicException('Invalid deletion action.'); endif;
            if ( ! $this->db->transStatus()): throw new \RuntimeException('Task deletion failed.'); endif;
            $this->db->transCommit();return ['result'=>true,'uuid'=>$uuid,'action'=>$action,'message'=>lang('backend/tasks.messages.'.$action.'Success')];
        } catch (\Throwable $error) { return $this->failTask($error); }
    }

    /**
     * @param string $adminUuid Amministratore autenticato; non proviene dal form.
     * @param bool $studio Seleziona lo stato Studio; false seleziona le attività.
     * @return object|null Task personale corrente nel solo contesto richiesto, con prossimo passo.
     */
    public function getCurrent(string $adminUuid, bool $studio = false): ?object
    {
        $row=$this->db->query($this->taskSelect().' inner join admin_work_state work on work.task_uuid=tasks.uuid where work.admin_uuid=? and work.work_context=? and tasks.is_studio=? and tasks.deleted_at is null and tasks.archived_at is null and tasks.status<>\'completed\' and coalesce(app.deleted_at,direct.deleted_at,origin.deleted_at) is null limit 1',[$adminUuid,$studio?'studio':'activities',(int)$studio])->getRow();
        if ( ! $row): return null; endif;
        $row->next_step=$this->db->query('select uuid,title,position from task_steps where task_uuid=? and completed_at is null order by position limit 1',[$row->uuid])->getRow();
        return $row;
    }

    /**
     * Seleziona il task corrente senza modificare il corrente dell'altro contesto.
     * @param string $uuid Task validato e autorizzato dal controller.
     * @param string $adminUuid Amministratore autenticato.
     * @param bool $studio Contesto imposto dal controller, non dal form.
     * @return array Esito atomico con record e UUID, oppure nessun cambiamento.
     */
    public function setCurrent(string $uuid, string $adminUuid, bool $studio = false): array
    {
        try {
            $this->db->transBegin();
            $task=$this->lockTask($uuid);
            if ((int)$task->is_studio !== (int)$studio): throw new \DomainException(lang('backend/tasks.messages.forbidden')); endif;
            if ($task->status==='completed'): throw new \DomainException(lang('backend/tasks.messages.currentCompleted')); endif;
            if ( ! $this->db->query('select uuid from admins where uuid=? for update',[$adminUuid])->getRow()): throw new \DomainException(lang('backend/tasks.messages.forbidden')); endif;
            $context=$studio?'studio':'activities';
            $state=$this->db->query('select task_uuid from admin_work_state where admin_uuid=? and work_context=? for update',[$adminUuid,$context])->getRow();
            if (($state->task_uuid??null)===$uuid): return $this->unchangedTransaction(); endif;
            $now=date('Y-m-d H:i:s');
            $this->execute('insert into admin_work_state (admin_uuid,work_context,task_uuid,created_at,updated_at) values (?,?,?,?,?) on duplicate key update task_uuid=values(task_uuid),updated_at=values(updated_at)',[$adminUuid,$context,$uuid,$now,$now]);
            if ($task->status==='todo'): $this->execute("update tasks set status='in_progress',updated_at=? where uuid=?",[$now,$uuid]); endif;
            return $this->finishTask($uuid);
        } catch (\Throwable $error) { return $this->failTask($error); }
    }

    /**
     * @param string $context Selezione non attendibile.
     * @return array|null Colonna statica e UUID, oppure null.
     */
    public function parseContext(mixed $context): ?array
    {
        if ($context === 'studio'): return ['is_studio', '1']; endif;
        if ( ! is_string($context) || ! preg_match('/^(project|application):([0-9a-f-]{36})$/i',$context,$parts) || ! $this->validUuid($parts[2])): return null; endif;
        return [strtolower($parts[1])==='project'?'project_uuid':'application_uuid',$parts[2]];
    }

    /**
     * @param bool $projects Contesto progetti.
     * @param bool $apps Contesto applicazioni.
     * @param bool $archived Seleziona i conteggi dell’archivio.
     * @return array Conteggi del cestino nei contesti consentiti.
     */
    public function getTrashCounts(bool $projects, bool $apps, bool $archived = false): array
    {
        $scope=$projects&&$apps?'(project_uuid is not null or application_uuid is not null)':($projects?'project_uuid is not null':($apps?'application_uuid is not null':'1=0'));
        $scope .= ' and archived_at is ' . ($archived ? 'not null' : 'null');
        $row=$this->db->query('select count(*) total,count(case when deleted_at is not null then 1 end) trashed from tasks where '.$scope)->getRow();
        return ['active'=>(int)$row->total-(int)$row->trashed,'trashed'=>(int)$row->trashed,'all'=>(int)$row->total];
    }

    /**
     * Riassume task aperti e passi da completare nei contesti autorizzati.
     * @param bool $projects Accesso ai progetti.
     * @param bool $applications Accesso alle applicazioni.
     * @return array Conteggi interi per il colpo d'occhio della lista.
     */
    public function overview(bool $projects, bool $applications): array
    {
        $scope = $projects && $applications ? '(project_uuid is not null or application_uuid is not null)' : ($projects ? 'project_uuid is not null' : ($applications ? 'application_uuid is not null' : '1=0'));
        $row = $this->db->query("select count(*) total, coalesce(sum(status <> 'completed'),0) open, coalesce(sum(status = 'in_progress'),0) in_progress,
            coalesce(sum((select count(*) from task_steps where task_uuid=tasks.uuid and completed_at is null)),0) remaining_steps
            from tasks where deleted_at is null and archived_at is null and " . $scope)->getRowArray();
        return array_map('intval', $row);
    }

    /**
     * Conta i task attivi nei soli contesti consentiti dal server.
     * @param bool $projects Accesso ai progetti.
     * @param bool $applications Accesso alle applicazioni.
     * @return int Numero del badge di navigazione.
     */
    public function activeCount(bool $projects = true, bool $applications = true): int
    {
        return $this->getTrashCounts($projects, $applications)['active'];
    }

    /**
     * @return string Proiezione statica riutilizzata da lista, scheda e task corrente.
     */
    private function taskSelect(): string
    {
        return 'select tasks.*,coalesce(direct.name,origin.name) project_name,app.name application_name,coalesce(app.deleted_at,direct.deleted_at,origin.deleted_at) owner_deleted_at,
            (select count(*) from task_steps where task_uuid=tasks.uuid) step_count,
            (select count(*) from notes n where n.deleted_at is null and (n.task_uuid=tasks.uuid or n.step_uuid in (select uuid from task_steps where task_uuid=tasks.uuid))) note_count,
            (select count(*) from task_steps where task_uuid=tasks.uuid and completed_at is not null) completed_count
            from tasks left join projects direct on direct.uuid=tasks.project_uuid left join applications app on app.uuid=tasks.application_uuid left join projects origin on origin.uuid=app.project_uuid';
    }

    /**
     * @param string $column Colonna interna del contesto.
     * @param string $uuid Proprietario.
     * @return void Richiede contesto e progetto padre attivi sotto lock.
     */
    private function lockOwner(string $column, string $uuid): void
    {
        $table=$this->ownerTable($column);
        if ($column==='is_studio'):
            if ($uuid!=='1'): throw new \InvalidArgumentException('Invalid Studio owner.'); endif;
            return;
        endif;
        if ($column==='application_uuid'):
            $app=$this->db->query('select project_uuid from applications where uuid=?',[$uuid])->getRow();
            if ( ! $app || ! $this->db->query('select uuid from projects where uuid=? and deleted_at is null for update',[$app->project_uuid])->getRow()): throw new \DomainException(lang('backend/tasks.messages.projectUnavailable')); endif;
        endif;
        if ( ! $this->db->query("select uuid from {$table} where uuid=? and deleted_at is null for update",[$uuid])->getRow()): throw new \DomainException(lang('backend/tasks.messages.projectUnavailable')); endif;
    }

    /**
     * @param string $uuid Task bloccato.
     * @param bool $started Mantiene in corso una riapertura esplicita.
     * @return void Aggiorna lo stato dall'avanzamento e libera i task correnti completati.
     */
    private function syncProgress(string $uuid, bool $started=false): void
    {
        $row=$this->db->query('select count(*) total,count(case when completed_at is not null then 1 end) done from task_steps where task_uuid=?',[$uuid])->getRow();
        $task=$this->db->query('select status,completed_at from tasks where uuid=?',[$uuid])->getRow();
        $status = (int) $row->total === 0
            ? ($started && $task->status === 'todo' ? 'in_progress' : $task->status)
            : ((int) $row->done === (int) $row->total ? 'completed' : ((int) $row->done > 0 || $started || $task->status === 'in_progress' ? 'in_progress' : 'todo'));
        $now=date('Y-m-d H:i:s');
        $this->execute('update tasks set status=?,completed_at=?,updated_at=? where uuid=?',[$status,$status==='completed'?($task->completed_at??$now):null,$now,$uuid]);
        if ($status==='completed'): $this->clearCurrent($uuid); endif;
    }

    /**
     * @param string $uuid Task non più lavorabile.
     * @return void Azzera i riferimenti personali senza cancellare gli amministratori.
     */
    private function clearCurrent(string $uuid): void
    {
        $this->execute('update admin_work_state set task_uuid=null,updated_at=? where task_uuid=?',[date('Y-m-d H:i:s'),$uuid]);
    }

    /**
     * @param string $uuid Task appena modificato.
     * @return array Esito con record completo dopo commit.
     */
    private function finishTask(string $uuid): array
    {
        if ( ! $this->db->transStatus()): throw new \RuntimeException('Task transaction failed.'); endif;
        $record=$this->getByUUID($uuid);
        if ( ! $record['result']): throw new \RuntimeException('Saved task missing.'); endif;
        $this->db->transCommit();return ['result'=>true,'record'=>$record['row'],'uuid'=>$uuid,'message'=>lang('backend/tasks.messages.saveSuccess')];
    }

    /**
     * @return array Chiude senza scritture una transazione senza cambiamenti.
     */
    private function unchangedTransaction(): array
    {
        $this->db->transCommit();return ['result'=>false,'changed'=>false,'message'=>lang('backend/tasks.messages.noDataChanged')];
    }

    /**
     * @param \Throwable $error Errore originale.
     * @return array Esito senza dettagli SQL esposti.
     */
    private function failTask(\Throwable $error): array
    {
        $this->db->transRollback();$this->db->resetTransStatus();log_message('error','Tasks - '.$error->getMessage());
        return ['result'=>false,'message'=>$error instanceof \DomainException?$error->getMessage():lang('backend/tasks.messages.saveError')];
    }

    /**
     * @return string Regola statica UUID condivisa da filtri e identificativi.
     */
    private function uuidRule(): string
    {
        return 'regex_match[/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i]';
    }

    /**
     * Recupera il task tramite il metodo globale e aggiunge i passi ordinati.
     * @param string $uuid UUID del task.
     * @return array Esito con row e relativo array steps.
     */
    public function getByUUID(string $uuid): array
    {
        $result = parent::getByUUID($uuid);
        if ($result['result']):
            $result['row']->steps = $this->getSteps($uuid);
            model(\Modules\Backend\Studio\Models\StudioReferencesModel::class)->attach([$result['row']]);
        endif;
        return $result;
    }

    /**
     * Carica i task attivi assegnati direttamente al proprietario. Contesto: progetto.
     * @param string $projectUuid UUID del proprietario controllato dal controller.
     * @param bool $withNotes Include le note solo dopo controllo del permesso nel controller.
     * @return array Record attivi.
     */
    public function getForProject(string $projectUuid, bool $withNotes = false): array
    {
        return $this->getForContext('project_uuid', $projectUuid, $withNotes);
    }

    /**
     * Carica i task attivi assegnati direttamente al proprietario. Contesto: applicazione.
     * @param string $applicationUuid UUID del proprietario controllato dal controller.
     * @param bool $withNotes Include le note solo dopo controllo del permesso nel controller.
     * @return array Record attivi.
     */
    public function getForApplication(string $applicationUuid, bool $withNotes = false): array
    {
        return $this->getForContext('application_uuid', $applicationUuid, $withNotes);
    }

    /**
     * Motore condiviso del metodo getForProject per progetto e applicazione.
     * @param string $ownerColumn Colonna interna ammessa dalla whitelist ownerTable.
     * @param string $ownerUuid UUID del proprietario.
     * @param bool $withNotes Caricamento autorizzato delle note in una sola query.
     * @return array Risultato persistito o errore senza scritture parziali.
     */
    private function getForContext(string $ownerColumn, string $ownerUuid, bool $withNotes): array
    {
        $this->ownerTable($ownerColumn);
        $rows = $this->db->query("select * from tasks where {$ownerColumn} = ? and deleted_at is null and archived_at is null order by id", [$ownerUuid])->getResult();
        if ($rows === []): return []; endif;
        $tasks = []; $steps = [];
        foreach ($rows as $row): $row->steps = []; $row->notes = []; $tasks[$row->uuid] = $row; endforeach;
        $stepRows = $this->db->query("select s.* from task_steps s join tasks t on t.uuid=s.task_uuid where t.{$ownerColumn}=? and t.deleted_at is null and t.archived_at is null order by s.task_uuid,s.position", [$ownerUuid])->getResult();
        foreach ($stepRows as $step):
            if ( ! isset($tasks[$step->task_uuid])): continue; endif;
            $step->completed = $step->completed_at !== null; $step->notes = [];
            $tasks[$step->task_uuid]->steps[] = $step; $steps[$step->uuid] = $step;
        endforeach;
        if ($withNotes):
            $notes = $this->db->query("select n.* from notes n left join task_steps s on s.uuid=n.step_uuid join tasks t on t.uuid=coalesce(n.task_uuid,s.task_uuid) where t.{$ownerColumn}=? and t.deleted_at is null and t.archived_at is null and n.deleted_at is null order by n.created_at desc,n.id desc", [$ownerUuid])->getResult();
            foreach ($notes as $note):
                if ($note->step_uuid !== null && isset($steps[$note->step_uuid])): $steps[$note->step_uuid]->notes[] = $note;
                elseif ($note->task_uuid !== null && isset($tasks[$note->task_uuid])): $tasks[$note->task_uuid]->notes[] = $note; endif;
            endforeach;
        endif;
        return $rows;
    }

    /**
     * Legge i passi del task senza accettare filtri o ordinamenti dal client.
     * @param string $uuid UUID del task.
     * @return list<object> Passi con completed booleano per le viste.
     */
    private function getSteps(string $uuid): array
    {
        $rows = $this->db->query('select * from task_steps where task_uuid = ? order by position', [$uuid])->getResult();
        foreach ($rows as $row): $row->completed = $row->completed_at !== null; endforeach;
        return $rows;
    }

    /**
     * Inserisce task nella transazione aperta dal proprietario. Contesto: progetto.
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
     * Inserisce task nella transazione aperta dal proprietario. Contesto: applicazione.
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
        $now = date('Y-m-d H:i:s');
        $completed = $data['status'] === 'completed' ? $now : null;
        $this->execute("insert into tasks (uuid, {$ownerColumn}, title, description, status, created_by_admin_uuid, created_at, completed_at) values (?, ?, ?, ?, ?, ?, ?, ?)", [$uuid, $ownerUuid, $data['title'], $data['description'] !== '' ? $data['description'] : null, $data['status'], $adminUuid, $now, $completed]);
        $notesModel = model(\Modules\Backend\Notes\Models\NotesModel::class);
        foreach ($data['notes'] ?? [] as $note): $notesModel->insertForTask($note, $uuid, $adminUuid); endforeach;
        foreach ($data['steps'] as $position => $step):
            $stepUuid = $this->generateUUID();
            $this->execute('insert into task_steps (uuid, task_uuid, title, position, created_at, completed_at, completed_by_admin_uuid) values (?, ?, ?, ?, ?, ?, ?)', [$stepUuid, $uuid, $step['title'], $position + 1, $now, $completed, $completed !== null ? $adminUuid : null]);
            foreach ($step['notes'] ?? [] as $note): $notesModel->insertForStep($note, $stepUuid, $adminUuid); endforeach;
        endforeach;
        return $uuid;
    }

    /**
     * Valida e salva task verificando appartenenza e stato del proprietario. Contesto: progetto.
     * @param array $posts Campi normalizzati per inserimento, oppure payload da validare per salvataggio.
     * @param string $projectUuid UUID del proprietario controllato dal controller.
     * @param string|null $adminUuid Amministratore autenticato stabilito dal server.
     * @param bool $canComplete Permesso server per completare o riaprire il task.
     * @return array Esito e record persistito oppure errori.
     */
    public function saveForProject(array $posts, string $projectUuid, ?string $adminUuid, bool $canComplete = false): array
    {
        return $this->saveForContext($posts, 'project_uuid', $projectUuid, $adminUuid, $canComplete);
    }

    /**
     * Valida e salva task verificando appartenenza e stato del proprietario. Contesto: applicazione.
     * @param array $posts Campi normalizzati per inserimento, oppure payload da validare per salvataggio.
     * @param string $applicationUuid UUID del proprietario controllato dal controller.
     * @param string|null $adminUuid Amministratore autenticato stabilito dal server.
     * @param bool $canComplete Permesso server per completare o riaprire il task.
     * @return array Esito e record persistito oppure errori.
     */
    public function saveForApplication(array $posts, string $applicationUuid, ?string $adminUuid, bool $canComplete = false): array
    {
        return $this->saveForContext($posts, 'application_uuid', $applicationUuid, $adminUuid, $canComplete);
    }

    /**
     * Salva task e passi dello Studio riusando validazione e transazione comuni.
     * @param array $posts Form non attendibile.
     * @param string|null $adminUuid Amministratore autenticato.
     * @param bool $canComplete Permesso server di completamento.
     * @return array Esito del salvataggio, senza assegnazioni a progetti o applicazioni.
     */
    public function saveForStudio(array $posts, ?string $adminUuid, bool $canComplete = false): array
    {
        return $this->saveForContext($posts, 'is_studio', '1', $adminUuid, $canComplete);
    }

    /**
     * Ricava il proprietario esclusivo del task dal record persistito.
     * @param object $task Riga caricata dal database.
     * @return array{0:string,1:string} Colonna interna e identificativo del contesto.
     */
    public static function ownerContext(object $task): array
    {
        if ((int) ($task->is_studio ?? 0) === 1): return ['is_studio', '1']; endif;
        return $task->project_uuid !== null ? ['project_uuid', $task->project_uuid] : ['application_uuid', $task->application_uuid];
    }

    /**
     * Motore condiviso del metodo saveForProject per progetto e applicazione.
     * @param string $ownerColumn Colonna interna ammessa dalla whitelist ownerTable.
     * @param string $ownerUuid UUID del proprietario.
     * @param array $posts Payload non attendibile validato prima delle scritture.
     * @param string|null $adminUuid Amministratore autenticato.
     * @param bool $canComplete Autorizzazione server al cambio di completamento.
     * @param array|null $reference Riferimento Studio validato; null conserva il valore in modifica.
     * @return array Risultato persistito o errore senza scritture parziali.
     */
    private function saveForContext(array $posts, string $ownerColumn, string $ownerUuid, ?string $adminUuid, bool $canComplete, ?array $reference = null): array
    {
        $this->ownerTable($ownerColumn);
        if ($reference !== null && $ownerColumn !== 'is_studio'): throw new \InvalidArgumentException('Reference requires Studio.'); endif;
        if (array_key_exists('uuid', $posts) && ! is_string($posts['uuid'])): return $this->validationFailure(['uuid' => lang('backend/tasks.messages.invalidData')]); endif;
        $editing = is_string($posts['uuid'] ?? null) && $posts['uuid'] !== '';
        if ( ! $editing): unset($posts['uuid']); endif;
        $validated = $this->validatePayload($posts, $editing);
        if ( ! $validated['result']): return $validated; endif;
        $data = $validated['data'];
        try {
            $this->db->transBegin();
            $this->lockOwner($ownerColumn,$ownerUuid);
            if ( ! $editing):
                if ($data['status'] === 'completed' && ! $canComplete): throw new \DomainException(lang('backend/tasks.messages.completionForbidden')); endif;
                $uuid = $this->insertForContext($data, $ownerColumn, $ownerUuid, $adminUuid);
            else:
                $uuid = $data['uuid'];
                $task = $this->db->query("select * from tasks where uuid = ? and {$ownerColumn} = ? and deleted_at is null and archived_at is null for update", [$uuid, $ownerUuid])->getRow();
                if ( ! $task): throw new \DomainException(lang('backend/tasks.messages.notFound')); endif;
                $hasNewSteps = count(array_filter($data['steps'], static fn (array $step): bool => $step['uuid'] === '')) > 0;
                if ( ! $canComplete && (($task->status === 'completed' && $data['status'] !== 'completed') || ($data['status'] === 'completed' && ($task->status !== 'completed' || $hasNewSteps)))): throw new \DomainException(lang('backend/tasks.messages.completionForbidden')); endif;
                $existing = $this->db->query('select * from task_steps where task_uuid = ? order by position for update', [$uuid])->getResult();
                $byUuid = [];
                foreach ($existing as $step): $byUuid[$step->uuid] = $step; endforeach;
                $retained = [];
                foreach ($data['steps'] as $step):
                    if ($step['uuid'] !== '' && ! isset($byUuid[$step['uuid']])): throw new \DomainException(lang('backend/tasks.messages.foreignStep')); endif;
                    if ($step['uuid'] !== ''): $retained[$step['uuid']] = true; endif;
                endforeach;
                // Anche la rimozione dell'ultimo passo incompleto può completare il task.
                $wouldComplete = $data['steps'] !== [] && ! $hasNewSteps && count(array_filter($data['steps'], static fn(array $step): bool => $byUuid[$step['uuid']]->completed_at !== null)) === count($data['steps']);
                if ( ! $canComplete && $task->status !== 'completed' && $wouldComplete): throw new \DomainException(lang('backend/tasks.messages.completionForbidden')); endif;
                $stepsChanged = $this->haveStepsChanged($data['steps'], $existing);
                $referenceChanged = $reference !== null && model(\Modules\Backend\Studio\Models\StudioReferencesModel::class)->hasChanged($uuid, $reference);
                if ( ! $this->hasDataChanged($data, $task) && ! $stepsChanged && ! $referenceChanged):
                    $this->db->transCommit();
                    return ['result' => false, 'changed' => false, 'message' => lang('backend/tasks.messages.noDataChanged')];
                endif;
                $now = date('Y-m-d H:i:s');
                if ($stepsChanged || $task->status !== $data['status']):
                    foreach ($existing as $step):
                        if (isset($retained[$step->uuid])): continue; endif;
                        $references = $this->db->query('select count(*) as total from operation_runs where step_uuid = ?', [$step->uuid])->getRow();
                        if ((int) $references->total !== 0): throw new \DomainException(lang('backend/tasks.messages.stepInUse')); endif;
                        $this->execute('delete from notes where step_uuid = ?', [$step->uuid]);
                        $this->execute('delete from task_steps where uuid = ? and task_uuid = ?', [$step->uuid, $uuid]);
                    endforeach;
                    // Libera le posizioni prima del riordino, preservando gli UUID già referenziati.
                    $this->execute('update task_steps set position = position + 1000 where task_uuid = ?', [$uuid]);
                    foreach ($data['steps'] as $position => $step):
                        $old = $byUuid[$step['uuid']] ?? null;
                        $completed = $data['status'] === 'completed' ? ($old->completed_at ?? $now) : ($task->status === 'completed' && $position === count($data['steps'])-1 ? null : ($old->completed_at ?? null));
                        $completedBy = $completed !== null ? ($old->completed_by_admin_uuid ?? $adminUuid) : null;
                        if ($old):
                            $this->execute('update task_steps set title = ?, position = ?, updated_at = ?, completed_at = ?, completed_by_admin_uuid = ? where uuid = ? and task_uuid = ?', [$step['title'], $position + 1, $now, $completed, $completedBy, $old->uuid, $uuid]);
                        else:
                            $this->execute('insert into task_steps (uuid, task_uuid, title, position, created_at, completed_at, completed_by_admin_uuid) values (?, ?, ?, ?, ?, ?, ?)', [$this->generateUUID(), $uuid, $step['title'], $position + 1, $now, $completed, $completedBy]);
                        endif;
                    endforeach;
                endif;
                $this->execute("update tasks set title = ?, description = ?, status = ?, completed_at = ?, updated_at = ? where uuid = ? and {$ownerColumn} = ?", [$data['title'], $data['description'] !== '' ? $data['description'] : null, $data['status'], $data['status'] === 'completed' ? ($task->completed_at ?? $now) : null, $now, $uuid, $ownerUuid]);
            endif;
            if ($reference !== null && (! $editing || $referenceChanged)): model(\Modules\Backend\Studio\Models\StudioReferencesModel::class)->save($uuid, $reference); endif;
            $this->syncProgress($uuid,$data['status']==='in_progress');
            if ( ! $this->db->transStatus()): throw new \RuntimeException('Task transaction failed.'); endif;
            $record = $this->getByUUID($uuid);
            if ( ! $record['result']): throw new \RuntimeException('Saved task not available.'); endif;
            $this->db->transCommit();
            return ['result' => true, 'record' => $record['row'], 'uuid' => $uuid, 'message' => lang('backend/tasks.messages.saveSuccess')];
        } catch (\Throwable $exception) {
            $this->db->transRollback();
            $this->db->resetTransStatus();
            log_message('error', 'Tasks::saveForProject - ' . $exception->getMessage());
            return ['result' => false, 'message' => $exception instanceof \DomainException ? $exception->getMessage() : lang('backend/tasks.messages.saveError')];
        }
    }

    /**
     * Confronta numero, ordine, identità e testo dei passi senza usare metadati forniti dal client.
     * @param array $steps Passi normalizzati e già verificati per appartenenza.
     * @param array $original Passi persistiti, ordinati per posizione e bloccati dalla transazione.
     * @return bool True se almeno un passo va inserito, modificato, eliminato o riordinato.
     */
    private function haveStepsChanged(array $steps, array $original): bool
    {
        if (count($steps) !== count($original)): return true; endif;
        foreach ($steps as $index => $step):
            if ($step['uuid'] === '' || $step['uuid'] !== $original[$index]->uuid || $this->hasDataChanged(['title' => $step['title']], $original[$index])): return true; endif;
        endforeach;
        return false;
    }

    /**
     * Traduce soltanto le colonne di contesto definite dal codice nei rispettivi proprietari.
     * @param string $column project_uuid, application_uuid oppure is_studio, mai un identificatore libero dal client.
     * @return string|null Nome statico della tabella del proprietario.
     * @throws \InvalidArgumentException Se il chiamante interno usa un contesto non previsto.
     */
    private function ownerTable(string $column): ?string
    {
        return match ($column) {
            'project_uuid' => 'projects',
            'application_uuid' => 'applications',
            'is_studio' => null,
            default => throw new \InvalidArgumentException('Invalid work owner.'),
        };
    }

    /**
     * Valida il formato UUID senza accessi al database.
     * @param string $uuid Identificativo ricevuto.
     * @return bool True per un UUID valido.
     */
    private function validUuid(string $uuid): bool
    {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $uuid) === 1;
    }

    /**
     * Interrompe immediatamente la transazione se una query preparata fallisce.
     * @param string $sql Query con placeholder.
     * @param array $parameters Valori da associare ai placeholder.
     * @return void
     */
    private function execute(string $sql, array $parameters): void
    {
        if ($this->db->query($sql, $parameters) === false): throw new \RuntimeException('Task query failed.'); endif;
    }

    /**
     * Uniforma gli errori di validazione per i controller chiamanti.
     * @param array $errors Errori indicizzati per campo.
     * @return array{result:false,errors:array,message:string} Esito non salvabile.
     */
    private function validationFailure(array $errors): array
    {
        return ['result' => false, 'errors' => $errors, 'message' => lang('backend/tasks.messages.invalidData')];
    }
}
