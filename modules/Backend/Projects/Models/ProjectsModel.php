<?php declare(strict_types = 1);

namespace Modules\Backend\Projects\Models;

use App\Models\Backend\BackendModel;

/**
 * Struttura del modello Projects; query, validazioni e operazioni da implementare.
 */
class ProjectsModel extends BackendModel
{
    /** @var string|null Tabella principale del modulo. */
    protected ?string $module = 'projects';

    /** @var bool Gestione del cestino prevista dal DDL. */
    protected bool $hasSoftDelete = true;

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
     * Conta i record e il contenuto del cestino.
     */
    public function getTrashCounts(): array
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Definisce le regole di inserimento.
     */
    public function addValidationRules(): array
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Definisce le regole di modifica.
     */
    public function editValidationRules(array $posts): array
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Definisce le regole di eliminazione e ripristino.
     */
    public function delValidationRules(): array
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Definisce le regole per i dati generali.
     */
    public function generalDataValidationRules(): array
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Inserisce il record.
     */
    public function add(array $posts): array
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Modifica il record.
     */
    public function edit(array $posts): array
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Elimina definitivamente il record.
     */
    public function hardDelete(array $posts): array
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Sposta il record nel cestino.
     */
    public function softDelete(array $posts): array
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Ripristina il record dal cestino.
     */
    public function restoreDelete(array $posts): array
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
     * Carica i progetti di origine selezionabili.
     */
    public function getParentOptions(): array
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Verifica la protezione del progetto.
     */
    public function isProtected(string $uuid): bool
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }
}
