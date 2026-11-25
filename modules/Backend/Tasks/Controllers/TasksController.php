<?php declare(strict_types = 1);

namespace Modules\Backend\Tasks\Controllers;

use App\Controllers\Backend\BackendController;
use App\Libraries\Backend\AuthorizationClass;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Modules\Backend\Tasks\Config\Views;
use Modules\Backend\Tasks\Libraries\TasksClass;
use Modules\Backend\Tasks\Models\TasksModel;
use Modules\Backend\Tasks\Models\TaskModalModel;
use Modules\Backend\Notes\Models\NotesModel;
use Psr\Log\LoggerInterface;

/** Gestisce i task applicativi senza modificare autenticazione, layout o servizi del backbone. */
class TasksController extends BackendController
{
    protected bool $studioContext = false;
    protected bool $archiveList = false;
    protected TasksModel $tasksModel;
    protected TasksClass $tasksClass;
    protected NotesModel $notesModel;

    /**
     * @param RequestInterface $request Richiesta.
     * @param ResponseInterface $response Risposta.
     * @param LoggerInterface $logger Log.
     * @return void
     */
    public function initController(RequestInterface $request,ResponseInterface $response,LoggerInterface $logger)
    {
        parent::initController($request,$response,$logger);
        $this->data['controller']='tasks';$this->data['entity']='tasks';$this->data['task']=null;
        $this->tasksModel=model(TasksModel::class);$this->tasksClass=new TasksClass();$this->notesModel=model(NotesModel::class);
    }

    /**
     * @return string|ResponseInterface Stesso ingresso della lista.
     */
    public function index(): string|ResponseInterface { return $this->showAll(); }

    /** @return string|ResponseInterface Stessa lista CRUD, limitata ai task archiviati. */
    public function archived(): string|ResponseInterface
    {
        $this->archiveList = true;
        return $this->showAll();
    }

    /**
     * @return string|ResponseInterface Lista CRUD a larghezza piena con riepilogo aggiornato.
     */
    public function showAll(): string|ResponseInterface
    {
        if ( ! $this->allows('tasks_show_all')): return $this->denied(); endif;
        $this->prepareView('showAll');
        $this->tasksModel->restrictContexts($this->projectAccess(),$this->applicationAccess(),$this->archiveList);
        if ($this->request->is('post')):
            $validated=$this->tasksModel->validateList($this->request->getPost());
            if ( ! $validated['result']): return $this->jsonResponse($this->format($validated)); endif;
            $this->data['posts']=$validated['data'];$this->data['data']=$this->tasksModel->getData($validated['data']);
            if ( ! $this->data['data']['result']): return $this->jsonResponse($this->data['data']); endif;
            return $this->jsonResponse(['result'=>true,'output'=>view(Views::SHOW_ALL_PARTIAL,$this->data),'navigationCounts'=>$this->data['navigationCounts'],'overviewOutput'=>view(Views::OVERVIEW_PARTIAL,$this->data),'trashCounts'=>$this->tasksModel->getTrashCounts($this->projectAccess(),$this->applicationAccess(),$this->archiveList)]);
        endif;
        return $this->render(Views::SHOW_ALL_VIEW,$this->data);
    }

    /**
     * @return string|ResponseInterface Inserimento con reset vuoto, mai redirect o salvataggi parziali.
     */
    public function add(): string|ResponseInterface
    {
        if ( ! $this->allows('tasks_add') || ($this->studioContext && ! $this->allows('studio_show'))): return $this->denied(); endif;
        $this->data['task']=null;$this->prepareView('add');
        if ($this->request->is('post')):
            $posts=$this->request->getPost();
            if (($posts['action']??null)==='reset'): return $this->jsonResponse(['result'=>true,'output'=>view(Views::ADD_PARTIAL,$this->data),'navigationCounts'=>$this->data['navigationCounts']]); endif;
            $context=$this->tasksModel->parseContext($posts['context']??null);
            if ($this->studioContext):
                if (array_key_exists('context',$posts) && $posts['context']!=='studio'): return $this->denied(); endif;
                $posts['context']='studio'; $context=['is_studio','1'];
            endif;
            if ($context && (($context[0]==='is_studio')!==$this->studioContext || ! $this->contextAccess($context[0]))): return $this->denied(); endif;
            if ( ! $this->referenceAccess($posts)): return $this->denied(); endif;
            $result=model(TaskModalModel::class)->saveForm($posts,null,$this->currentAdmin->uuid??null,$this->allows('tasks_complete'),$this->notePermissions());
            if ($result['result']): $this->audit('ADD_TASKS',$result['uuid']); endif;
            unset($result['record']);return $this->jsonResponse($this->format($result));
        endif;
        return $this->render($this->taskView('ADD_VIEW'),$this->data);
    }

    /**
     * @param string|null $uuid UUID di rotta per GET.
     * @return string|ResponseInterface Modifica oppure esito con confronto dati e refresh.
     */
    public function edit(?string $uuid=null): string|ResponseInterface
    {
        if ( ! $this->allows('tasks_edit')): return $this->denied(); endif;
        if ($this->request->is('post')):
            $posts=$this->request->getPost();
            if ( ! $this->validateData($posts,$this->tasksModel->delValidationRules())): return $this->validation('action'); endif;
            $task=$this->taskOr404($posts['uuid']);
            if ($task->deleted_at!==null || $task->owner_deleted_at!==null || $task->archived_at!==null): return $this->jsonResponse(['result'=>false,'message'=>lang('backend/tasks.messages.contextInactive')]); endif;
            if (($posts['action']??null)==='refresh'):
                $this->data['task']=$task;$this->prepareView('edit');return $this->jsonResponse(['result'=>true,'output'=>view(Views::EDIT_PARTIAL,$this->data)]);
            endif;
            if ( ! $this->referenceAccess($posts)): return $this->denied(); endif;
            $result=model(TaskModalModel::class)->saveForm($posts,$task,$this->currentAdmin->uuid??null,$this->allows('tasks_complete'),$this->notePermissions());
            if ($result['result']): $this->audit('EDIT_TASKS',$posts['uuid']); endif;
            unset($result['record']);return $this->jsonResponse($this->format($result));
        endif;
        $this->data['task']=$this->taskOr404($uuid);
        if ($this->data['task']->deleted_at!==null || $this->data['task']->owner_deleted_at!==null || $this->data['task']->archived_at!==null): throw PageNotFoundException::forPageNotFound(); endif;
        $this->prepareView('edit');return $this->render($this->taskView('EDIT_VIEW'),$this->data);
    }

    /**
     * @param string $uuid Task richiesto.
     * @return string|ResponseInterface Scheda, leggibile anche dal cestino per gestire i collegamenti.
     */
    public function show(string $uuid): string|ResponseInterface
    {
        if ( ! $this->allows('tasks_show')): return $this->denied(); endif;
        $this->data['task']=$this->taskOr404($uuid);$this->prepareView('show');return $this->render($this->taskView('SHOW_VIEW'),$this->data);
    }


    /**
     * @return ResponseInterface Ricarica campi e passi mantenendo il contesto della vista.
     */
    public function getGeneralData(): ResponseInterface
    {
        if ( ! $this->readAccess()): return $this->denied(); endif;
        $posts=$this->request->getPost();$rules=$this->tasksModel->delValidationRules()+['context'=>['rules'=>['required','string','in_list[edit,show]']]];
        if ( ! $this->validateData($posts,$rules)): return $this->validation('action'); endif;
        $this->data['task']=$this->taskOr404($posts['uuid']);$this->prepareView($posts['context']);
        return $this->jsonResponse(['result'=>true,'output'=>view($posts['context']==='edit'?Views::EDIT_GENERAL_DATA_PARTIAL:Views::SHOW_GENERAL_DATA_PARTIAL,$this->data)]);
    }

    /**
     * @return ResponseInterface Metadati persistiti del task.
     */
    public function getMetaData(): ResponseInterface
    {
        if ( ! $this->readAccess()): return $this->denied(); endif;
        $posts=$this->request->getPost();
        if ( ! $this->validateData($posts,$this->tasksModel->delValidationRules())): return $this->validation('action'); endif;
        $this->data['task']=$this->taskOr404($posts['uuid']);return $this->jsonResponse(['result'=>true,'output'=>view(Views::META_DATA_PARTIAL,$this->data)]);
    }

    /**
     * @return ResponseInterface Cestina il task senza cancellarne i dati.
     */
    public function softDelete(): ResponseInterface { return $this->deleteAction('softDelete'); }
    /**
     * @return ResponseInterface Ripristina un task nel suo contesto attivo.
     */
    public function restoreDelete(): ResponseInterface { return $this->deleteAction('restoreDelete'); }
    /**
     * @return ResponseInterface Cancella solo un task cestinato senza riferimenti.
     */
    public function hardDelete(): ResponseInterface { return $this->deleteAction('hardDelete'); }
    /**
     * @return ResponseInterface Completa tutti i passi e libera il task corrente.
     */
    public function complete(): ResponseInterface { return $this->completion(true,false); }
    /**
     * @return ResponseInterface Riapre soltanto l'ultimo passo.
     */
    public function reopen(): ResponseInterface { return $this->completion(false,false); }
    /**
     * @return ResponseInterface Completa il passo ricevuto dopo verifica di appartenenza.
     */
    public function completeStep(): ResponseInterface { return $this->completion(true,true); }
    /**
     * @return ResponseInterface Riapre un passo e aggiorna lo stato del task.
     */
    public function reopenStep(): ResponseInterface { return $this->completion(false,true); }

    /** @return ResponseInterface Selezione personale con UUID e contesto verificati prima della scrittura. */
    public function setCurrent(): ResponseInterface
    {
        if ( ! $this->allows('tasks_set_current') || ! $this->readAccess()): return $this->denied(); endif;
        $posts=$this->request->getPost();
        if ($failure=$this->prepareAction($posts)): return $failure; endif;
        if (array_diff(array_keys($posts),['uuid'])): return $this->jsonResponse(['result'=>false,'message'=>lang('backend/tasks.messages.invalidData')]); endif;
        if ( ! $this->validateData($posts,$this->tasksModel->delValidationRules())): return $this->validation('action'); endif;
        $this->taskOr404($posts['uuid']);
        $result=$this->tasksModel->setCurrent($posts['uuid'],(string)$this->currentAdmin->uuid,$this->studioContext);
        if ($result['result']): $this->audit('CURRENT_TASKS',$result['uuid']); endif;
        unset($result['record']);
        return $this->actionResponse($result);
    }

    /**
     * Ricarica separatamente le note del task e quelle di ciascun passo, senza toccare il form Edit.
     * @return ResponseInterface Partial delle note raggruppati per proprietario reale.
     */
    public function getNotes(): ResponseInterface
    {
        if ( ! $this->readAccess() || ! $this->allows('notes_show')): return $this->denied(); endif;
        $posts=$this->request->getPost();
        if ( ! $this->validateData($posts,$this->tasksModel->delValidationRules())): return $this->validation('action'); endif;
        $this->data['task']=$this->taskOr404($posts['uuid']); $this->prepareView('show');
        $noteData = ['task'=>$this->data['task'], 'currentAdmin'=>$this->currentAdmin, 'taskNotes'=>$this->data['taskNotes'], 'notesForStep'=>false];
        // Il renderer conserva i dati per impostazione predefinita: ogni partial riceve il proprio contesto esplicito.
        $output = view(Views::NOTES_PARTIAL, $noteData, ['saveData'=>false]);
        $steps = [];
        foreach ($this->data['task']->steps as $step):
            $stepData = array_replace($noteData, ['taskNotes'=>$this->data['stepNotes'][$step->uuid]??[], 'notesForStep'=>true]);
            $steps[$step->uuid] = view(Views::NOTES_PARTIAL, $stepData, ['saveData'=>false]);
        endforeach;
        return $this->jsonResponse(['result'=>true, 'output'=>$output, 'stepOutputs'=>$steps]);
    }

    /**
     * @return ResponseInterface Salva una nota del task o di un suo passo con i permessi Notes.
     */
    public function saveNote(): ResponseInterface
    {
        $posts=$this->request->getPost();
        if ($failure=$this->prepareAction($posts)): return $failure; endif;
        $rules=['task_uuid'=>$this->tasksModel->delValidationRules()['uuid'],'step_uuid'=>['rules'=>['permit_empty','string',$this->uuidRule()]],'uuid'=>['rules'=>['permit_empty','string',$this->uuidRule()]]];
        foreach (['uuid','step_uuid'] as $key): if (array_key_exists($key,$posts) && ! is_string($posts[$key])): return $this->jsonResponse($this->format(['result'=>false,'errors'=>[$key=>lang('backend/tasks.messages.invalidData')]],'action')); endif; endforeach;
        if ( ! $this->validateData($posts,$rules)): return $this->validation('action'); endif;
        if ( ! $this->readAccess() || ! $this->allows(empty($posts['uuid'])?'notes_add':'notes_edit')): return $this->denied(); endif;
        $noteOwner=$this->taskOr404($posts['task_uuid']);
        if ($noteOwner->archived_at !== null): return $this->jsonResponse(['result'=>false,'message'=>lang('backend/tasks.archive.readOnly')]); endif;
        if ( ! empty($posts['step_uuid']) && ! $this->stepBelongs($posts['step_uuid'],$posts['task_uuid'])): return $this->denied(); endif;
        $taskUuid=$posts['task_uuid'];$stepUuid=$posts['step_uuid']??'';unset($posts['task_uuid'],$posts['step_uuid']);
        $result=$stepUuid!==''?$this->notesModel->saveForStep($posts,$stepUuid,$this->currentAdmin->uuid??null):$this->notesModel->saveForTask($posts,$taskUuid,$this->currentAdmin->uuid??null);
        if ($result['result']): $this->audit('SAVE_TASK_NOTE',$result['record']->uuid); endif;
        if ($result['result']): $result['uuid']=$result['record']->uuid; endif;
        unset($result['record']);return $this->actionResponse($this->format($result,'modal'));
    }

    /**
     * Verifica permessi e parametri nascosti prima di eliminare definitivamente una nota.
     * @return ResponseInterface Esito con dettagli escapati in caso di manomissione.
     */
    public function deleteNote(): ResponseInterface
    {
        if ( ! $this->readAccess() || ! $this->allows('notes_delete')): return $this->denied(); endif;
        $posts=$this->request->getPost();
        if ($failure=$this->prepareAction($posts)): return $failure; endif;
        if (array_diff(array_keys($posts), ['uuid','task_uuid'])): return $this->jsonResponse(['result'=>false,'message'=>lang('backend/tasks.messages.invalidData')]); endif;
        $rules=$this->tasksModel->delValidationRules()+['task_uuid'=>$this->tasksModel->delValidationRules()['uuid']];
        if ( ! $this->validateData($posts,$rules)): return $this->validation('action'); endif;
        $noteOwner=$this->taskOr404($posts['task_uuid']);
        if ($noteOwner->archived_at !== null): return $this->jsonResponse(['result'=>false,'message'=>lang('backend/tasks.archive.readOnly')]); endif;
        $result=$this->notesModel->deleteTaskNote($posts['uuid'],$posts['task_uuid']);
        if ($result['result']): $this->audit('DELETE_TASK_NOTE',$posts['uuid']); endif;
        return $this->actionResponse($result);
    }

    /** @return ResponseInterface Archivia un task autorizzato, anche non completato. */
    public function archive(): ResponseInterface { return $this->archiveAction(true); }
    /** @return ResponseInterface Recupera il task conservandone stato e contenuti. */
    public function unarchive(): ResponseInterface { return $this->archiveAction(false); }

    /**
     * Valida tutti i parametri nascosti prima di avviare la transazione.
     * @param bool $archive Azione fissata dalla rotta, mai dal payload.
     * @return ResponseInterface Errori dettagliati nell'alert oppure esito e dati aggiornati.
     */
    private function archiveAction(bool $archive): ResponseInterface
    {
        if ( ! $this->allows('tasks_archive') || ! $this->readAccess()): return $this->denied(); endif;
        $posts=$this->request->getPost();
        if ($failure=$this->prepareAction($posts)): return $failure; endif;
        if (array_diff(array_keys($posts), ['uuid'])): return $this->jsonResponse(['result'=>false,'message'=>lang('backend/tasks.messages.invalidData')]); endif;
        if ( ! $this->validateData($posts,$this->tasksModel->delValidationRules())): return $this->validation('action'); endif;
        $this->taskOr404($posts['uuid']);
        $result=$this->tasksModel->changeArchive($posts['uuid'],$archive);
        if ($result['result']): $this->audit($archive?'ARCHIVE_TASKS':'UNARCHIVE_TASKS',$posts['uuid']); endif;
        if ($result['result']):
            $this->data['task']=$result['record']; $this->prepareView('show');
            $result['notesPanelOutput']=view(Views::NOTES_PANEL_PARTIAL,$this->data,['saveData'=>false]);
            $result['linksBarOutput']=view('backend/template/linksBarView',['linksBar'=>$this->tasksClass->getLinksBarShow($posts['uuid'])],['saveData'=>false]);
        endif;
        unset($result['record']);
        $result['navigationCounts']=\Modules\Backend\Template\Libraries\NavigationClass::counts($this->currentAdmin);
        return $this->actionResponse($result);
    }

    /**
     * @param string $action Azione interna.
     * @return ResponseInterface Esito della transizione autorizzata.
     */
    private function deleteAction(string $action): ResponseInterface
    {
        if ( ! $this->allows($action==='restoreDelete'?'tasks_restore':'tasks_delete')): return $this->denied(); endif;
        $posts=$this->request->getPost();
        if ( ! $this->validateData($posts,$this->tasksModel->delValidationRules())): return $this->validation('action'); endif;
        $this->taskOr404($posts['uuid']);$result=$this->tasksModel->changeDeletion($posts['uuid'],$action);
        if ($result['result']): $this->audit(strtoupper($action).'_TASKS',$posts['uuid']); endif;
        return $this->jsonResponse($result);
    }

    /**
     * @param bool $complete Stato richiesto.
     * @param bool $step Azione su un passo.
     * @return ResponseInterface Parametri, checkbox e appartenenza validati lato server.
     */
    private function completion(bool $complete,bool $step): ResponseInterface
    {
        if ( ! $this->allows('tasks_complete')): return $this->denied(); endif;
        $posts=$this->request->getPost();
        if ($failure=$this->prepareAction($posts)): return $failure; endif;
        $rules=$this->tasksModel->delValidationRules();
        if ($step): $rules+=['step_uuid'=>$rules['uuid'],'completed'=>['rules'=>['required','string','in_list['.($complete?'1':'0').']']]]; endif;
        if ( ! $this->validateData($posts,$rules)): return $this->validation('action'); endif;
        $this->taskOr404($posts['uuid']);$result=$this->tasksModel->changeCompletion($posts['uuid'],$step?$posts['step_uuid']:null,$complete,(string)$this->currentAdmin->uuid);
        if ($result['result']): $this->audit($step?'CHANGE_TASK_STEP':'CHANGE_TASK_STATUS',$posts['uuid']); endif;
        return $this->actionResponse($result['result']?['result'=>true,'uuid'=>$posts['uuid'],'message'=>$result['message']]:$result);
    }

    /**
     * Consente alle sezioni derivate di validare e consumare lo stato della lista prima della mutazione.
     * @param array $posts Campi ricevuti, modificati solo per rimuovere dati di presentazione validati.
     * @return ResponseInterface|null Errore oppure null per proseguire.
     */
    protected function prepareAction(array &$posts): ?ResponseInterface
    {
        return array_key_exists('studioList',$posts) ? $this->jsonResponse(['result'=>false,'message'=>lang('backend/tasks.messages.invalidData')]) : null;
    }

    /** @param array $result Esito autorizzato della mutazione. @return ResponseInterface Risposta, estendibile con partial senza nuove richieste. */
    protected function actionResponse(array $result): ResponseInterface
    {
        return $this->jsonResponse($result);
    }

    /**
     * @param string $action Vista.
     * @return void Prepara dati reali e capacità dei contesti.
     */
    private function prepareView(string $action): void
    {
        $this->data['navigationCounts']=\Modules\Backend\Template\Libraries\NavigationClass::counts($this->currentAdmin);
        $this->data['archiveList']=$this->archiveList;
        $this->tasksClass->setArchiveContext($this->archiveList || ($this->data['task']->archived_at ?? null) !== null);
        $this->data['action']=$action;$this->data['title']=lang('backend/tasks.titles.'.$action);$this->data['icon']='<i class="fa-solid fa-list-check"></i>';$this->data['options']='';
        if ($this->archiveList): $this->data['title']=lang('backend/tasks.archive.title'); endif;
        $task=$this->data['task']??null;$this->data['uuid']=$task->uuid??null;
        $isStudio=$this->studioContext;
        $this->data['studioTaskContext']=$isStudio;
        $this->data['navigationSection']=$isStudio?'studio':'tasks';
        $this->tasksClass->setStudioContext($isStudio);
        $this->data['ownerOptions']=$this->tasksModel->ownerOptions($this->projectAccess(),$this->applicationAccess());
        $this->data['taskNotes']=[]; $this->data['stepNotes']=[];
        if ($task && $this->allows('notes_show')):
            foreach ($this->notesModel->getTaskNotes($task->uuid) as $note):
                if ($note->step_uuid !== null): $this->data['stepNotes'][$note->step_uuid][]=$note;
                else: $this->data['taskNotes'][]=$note; endif;
            endforeach;
        endif;
        if ($action === 'showAll'): $this->data['taskOverview']=$this->tasksModel->overview($this->projectAccess(),$this->applicationAccess()); endif;
        $this->data['currentTask']=null;
        if ($this->readAccess() && is_string($this->currentAdmin->uuid??null)):
            $current=$this->tasksModel->getCurrent($this->currentAdmin->uuid,$this->studioContext);
            if ($current && $this->contextAccess(TasksModel::ownerContext($current)[0])): $this->data['currentTask']=$current; endif;
        endif;
    }

    /**
     * Sceglie la pagina del contesto senza duplicare CRUD, validazioni o partial.
     * @param string $name Costante della vista, stabilita dal controller.
     * @return string Vista propria di Studio oppure vista delle attività.
     */
    protected function taskView(string $name): string
    {
        return constant(($this->studioContext ? \Modules\Backend\Studio\Config\Views::class : Views::class) . '::' . $name);
    }

    /**
     * @param string|null $uuid Identificativo richiesto.
     * @return object Task leggibile nel contesto autorizzato oppure 404.
     */
    private function taskOr404(?string $uuid): object
    {
        if ($uuid===null || ! $this->validateData(['uuid'=>$uuid],$this->tasksModel->delValidationRules())): throw PageNotFoundException::forPageNotFound(); endif;
        $result=$this->tasksModel->getByUUID($uuid);
        if ( ! $result['result'] || ((int)$result['row']->is_studio===1)!==$this->studioContext || ! $this->contextAccess(TasksModel::ownerContext($result['row'])[0])): throw PageNotFoundException::forPageNotFound(); endif;
        return $result['row'];
    }

    /**
     * @param string $stepUuid Passo.
     * @param string $taskUuid Task della scheda.
     * @return bool Verifica senza usare identità ricevute come autorevoli.
     */
    private function stepBelongs(string $stepUuid,string $taskUuid): bool
    {
        foreach ($this->tasksModel->getByUUID($taskUuid)['row']->steps as $step): if ($step->uuid===$stepUuid): return true; endif; endforeach;return false;
    }
    /**
     * Verifica il permesso sul riferimento scelto senza consentire cambi di proprietario.
     * @param array $posts Form ricevuto; il model aggrega gli errori di formato.
     * @return bool False solo per un riferimento valido ma non autorizzato.
     */
    private function referenceAccess(array $posts): bool
    {
        if ( ! array_key_exists('reference', $posts)): return true; endif;
        $reference = model(\Modules\Backend\Studio\Models\StudioReferencesModel::class)->parse($posts['reference']);
        if ($reference === null || $reference[0] === ''): return true; endif;
        if ( ! $this->studioContext): return false; endif;
        $original = $this->data['task'] ?? null;
        if (isset($posts['uuid'])):
            $row = $this->tasksModel->getByUUID($posts['uuid']);
            $original = $row['result'] ? $row['row'] : null;
        endif;
        // Consente di conservare un riferimento esistente anche dopo una revoca dei permessi.
        if (($original->reference->value ?? null) === $posts['reference']): return true; endif;
        return $reference[0] === 'project' ? $this->projectAccess() : $this->applicationAccess();
    }

    /**
     * @return bool Accesso minimo alla scheda.
     */
    private function readAccess(): bool { return (! $this->studioContext || $this->allows('studio_show')) && ($this->allows('tasks_show')||$this->allows('tasks_edit')); }
    /**
     * @return bool Accesso al contesto progetti.
     */
    private function projectAccess(): bool { return $this->allows('projects_show_all')||$this->allows('projects_show')||$this->allows('projects_edit'); }
    /**
     * @return bool Accesso al contesto applicazioni.
     */
    private function applicationAccess(): bool { return $this->allows('applications_show_all')||$this->allows('applications_show')||$this->allows('applications_edit'); }
    /**
     * @param string $column Contesto interno.
     * @return bool Autorizzazione del proprietario.
     */
    private function contextAccess(string $column): bool { return match ($column) { 'project_uuid' => $this->projectAccess(), 'application_uuid' => $this->applicationAccess(), 'is_studio' => $this->allows('studio_show'), default => false }; }
    /**
     * @param string $permission Permesso.
     * @return bool Decisione del backbone.
     */
    private function allows(string $permission): bool { return AuthorizationClass::allows($this->currentAdmin,$permission); }
    /**
     * @return ResponseInterface Rifiuto privo di dati del record.
     */
    private function denied(): ResponseInterface { return $this->jsonResponse(['result'=>false,'message'=>lang('backend/tasks.messages.forbidden')])->setStatusCode(403); }
    /**
     * @return string Regola statica UUID.
     */
    private function uuidRule(): string { return $this->tasksModel->delValidationRules()['uuid']['rules'][2]; }
    /**
     * @param string $context Tipo di form interno.
     * @return ResponseInterface Errori validati.
     */
    private function validation(string $context='form'): ResponseInterface { return $this->jsonResponse($this->format(['result'=>false,'errors'=>$this->validator->getErrors()],$context)); }
    /**
     * @param array $result Esito.
     * @param string $context form, modal o action.
     * @return array Messaggio generico nei form, dettagli escapati nelle azioni nascoste.
     */
    private function format(array $result,string $context='form'): array
    {
        if ( ! isset($result['errors'])): return $result; endif;
        if ($context==='action'): $result['message']=implode('<br>',array_map(static fn($error):string=>esc((string)$error),$result['errors']));return $result; endif;
        if ($context==='form'):
            $mapped=[];
            foreach ($result['errors'] as $field=>$error):
                if (preg_match('/^steps\.(\d+)\.title$/',(string)$field,$match)): $key='step_'.$match[1].'_title';
                elseif (preg_match('/^steps\.\d+\.notes\.\d+\.content$/',(string)$field)): $key=str_replace('.', '_', (string)$field);
                elseif (preg_match('/^notes\.\d+\.content$/',(string)$field)): $key=str_replace('.', '_', (string)$field);
                elseif (str_starts_with((string)$field,'steps.')): $key='steps';
                else: $key=preg_replace('/^(searchFields|searchDates)\./','',(string)$field); endif;
                $mapped[$key]=isset($mapped[$key])?$mapped[$key].' '.$error:$error;
            endforeach;$result['errors']=$mapped;
        endif;
        $result['message']=lang('backend/tasks.messages.validationErrors');return $result;
    }
    /** @return array<string, bool> Permessi delle note ricavati esclusivamente dall'amministratore corrente. */
    private function notePermissions(): array
    {
        return ['add'=>$this->allows('notes_add'), 'edit'=>$this->allows('notes_edit'), 'delete'=>$this->allows('notes_delete'), 'show'=>$this->allows('notes_show')];
    }

    /**
     * @param string $action Operazione completata.
     * @param string $uuid Identificativo reale.
     * @return void Registra solo mutazioni riuscite.
     */
    private function audit(string $action,string $uuid): void { log_admin_activity($action,'tasks',esc($uuid),$this->currentAdmin); }
}
