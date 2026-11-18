<?php declare(strict_types = 1);

namespace Modules\Backend\Operations\Controllers;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use CodeIgniter\HTTP\RedirectResponse;
use Modules\Backend\Operations\Config\Views;
use Modules\Backend\Operations\Models\OperationsModel;
use Modules\Backend\Operations\Libraries\OperationsClass;
use App\Controllers\Backend\BackendController;

/**
 * Struttura del controller Operations; logica applicativa da implementare.
 */
class OperationsController extends BackendController
{
    /** @var OperationsModel Modello del modulo. */
    protected OperationsModel $operationsModel;

    /** @var OperationsClass Libreria di presentazione del modulo. */
    protected OperationsClass $operationsClass;

    /**
     * Inizializza il controller e le dipendenze del modulo.
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->data['controller'] = 'operations';
        $this->data['entity'] = 'operations';

        $this->operationsModel = model(OperationsModel::class);
        $this->operationsClass = new OperationsClass();
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
     * Visualizza il dettaglio del record.
     */
    public function show(string $uuid): RedirectResponse|string
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
     * Prepara una delle operazioni consentite.
     */
    public function prepare(): ResponseInterface
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Predispone il comando execute di controllo.
     */
    public function check(): ResponseInterface
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Predispone il comando execute dei test.
     */
    public function tests(): ResponseInterface
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Predispone il comando execute di aggiornamento.
     */
    public function update(): ResponseInterface
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Predispone il comando execute di commit.
     */
    public function commit(): ResponseInterface
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Predispone il comando execute di push.
     */
    public function push(): ResponseInterface
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Predispone il comando execute di clonazione.
     */
    public function cloneApp(): ResponseInterface
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Carica il rapporto di un'esecuzione.
     */
    public function getReport(): ResponseInterface
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }
}
