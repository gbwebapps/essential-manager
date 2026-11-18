<?php declare(strict_types = 1);

namespace Modules\Backend\Notes\Controllers;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use CodeIgniter\HTTP\RedirectResponse;
use Modules\Backend\Notes\Config\Views;
use Modules\Backend\Notes\Models\NotesModel;
use Modules\Backend\Notes\Libraries\NotesClass;
use App\Controllers\Backend\BackendController;

/**
 * Struttura del controller Notes; logica applicativa da implementare.
 */
class NotesController extends BackendController
{
    /** @var NotesModel Modello del modulo. */
    protected NotesModel $notesModel;

    /** @var NotesClass Libreria di presentazione del modulo. */
    protected NotesClass $notesClass;

    /**
     * Inizializza il controller e le dipendenze del modulo.
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->data['controller'] = 'notes';
        $this->data['entity'] = 'notes';

        $this->notesModel = model(NotesModel::class);
        $this->notesClass = new NotesClass();
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
}
