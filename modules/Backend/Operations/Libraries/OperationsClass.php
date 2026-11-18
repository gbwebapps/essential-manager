<?php declare(strict_types = 1);

namespace Modules\Backend\Operations\Libraries;

/**
 * Struttura della libreria Operations; collegamenti, asset e opzioni da implementare.
 */
class OperationsClass
{
    /* LINKS BAR ----------------------------------------------------------------------------------- */

    /**
     * Predispone i collegamenti della vista.
     */
    public function getLinksBarShowAll(): array
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Predispone i collegamenti della vista.
     */
    public function getLinksBarShow(?string $uuid = null): array
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /* JAVASCRIPT ---------------------------------------------------------------------------------- */

    /**
     * Predispone gli asset JavaScript della vista.
     */
    public function getJsShowAll(): array
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Predispone gli asset JavaScript della vista.
     */
    public function getJsShow(): array
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /* CSS ----------------------------------------------------------------------------------------- */

    /**
     * Predispone gli asset CSS dell'elenco.
     */
    public function getCssShowAll(): array
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /* OPERAZIONI EXECUTE --------------------------------------------------------------------------- */

    /**
     * Definisce i soli comandi execute consentiti.
     */
    public function getAllowedOperations(): array
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Prepara il comando e gli argomenti consentiti.
     */
    public function prepareCommand(array $operation): array
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Esegue un'operazione autorizzata e raccoglie il risultato.
     */
    public function runCommand(array $operation): array
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /* OPTIONS ------------------------------------------------------------------------------------- */

    // TODO: dichiarare le opzioni specifiche quando saranno definiti i form.
}
