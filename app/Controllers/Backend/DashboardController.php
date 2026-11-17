<?php declare(strict_types = 1); 

namespace App\Controllers\Backend;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

use App\Models\Backend\DashboardModel;
use App\Libraries\Backend\DashboardClass;
use App\Controllers\Backend\BackendController; 
use Config\Backend\Dashboard;

/**
 * Controller per la gestione della dashboard principale.
 * 
 * Si occupa di fornire l'interfaccia di atterraggio (landing) a seguito dell'autenticazione, 
 * predisponendo la vista che ospita widget informativi, riepiloghi statistici e notifiche globali.
 */
class DashboardController extends BackendController 
{
    /**
     * Istanza del modello incaricato di recuperare eventuali dati statistici o di riepilogo dal database.
     * @var DashboardModel 
     */
    protected DashboardModel $dashboardModel;

    /**
     * Istanza della libreria di supporto per l'elaborazione di dati formattati specifici per la dashboard.
     * @var DashboardClass 
     */
    protected DashboardClass $dashboardClass;

    /**
     * Inizializza le dipendenze specifiche della dashboard, dichiarando l'identificativo del controller 
     * e istanziando il modello e la classe di supporto associati.
     *
     * @param RequestInterface $request Oggetto contenente la richiesta HTTP.
     * @param ResponseInterface $response Oggetto deputato alla costruzione della risposta HTTP.
     * @param LoggerInterface $logger L'interfaccia per il tracciamento dei log.
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->data['controller'] = 'dashboard';

        $this->dashboardModel = model(DashboardModel::class);
        $this->dashboardClass = new DashboardClass($this->dashboardModel);
    }

    /**
     * Elabora e restituisce la vista principale della dashboard, configurando i metadati essenziali (titolo e icona) 
     * prima di delegare la renderizzazione al BackendController.
     *
     * @return string HTML renderizzato della schermata di riepilogo.
     */
    public function index(): string
    {
        $this->data['action'] = 'index';
        
        $this->data['title'] = lang('backend/dashboard.titles.index');
        $this->data['icon'] = '<i class="fa-solid fa-gauge"></i>';

        $overview = $this->dashboardModel->getOverview($this->currentAdmin ?? null);
        $this->data['menuCounts'] = $overview['counts'];
        $this->data['dashboardCards'] = $this->dashboardClass->getCards($overview['counts'], $this->currentAdmin ?? null);
        $this->data['dashboardRankings'] = $this->dashboardClass->getRankings($overview['rankings']);
        $this->data['dashboardTotals'] = $overview['totals'];
        $this->data['dashboardPresenter'] = $this->dashboardClass;
        $this->data['dashboardTheme'] = backend_theme();

        return $this->render('backend/dashboard/indexView', $this->data);
    }

    /** Returns the refreshed contents of a single visible summary card. */
    public function reloadSummary(string $entity): ResponseInterface
    {
        if ( ! $this->request->isAJAX()):
            return $this->jsonResponse(['result' => false], 400);
        endif;

        if ( ! isset(config(Dashboard::class)->sections[$entity])):
            return $this->jsonResponse(['result' => false], 404);
        endif;

        $summary = $this->dashboardModel->getSummary($this->currentAdmin, $entity);
        $card = $this->dashboardClass->getCards($summary['counts'], $this->currentAdmin)[$entity] ?? null;
        $removalCounts = $summary['totals']['usersRemoval'] ?? null;

        if ($card === null || ($entity === 'users' && $removalCounts === null)):
            return $this->jsonResponse(['result' => false]);
        endif;

        $output = view('backend/dashboard/partials/index/summaryContentPartial', [
            'entity' => $entity,
            'card' => $card,
            'dashboardPresenter' => $this->dashboardClass,
            'removalCounts' => $removalCounts,
        ]);

        return $this->jsonResponse(['result' => true, 'output' => $output]);
    }
}
