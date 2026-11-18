<?php declare(strict_types = 1);

namespace Modules\Backend\Notes\Libraries;

/**
 * Struttura della libreria Notes; collegamenti, asset e opzioni da implementare.
 */
class NotesClass
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
    public function getLinksBarAdd(): array
    {
        // TODO: implementare.
        throw new \BadMethodCallException(__METHOD__ . ' non ancora implementato.');
    }

    /**
     * Predispone i collegamenti della vista.
     */
    public function getLinksBarEdit(?string $uuid = null): array
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

    /* OPTIONS ------------------------------------------------------------------------------------- */

    // TODO: dichiarare le opzioni specifiche quando saranno definiti i form.
}
