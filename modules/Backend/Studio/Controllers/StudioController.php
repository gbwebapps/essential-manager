<?php declare(strict_types = 1);

namespace Modules\Backend\Studio\Controllers;

use App\Controllers\Backend\BackendController;
use App\Libraries\Backend\AuthorizationClass;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Modules\Backend\Studio\Config\Views;
use Modules\Backend\Studio\Libraries\StudioClass;
use Modules\Backend\Studio\Models\StudioModel;
use Modules\Backend\Tasks\Models\TasksModel;
use Modules\Backend\Notes\Models\NotesModel;
use Modules\Backend\Template\Libraries\NavigationClass;
use Psr\Log\LoggerInterface;

/** Studio: liste essenziali AJAX e contesto di proprietà imposto dal server. */
class StudioController extends BackendController
{
    protected StudioModel $studioModel;
    protected StudioClass $studioClass;
    protected TasksModel $tasksModel;
    protected NotesModel $notesModel;
    protected array $actionErrors=[];

    /** @param RequestInterface $request Richiesta. @param ResponseInterface $response Risposta. @param LoggerInterface $logger Log. @return void */
    public function initController(RequestInterface $request,ResponseInterface $response,LoggerInterface $logger)
    {
        parent::initController($request,$response,$logger);
        $this->studioModel=model(StudioModel::class);
        $this->studioClass=new StudioClass($this->currentAdmin);
        $this->tasksModel=model(TasksModel::class);
        $this->notesModel=model(NotesModel::class);
        $this->data['controller']='studio';$this->data['entity']='studio';$this->data['action']='index';
        $this->data['title']=lang('backend/studio.title');$this->data['icon']='<i class="fa-solid fa-building"></i>';
        $this->data['uuid']=null;$this->data['options']='';
    }

    /** @param string $tab Sezione imposta dalla rotta. @return string|ResponseInterface Struttura delle liste AJAX; pannelli inizialmente chiusi. */
    public function index(string $tab = 'tasks'): string|ResponseInterface
    {
        if ( ! $this->allows('studio_show')): return $this->denied(); endif;
        $data=$this->studioClass->data();
        $data['studioTab']=$tab==='notes' && $data['canReadNotes'] ? 'notes' : ($data['canReadTasks']?'tasks':'notes');
        $data['studioOverview']=$data['canReadTasks']?$this->studioClass->overview():'';
        $data['taskCount']=$data['canReadTasks']?$this->studioModel->taskCount():0;
        $data['noteCount']=$data['canReadNotes']?$this->studioModel->noteCount():0;
        return $this->render(Views::INDEX_VIEW,$data);
    }

    /** @return ResponseInterface Pagina di task con ricerca letterale nel titolo e ordinamento per data. */
    public function getTasks(): ResponseInterface { return $this->getItems('tasks'); }
    /** @return ResponseInterface Pagina di note con ricerca letterale nel contenuto e ordinamento per data. */
    public function getNotes(): ResponseInterface { return $this->getItems('notes'); }

    /** @param string $kind Sezione interna. @return ResponseInterface HTML paginato e stato normalizzato dal server. */
    private function getItems(string $kind): ResponseInterface
    {
        if ( ! $this->allows('studio_show') || ! $this->canRead($kind)): return $this->denied(); endif;
        $posts=$this->request->getPost();
        if (array_diff(array_keys($posts),['studioList'])): return $this->invalidPayload(); endif;
        $checked=$this->studioModel->validateList($posts['studioList']??null);
        if ( ! $checked['result']): return $this->jsonResponse(['result'=>false,'message'=>lang('backend/tasks.messages.validationErrors'),'errors'=>$checked['errors']]); endif;
        return $this->jsonResponse($this->withList(['result'=>true],$kind,$checked['state']));
    }

    /** @return ResponseInterface Pannello completo caricato solo quando l'utente apre un task Studio. */
    public function getTask(): ResponseInterface
    {
        if ( ! $this->allows('studio_show') || ! ($this->allows('tasks_show')||$this->allows('tasks_edit'))): return $this->denied(); endif;
        $posts=$this->request->getPost();
        if ( ! $this->validateAction($posts,$this->tasksModel->delValidationRules(),['uuid'])): return $this->invalidAction(); endif;
        $task=$this->studioModel->getTask($posts['uuid']);
        return $task ? $this->jsonResponse(['result'=>true,'output'=>$this->studioClass->taskPanel($task)]) : $this->missing();
    }

    /** @return ResponseInterface Nota autonoma e lista ricalcolata, senza modificare i filtri dell'utente. */
    public function saveNote(): ResponseInterface
    {
        if ( ! $this->allows('studio_show')): return $this->denied(); endif;
        $posts=$this->request->getPost();
        $editing=is_string($posts['uuid']??null)&&$posts['uuid']!=='';
        if ( ! $this->allows($editing?'notes_edit':'notes_add')): return $this->denied(); endif;
        $state=$this->consumeState($posts,'notes');
        if ($state instanceof ResponseInterface): return $state; endif;
        $result=$this->notesModel->saveForStudio($posts,$this->currentAdmin->uuid??null);
        if (($result['message']??'')===lang('backend/notes.messages.notFound')): $result['message']=lang('backend/studio.notFound'); endif;
        if ($result['result']):
            $this->audit($editing?'EDIT_NOTES':'ADD_NOTES',$result['record']->uuid);
            $result=$this->withList($result,'notes',$state);
        elseif (isset($result['errors'])): $result['message']=lang('backend/tasks.messages.validationErrors'); endif;
        return $this->jsonResponse($result);
    }

    /** @return ResponseInterface Cancellazione definitiva con conferma; contenuti della pagina restituiti nella stessa risposta. */
    public function deleteTask(): ResponseInterface { return $this->deleteItem('tasks'); }
    /** @return ResponseInterface Cancellazione definitiva della sola nota autonoma Studio autorizzata. */
    public function deleteNote(): ResponseInterface { return $this->deleteItem('notes'); }

    /** @param string $kind Tipo imposto dalla rotta. @return ResponseInterface Esito con parametri nascosti validati. */
    private function deleteItem(string $kind): ResponseInterface
    {
        if ( ! $this->allows('studio_show') || ! $this->allows($kind==='tasks'?'tasks_delete':'notes_delete')): return $this->denied(); endif;
        $posts=$this->request->getPost();
        $state=$this->consumeState($posts,$kind);
        if ($state instanceof ResponseInterface): return $state; endif;
        if ( ! $this->validateAction($posts,$this->tasksModel->delValidationRules(),['uuid'])): return $this->invalidAction(); endif;
        $result=$kind==='tasks'?$this->studioModel->deleteTask($posts['uuid']):$this->notesModel->deleteForOwner($posts['uuid'],'is_studio','1');
        if (($result['message']??'')===lang('backend/notes.messages.notFound')): $result['message']=lang('backend/studio.notFound'); endif;
        if ($result['result']):
            $this->audit($kind==='tasks'?'HARDDELETE_TASKS':'HARDDELETE_NOTES',$posts['uuid']);
            $result=$this->withList($result,$kind,$state);
        endif;
        return $this->jsonResponse($result);
    }

    /**
     * Consuma lo stato presentazionale prima di passare i campi al model di scrittura.
     * @param array $posts Form non attendibile, modificato per togliere studioList.
     * @param string $kind Lista autorizzata.
     * @return array|ResponseInterface|null Stato validato, errore o assenza di una lista incorporata.
     */
    private function consumeState(array &$posts,string $kind): array|ResponseInterface|null
    {
        if ( ! array_key_exists('studioList',$posts)): return null; endif;
        if ( ! $this->canRead($kind)): return $this->denied(); endif;
        $checked=$this->studioModel->validateList($posts['studioList']);unset($posts['studioList']);
        if ($checked['result']): return $checked['state']; endif;
        $this->actionErrors=$checked['errors'];return $this->invalidAction();
    }

    /** @param array $result Esito reale. @param string $kind Lista. @param array|null $state Stato validato. @return array Risposta senza successive chiamate di aggiornamento. */
    private function withList(array $result,string $kind,?array $state): array
    {
        if ($state!==null): $result['studioList']=$this->studioClass->listing($kind,$state); endif;
        if ($kind==='tasks' && $this->canRead('tasks')): $result['studioOverview']=$this->studioClass->overview(); endif;
        $result['navigationCounts']=NavigationClass::counts($this->currentAdmin);
        if ($this->allows('notes_show')): $result['noteCount']=$this->studioModel->noteCount(); endif;
        return $result;
    }

    /** @param array $posts Campi. @param array $rules Regole comuni. @param array $allowed Nomi ammessi. @return bool Validità complessiva. */
    private function validateAction(array $posts,array $rules,array $allowed): bool
    {
        $this->actionErrors=[];
        $unexpected=array_diff(array_keys($posts),$allowed);
        if ($unexpected): $this->actionErrors['payload']=lang('backend/studio.unexpectedFields',[implode(', ',$unexpected)]); endif;
        $valid=$this->validateData($posts,$rules);
        if ( ! $valid): $this->actionErrors+=$this->validator->getErrors(); endif;
        return $valid && !$unexpected;
    }

    /** @return ResponseInterface Errori nascosti riportati nell'alert e sempre escapati. */
    private function invalidAction(): ResponseInterface
    {
        return $this->jsonResponse(['result'=>false,'message'=>implode('<br>',array_map(static fn($error):string=>esc((string)$error),$this->actionErrors))]);
    }
    /** @return ResponseInterface Payload della ricerca manomesso. */
    private function invalidPayload(): ResponseInterface { return $this->jsonResponse(['result'=>false,'message'=>lang('backend/tasks.messages.invalidData')]); }
    /** @param string $kind Sezione interna. @return bool Capacità di lettura stabilita dal backbone. */
    private function canRead(string $kind): bool { return $kind==='tasks' ? ($this->allows('tasks_show_all')||$this->allows('tasks_show')||$this->allows('tasks_edit')) : $this->allows('notes_show'); }
    /** @param string $permission Permesso interno. @return bool Autorizzazione comune. */
    private function allows(string $permission): bool { return AuthorizationClass::allows($this->currentAdmin,$permission); }
    /** @return ResponseInterface Accesso rifiutato senza contenuti. */
    private function denied(): ResponseInterface { return $this->jsonResponse(['result'=>false,'message'=>lang('backend/tasks.messages.forbidden')],403); }
    /** @return ResponseInterface Task inesistente o di un altro contesto. */
    private function missing(): ResponseInterface { return $this->jsonResponse(['result'=>false,'message'=>lang('backend/studio.notFound')],404); }
    /** @param string $action Operazione riuscita. @param string $uuid Identificativo persistito. @return void Audit dopo la mutazione. */
    private function audit(string $action,string $uuid): void { log_admin_activity($action,'studio',esc($uuid),$this->currentAdmin); }
}
