<?php declare(strict_types = 1);

namespace Modules\Backend\Tasks\Controllers;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use CodeIgniter\HTTP\RedirectResponse;
use Modules\Backend\Tasks\Config\Views;
use Modules\Backend\Tasks\Models\TasksModel;
use Modules\Backend\Tasks\Libraries\TasksClass;
use App\Controllers\Backend\BackendController;

/**
 * Struttura del controller Tasks; logica applicativa da implementare.
 */
class TasksController extends BackendController
{
    /** @var TasksModel Modello del modulo. */
    protected TasksModel $tasksModel;

    /** @var TasksClass Libreria di presentazione del modulo. */
    protected TasksClass $tasksClass;

    /**
     * Inizializza il controller e le dipendenze del modulo.
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->data['controller'] = 'tasks';
        $this->data['entity'] = 'tasks';

        $this->tasksModel = model(TasksModel::class);
        $this->tasksClass = new TasksClass();
    }

    /**
     * Visualizza la pagina iniziale del modulo.
     */
    public function index(): string
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Visualizza e filtra l'elenco.
     */
    public function showAll(): string|ResponseInterface
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Gestisce il nuovo inserimento.
     */
    public function add(): string|ResponseInterface
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Gestisce la modifica del record.
     */
    public function edit(?string $uuid = null): string|ResponseInterface
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Visualizza il dettaglio del record.
     */
    public function show(string $uuid): RedirectResponse|string
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Elimina definitivamente il record.
     */
    public function hardDelete(): ResponseInterface
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Sposta il record nel cestino.
     */
    public function softDelete(): ResponseInterface
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Ripristina il record dal cestino.
     */
    public function restoreDelete(): ResponseInterface
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Carica i dati generali del record.
     */
    public function getGeneralData(): ResponseInterface
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Carica i metadati del record.
     */
    public function getMetaData(): ResponseInterface
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Completa il task.
     */
    public function complete(): ResponseInterface
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Riapre il task.
     */
    public function reopen(): ResponseInterface
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Carica i passaggi del task.
     */
    public function getSteps(): ResponseInterface
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Aggiunge un passaggio.
     */
    public function addStep(): ResponseInterface
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Modifica un passaggio.
     */
    public function editStep(): ResponseInterface
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Elimina un passaggio.
     */
    public function deleteStep(): ResponseInterface
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Riordina i passaggi.
     */
    public function reorderSteps(): ResponseInterface
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Completa un passaggio.
     */
    public function completeStep(): ResponseInterface
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Riapre un passaggio.
     */
    public function reopenStep(): ResponseInterface
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Carica il task corrente dell'amministratore.
     */
    public function getCurrent(): ResponseInterface
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Imposta il task corrente.
     */
    public function setCurrent(): ResponseInterface
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Mette in pausa il lavoro corrente.
     */
    public function pauseCurrent(): ResponseInterface
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Riprende il lavoro corrente.
     */
    public function resumeCurrent(): ResponseInterface
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }
}
