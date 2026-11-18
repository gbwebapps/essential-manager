<?php declare(strict_types = 1);

namespace Modules\Backend\Checklists\Controllers;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use CodeIgniter\HTTP\RedirectResponse;
use Modules\Backend\Checklists\Config\Views;
use Modules\Backend\Checklists\Models\ChecklistsModel;
use Modules\Backend\Checklists\Libraries\ChecklistsClass;
use App\Controllers\Backend\BackendController;

/**
 * Struttura del controller Checklists; logica applicativa da implementare.
 */
class ChecklistsController extends BackendController
{
    /** @var ChecklistsModel Modello del modulo. */
    protected ChecklistsModel $checklistsModel;

    /** @var ChecklistsClass Libreria di presentazione del modulo. */
    protected ChecklistsClass $checklistsClass;

    /**
     * Inizializza il controller e le dipendenze del modulo.
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->data['controller'] = 'checklists';
        $this->data['entity'] = 'checklists';

        $this->checklistsModel = model(ChecklistsModel::class);
        $this->checklistsClass = new ChecklistsClass();
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
     * Carica i passaggi della checklist.
     */
    public function getItems(): ResponseInterface
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Aggiunge un passaggio alla checklist.
     */
    public function addItem(): ResponseInterface
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Modifica un passaggio della checklist.
     */
    public function editItem(): ResponseInterface
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Elimina un passaggio della checklist.
     */
    public function deleteItem(): ResponseInterface
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Riordina i passaggi della checklist.
     */
    public function reorderItems(): ResponseInterface
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Copia i passaggi della checklist nel task.
     */
    public function applyToTask(): ResponseInterface
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }
}
