<?php declare(strict_types = 1);

namespace Modules\Backend\Studio\Libraries;

use App\Libraries\Backend\AuthorizationClass;
use Modules\Backend\Studio\Config\Views;
use Modules\Backend\Studio\Models\StudioModel;
use Modules\Backend\Tasks\Models\TasksModel;

/** Collegamenti e partial Studio riusati dalla pagina e dalle risposte delle mutazioni. */
class StudioClass
{
    private ?array $viewData = null;

    /** @param object|null $admin Identità autenticata, mai ricevuta dal form. */
    public function __construct(protected ?object $admin = null) {}

    /** @return array Collegamenti per pagina Task e modale Note. */
    public function getLinksBarIndex(): array
    {
        $links=[];
        if ($this->allows('tasks_add')): $links[]=['icon'=>'<i class="fa-solid fa-plus"></i>','label'=>lang('backend/studio.newTask'),'route'=>'backend/studio/tasks/add']; endif;
        if ($this->allows('notes_add')): $links[]=['icon'=>'<i class="fa-solid fa-note-sticky"></i>','label'=>lang('backend/studio.newNote'),'route'=>'backend/studio#add-note']; endif;
        return $links;
    }

    /** @return array Capacità e corrente personale letti una sola volta per risposta. */
    public function data(): array
    {
        if ($this->viewData!==null): return $this->viewData; endif;
        $details=$this->allows('tasks_show')||$this->allows('tasks_edit');
        return $this->viewData=[
            'currentAdmin'=>$this->admin,
            'canReadTasks'=>$details||$this->allows('tasks_show_all'), 'canReadTaskDetails'=>$details,
            'canReadNotes'=>$this->allows('notes_show'), 'canEditTasks'=>$this->allows('tasks_edit'), 'canDeleteTasks'=>$this->allows('tasks_delete'),
            'canEditNotes'=>$this->allows('notes_edit'), 'canDeleteNotes'=>$this->allows('notes_delete'),
            'currentTask'=>$details ? model(TasksModel::class)->getCurrent((string)$this->admin->uuid,true) : null,
        ];
    }

    /** @return string Riepilogo Studio con gli stessi componenti della lista Tasks. */
    public function overview(): string
    {
        return view(\Modules\Backend\Tasks\Config\Views::OVERVIEW_PARTIAL,array_merge($this->data(),[
            'taskOverview'=>model(StudioModel::class)->overview(),'taskRouteBase'=>'backend/studio/tasks/',
        ]),['saveData'=>false]);
    }

    /**
     * Renderizza solo record della pagina autorizzata, espandendo esclusivamente quelli aperti dall'utente.
     * @param string $kind Sezione interna tasks oppure notes.
     * @param array $state Stato già validato dal model.
     * @return array Tipo, stato corretto e HTML completo di due paginazioni.
     */
    public function listing(string $kind,array $state): array
    {
        $data=$this->data();
        if ( ! ($kind==='tasks'?$data['canReadTasks']:$data['canReadNotes'])): throw new \LogicException('Unauthorized Studio list.'); endif;
        $listing=model(StudioModel::class)->listing($kind,$state);
        $output=view(Views::LIST_PARTIAL,array_merge($data,[
            'kind'=>$kind,'listing'=>$listing,'openedTasks'=>$data['canReadTaskDetails']?$state['opened']:[],
        ]),['saveData'=>false]);
        return ['kind'=>$kind,'state'=>$listing['state'],'output'=>$output];
    }

    /** @param object $task Task Studio verificato. @return string Pannello richiesto esplicitamente dall'utente. */
    public function taskPanel(object $task): string
    {
        return view(Views::TASK_PARTIAL,array_merge($this->data(),['studioTask'=>$task,'openedTasks'=>[$task->uuid]]),['saveData'=>false]);
    }

    /** @param string $permission Permesso interno. @return bool Autorizzazione del backbone. */
    private function allows(string $permission): bool { return AuthorizationClass::allows($this->admin,$permission); }
}
