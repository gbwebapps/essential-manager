<?php declare(strict_types = 1);

namespace Modules\Backend\Operations\Models;

use App\Models\Backend\BackendModel;

/**
 * Struttura del modello Operations; query, validazioni e operazioni da implementare.
 */
class OperationsModel extends BackendModel
{
    /** @var string|null Tabella principale del modulo. */
    protected ?string $module = 'operation_runs';

    /** @var bool Gestione del cestino prevista dal DDL. */
    protected bool $hasSoftDelete = false;

    /** @var bool Il modulo non usa lo stato numerico attivo/sospeso. */
    protected bool $hasStatusFilter = false;

    /**
     * Inizializza il modello attraverso il costruttore ereditato.
     */
    protected function initModel(): void
    {
        parent::initModel();
    }

    /**
     * Definisce le regole per i parametri dell'elenco.
     */
    public function showAllValidationRules(): array
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Definisce le regole per i filtri dell'elenco.
     */
    public function showAllSearchValidationRules(): array
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Carica i dati dell'elenco.
     */
    public function getData(array $posts): array
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Carica il record tramite UUID.
     */
    public function getByUUID(string $uuid): array
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Definisce le regole per i metadati.
     */
    public function metaDataValidationRules(): array
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Registra la richiesta di un'operazione consentita.
     */
    public function createRun(array $posts): array
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Registra l'avvio di un'operazione.
     */
    public function startRun(array $posts): array
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Registra l'esito e il rapporto di un'operazione.
     */
    public function finishRun(array $posts): array
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Carica i riferimenti al rapporto di un'esecuzione.
     */
    public function getReport(string $uuid): array
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }
}
