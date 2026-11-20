<?php declare(strict_types = 1);

namespace Modules\Backend\Applications\Libraries;

/**
 * Prepara barre dei collegamenti e asset specifici del modulo Applications.
 */
class ApplicationsClass
{
    /* LINKS BAR ----------------------------------------------------------------------------------- */

    /**
     * Prepara il collegamento al form di nuova applicazione.
     * @return array Configurazione consumata dal renderer comune del backend.
     */
    /**
     * Prepara Lista, Aggiungi e Aggiorna per il dettaglio.
     * @param string|null $uuid UUID dell’applicazione corrente.
     * @return array Configurazione consumata dal renderer comune del backend.
     */
    public function getLinksBarShowAll(): array
    {
        return [
            ['icon' => '<i class="fa-solid fa-plus"></i>', 'label' => lang('backend/applications.linksBar.add'), 'route' => 'backend/applications/add'],
        ];
    }

    /**
     * Prepara il ritorno alla lista delle applicazioni.
     * @return array Configurazione consumata dal renderer comune del backend.
     */
    public function getLinksBarAdd(): array
    {
        return [
            ['icon' => '<i class="fa-solid fa-list"></i>', 'label' => lang('backend/applications.linksBar.showAll'), 'route' => 'backend/applications/showAll'],
        ];
    }

    /**
     * Prepara Lista, Aggiungi e Dettaglio per la modifica.
     * @param string|null $uuid UUID dell’applicazione corrente.
     * @return array Configurazione consumata dal renderer comune del backend.
     */
    public function getLinksBarEdit(?string $uuid = null): array
    {
        if ( ! $uuid):
            return [];
        endif;

        return [
            ['icon' => '<i class="fa-solid fa-list"></i>', 'label' => lang('backend/applications.linksBar.showAll'), 'route' => 'backend/applications/showAll'],
            ['icon' => '<i class="fa-solid fa-plus"></i>', 'label' => lang('backend/applications.linksBar.add'), 'route' => 'backend/applications/add'],
            ['icon' => '<i class="fa-solid fa-eye"></i>', 'label' => lang('backend/applications.linksBar.show'), 'route' => "backend/applications/show/{$uuid}"],
        ];
    }

    /**
     * Prepara Lista, Aggiungi e Aggiorna per il dettaglio.
     * @param string|null $uuid UUID dell’applicazione corrente.
     * @return array Configurazione consumata dal renderer comune del backend.
     */
    public function getLinksBarShow(?string $uuid = null): array
    {
        if ( ! $uuid):
            return [];
        endif;

        return [
            ['icon' => '<i class="fa-solid fa-list"></i>', 'label' => lang('backend/applications.linksBar.showAll'), 'route' => 'backend/applications/showAll'],
            ['icon' => '<i class="fa-solid fa-plus"></i>', 'label' => lang('backend/applications.linksBar.add'), 'route' => 'backend/applications/add'],
            ['icon' => '<i class="fa-solid fa-pen"></i>', 'label' => lang('backend/applications.linksBar.edit'), 'route' => "backend/applications/edit/{$uuid}"],
        ];
    }

    /* JAVASCRIPT ---------------------------------------------------------------------------------- */

    /**
     * Definisce gli script necessari ai filtri temporali della lista.
     * @return array Configurazione consumata dal renderer comune del backend.
     */
    /**
     * Definisce lo script di esportazione del dettaglio in PDF.
     * @return array Configurazione consumata dal renderer comune del backend.
     */
    public function getJsShowAll(): array
    {
        $lang = service('request')->getLocale();
        $locale = ($lang === 'en' ? 'default' : $lang);

        return [
            ['id' => 'flatpickr-js', 'path' => 'assets/vendor/flatpickr/js/flatpickr.min.js', 'position' => 'before', 'target' => 'applications-js'],
            ['id' => $locale . '-js', 'path' => 'assets/vendor/flatpickr/js/' . $locale . '.js', 'position' => 'after', 'target' => 'flatpickr-js'],
        ];
    }

    /**
     * Definisce lo script di esportazione del dettaglio in PDF.
     * @return array Configurazione consumata dal renderer comune del backend.
     */
    public function getJsShow(): array
    {
        return [
            ['id' => 'html2pdf-js', 'path' => 'assets/vendor/html2pdf/js/html2pdf.bundle.min.js', 'position' => 'before', 'target' => 'applications-js'],
        ];
    }

    /* CSS ----------------------------------------------------------------------------------------- */

    /**
     * Definisce gli stili del selettore di date della lista.
     * @return array Configurazione consumata dal renderer comune del backend.
     */
    public function getCssShowAll(): array
    {
        return [
            ['id' => 'flatpickr-css', 'path' => 'assets/vendor/flatpickr/css/flatpickr.min.css', 'position' => 'before', 'target' => 'backend-css'],
        ];
    }
}
