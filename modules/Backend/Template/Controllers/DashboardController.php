<?php declare(strict_types = 1);

namespace Modules\Backend\Template\Controllers;

use App\Controllers\Backend\BackendController;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Modules\Backend\Template\Config\Views;
use Modules\Backend\Template\Libraries\DashboardClass;
use Modules\Backend\Template\Models\DashboardModel;
use Psr\Log\LoggerInterface;

/** Dashboard applicativa Manager; il backbone continua a fornire layout, autenticazione e servizi comuni. */
class DashboardController extends BackendController
{
    protected DashboardModel $dashboardModel;
    protected DashboardClass $dashboardClass;

    /** @param RequestInterface $request Richiesta. @param ResponseInterface $response Risposta. @param LoggerInterface $logger Log. @return void Inizializza model e presenter del modulo. */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->dashboardModel = model(DashboardModel::class);
        $this->dashboardClass = new DashboardClass($this->currentAdmin, $this->dashboardModel);
        $this->data['controller'] = 'dashboard';
    }

    /** @return string Dashboard con gli ultimi quattro post-it e anteprima indipendente dello Studio. */
    public function index(): string
    {
        $this->data['action'] = 'index';
        $this->data['title'] = lang('backend/dashboard.titles.index');
        $this->data['icon'] = '<i class="fa-solid fa-gauge"></i>';
        return $this->render(Views::DASHBOARD_VIEW, $this->dashboardClass->data());
    }

}
