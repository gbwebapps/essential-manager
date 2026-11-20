<?php declare(strict_types = 1);

namespace Modules\Backend\Tasks\Libraries;

/**
 * Prepara barre dei collegamenti e asset specifici del modulo Tasks.
 */
class TasksClass
{
    /* LINKS BAR ----------------------------------------------------------------------------------- */

    /**
     * Prepara il collegamento per aggiungere un task.
     * @return array Collegamenti della lista.
     */
    public function getLinksBarShowAll(): array
    {
        return [
            ['icon' => '<i class="fa-solid fa-plus"></i>', 'label' => lang('backend/tasks.linksBar.add'), 'route' => 'backend/tasks/add'],
        ];
    }

    /**
     * Prepara il ritorno alla lista dei task.
     * @return array Configurazione consumata dal renderer comune del backend.
     */
    public function getLinksBarAdd(): array
    {
        return [
            ['icon' => '<i class="fa-solid fa-list"></i>', 'label' => lang('backend/tasks.linksBar.showAll'), 'route' => 'backend/tasks/showAll'],
        ];
    }

    /**
     * Prepara Lista, Aggiungi e Dettaglio per la modifica.
     * @param string|null $uuid UUID del task corrente.
     * @return array Configurazione consumata dal renderer comune del backend.
     */
    public function getLinksBarEdit(?string $uuid = null): array
    {
        if ( ! $uuid):
            return [];
        endif;

        return [
            ['icon' => '<i class="fa-solid fa-list"></i>', 'label' => lang('backend/tasks.linksBar.showAll'), 'route' => 'backend/tasks/showAll'],
            ['icon' => '<i class="fa-solid fa-plus"></i>', 'label' => lang('backend/tasks.linksBar.add'), 'route' => 'backend/tasks/add'],
            ['icon' => '<i class="fa-solid fa-eye"></i>', 'label' => lang('backend/tasks.linksBar.show'), 'route' => "backend/tasks/show/{$uuid}"],
        ];
    }

    /**
     * Prepara Lista, Aggiungi e Aggiorna per il dettaglio.
     * @param string|null $uuid UUID del task corrente.
     * @return array Configurazione consumata dal renderer comune del backend.
     */
    public function getLinksBarShow(?string $uuid = null): array
    {
        if ( ! $uuid):
            return [];
        endif;

        return [
            ['icon' => '<i class="fa-solid fa-list"></i>', 'label' => lang('backend/tasks.linksBar.showAll'), 'route' => 'backend/tasks/showAll'],
            ['icon' => '<i class="fa-solid fa-plus"></i>', 'label' => lang('backend/tasks.linksBar.add'), 'route' => 'backend/tasks/add'],
            ['icon' => '<i class="fa-solid fa-pen"></i>', 'label' => lang('backend/tasks.linksBar.edit'), 'route' => "backend/tasks/edit/{$uuid}"],
        ];
    }

    /* JAVASCRIPT ---------------------------------------------------------------------------------- */

    /**
     * Carica i filtri data della lista come nelle altre sezioni CRUD.
     * @return array Asset JavaScript del selettore di date.
     */
    public function getJsShowAll(): array
    {
        $lang = service('request')->getLocale();
        $locale = ($lang === 'en' ? 'default' : $lang);

        return [
            ['id' => 'flatpickr-js', 'path' => 'assets/vendor/flatpickr/js/flatpickr.min.js', 'position' => 'before', 'target' => 'tasks-js'],
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
            ['id' => 'html2pdf-js', 'path' => 'assets/vendor/html2pdf/js/html2pdf.bundle.min.js', 'position' => 'before', 'target' => 'tasks-js'],
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
