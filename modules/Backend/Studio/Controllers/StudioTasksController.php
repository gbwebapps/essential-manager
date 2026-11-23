<?php declare(strict_types = 1);

namespace Modules\Backend\Studio\Controllers;

use App\Libraries\Backend\AuthorizationClass;
use CodeIgniter\HTTP\ResponseInterface;
use Modules\Backend\Studio\Libraries\StudioClass;
use Modules\Backend\Studio\Models\StudioModel;
use Modules\Backend\Tasks\Controllers\TasksController;
use Modules\Backend\Template\Libraries\NavigationClass;

/** CRUD Studio con viste proprie e liste aggiornate nella stessa risposta delle azioni. */
class StudioTasksController extends TasksController
{
    protected bool $studioContext = true;
    protected ?array $studioListState = null;

    /**
     * Consuma e valida lo stato della lista prima della scrittura condivisa.
     * @param array $posts Form modificato solo per rimuovere studioList, mai dati di proprietà.
     * @return ResponseInterface|null Errore validato oppure null per continuare il CRUD comune.
     */
    protected function prepareAction(array &$posts): ?ResponseInterface
    {
        $this->studioListState=null;
        if ( ! array_key_exists('studioList',$posts)): return null; endif;
        $canRead=AuthorizationClass::allows($this->currentAdmin,'studio_show') && (
            AuthorizationClass::allows($this->currentAdmin,'tasks_show_all') || AuthorizationClass::allows($this->currentAdmin,'tasks_show') || AuthorizationClass::allows($this->currentAdmin,'tasks_edit'));
        if ( ! $canRead): return $this->jsonResponse(['result'=>false,'message'=>lang('backend/tasks.messages.forbidden')],403); endif;
        $checked=model(StudioModel::class)->validateList($posts['studioList']);
        unset($posts['studioList']);
        if ( ! $checked['result']):
            return $this->jsonResponse(['result'=>false,'message'=>implode('<br>',array_map(static fn($error):string=>esc((string)$error),$checked['errors']))]);
        endif;
        $this->studioListState=$checked['state'];
        return null;
    }

    /**
     * Aggiunge la pagina corrente e il riepilogo solo per le azioni inviate dall'elenco Studio.
     * @param array $result Esito del salvataggio comune già autorizzato.
     * @return ResponseInterface Un'unica risposta; le viste autonome mantengono il proprio refresh.
     */
    protected function actionResponse(array $result): ResponseInterface
    {
        if (($result['result']??false) && $this->studioListState!==null):
            $studio=new StudioClass($this->currentAdmin);
            $result['studioList']=$studio->listing('tasks',$this->studioListState);
            $result['studioOverview']=$studio->overview();
            $result['navigationCounts']=NavigationClass::counts($this->currentAdmin);
        endif;
        return $this->jsonResponse($result);
    }
}
