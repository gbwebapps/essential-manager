<?php declare(strict_types = 1);

namespace Modules\Backend\Checklists\Libraries;

/**
 * Prepara barre dei collegamenti e asset specifici del modulo Checklists.
 */
class ChecklistsClass
{
    /* LINKS BAR ----------------------------------------------------------------------------------- */

    /**
     * Collegamento al nuovo modello.
     * @return array Configurazione della vista.
     */
    public function getLinksBarShowAll(): array
    {
        return [
            ['icon' => '<i class="fa-solid fa-plus"></i>', 'label' => lang('backend/checklists.linksBar.add'), 'route' => 'backend/checklists/add'],
        ];
    }

    /**
     * Collegamento alla lista.
     * @return array Configurazione della vista.
     */
    public function getLinksBarAdd(): array
    {
        return [
            ['icon' => '<i class="fa-solid fa-list"></i>', 'label' => lang('backend/checklists.linksBar.showAll'), 'route' => 'backend/checklists/showAll'],
        ];
    }

    /**
     * Lista, nuovo modello e dettaglio.
     * @param string|null $uuid Record corrente.
     * @return array Configurazione della vista.
     */
    public function getLinksBarEdit(?string $uuid = null): array
    {
        if ( ! $uuid):
            return [];
        endif;

        return [
            ['icon' => '<i class="fa-solid fa-list"></i>', 'label' => lang('backend/checklists.linksBar.showAll'), 'route' => 'backend/checklists/showAll'],
            ['icon' => '<i class="fa-solid fa-plus"></i>', 'label' => lang('backend/checklists.linksBar.add'), 'route' => 'backend/checklists/add'],
            ['icon' => '<i class="fa-solid fa-eye"></i>', 'label' => lang('backend/checklists.linksBar.show'), 'route' => "backend/checklists/show/{$uuid}"],
        ];
    }

    /**
     * Lista, nuovo modello e modifica.
     * @param string|null $uuid Record corrente.
     * @return array Configurazione della vista.
     */
    public function getLinksBarShow(?string $uuid = null): array
    {
        if ( ! $uuid):
            return [];
        endif;

        return [
            ['icon' => '<i class="fa-solid fa-list"></i>', 'label' => lang('backend/checklists.linksBar.showAll'), 'route' => 'backend/checklists/showAll'],
            ['icon' => '<i class="fa-solid fa-plus"></i>', 'label' => lang('backend/checklists.linksBar.add'), 'route' => 'backend/checklists/add'],
            ['icon' => '<i class="fa-solid fa-pen"></i>', 'label' => lang('backend/checklists.linksBar.edit'), 'route' => "backend/checklists/edit/{$uuid}"],
        ];
    }

    /* JAVASCRIPT ---------------------------------------------------------------------------------- */

    /**
     * Asset per i filtri data della lista.
     * @return array Configurazione della vista.
     */
    /**
     * Asset di stampa/esportazione del dettaglio.
     * @return array Configurazione della vista.
     */
    public function getJsShowAll(): array
    {
        $lang = service('request')->getLocale();
        $locale = ($lang === 'en' ? 'default' : $lang);

        return [
            ['id' => 'flatpickr-js', 'path' => 'assets/vendor/flatpickr/js/flatpickr.min.js', 'position' => 'before', 'target' => 'checklists-js'],
            ['id' => $locale . '-js', 'path' => 'assets/vendor/flatpickr/js/' . $locale . '.js', 'position' => 'after', 'target' => 'flatpickr-js'],
        ];
    }

    /**
     * Asset di stampa/esportazione del dettaglio.
     * @return array Configurazione della vista.
     */
    public function getJsShow(): array
    {
        return [
            ['id' => 'html2pdf-js', 'path' => 'assets/vendor/html2pdf/js/html2pdf.bundle.min.js', 'position' => 'before', 'target' => 'checklists-js'],
        ];
    }

    /* CSS ----------------------------------------------------------------------------------------- */

    /**
     * Stile dei filtri data.
     * @return array Configurazione della vista.
     */
    public function getCssShowAll(): array
    {
        return [
            ['id' => 'flatpickr-css', 'path' => 'assets/vendor/flatpickr/css/flatpickr.min.css', 'position' => 'before', 'target' => 'backend-css'],
        ];
    }
}
