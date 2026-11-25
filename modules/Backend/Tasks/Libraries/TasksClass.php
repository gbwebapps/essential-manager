<?php declare(strict_types = 1);

namespace Modules\Backend\Tasks\Libraries;

/**
 * Prepara barre dei collegamenti e asset specifici del modulo Tasks.
 */
class TasksClass
{
    protected bool $studioContext = false;
    protected bool $archiveContext = false;

    /** @param bool $archived Vista dell’archivio oppure scheda di un task archiviato. */
    public function setArchiveContext(bool $archived): void { $this->archiveContext = $archived; }

    /** @param bool $studio Contesto del task persistito. @return void Configura il ritorno alla sezione corretta. */
    public function setStudioContext(bool $studio): void
    {
        $this->studioContext = $studio;
    }

    /** @param object $task Task persistito. @return string Destinazione leggibile senza campi tecnici per Studio. */
    public static function contextLabel(object $task): string
    {
        if ((int) ($task->is_studio ?? 0) === 1): return lang('backend/studio.title'); endif;
        return ($task->project_name ?? '') . (($task->application_name ?? null) !== null ? ' / ' . $task->application_name : '');
    }

    /**
     * Prepara esclusivamente dati validati per l'anteprima condivisa della bozza.
     * @param array $data Payload normalizzato dal model.
     * @param string $kind task oppure note, stabilito dal controller.
     * @return object Record di sola presentazione; nessun UUID persistito.
     */
    public static function draftRecord(array $data, string $kind): object
    {
        $record = (object) ($data + ['uuid'=>'preview', 'deleted_at'=>null]);
        if ($kind === 'task'):
            $record->notes = array_map(static fn(array $note): object => (object) $note, $data['notes']);
            $record->steps = array_map(static function(array $step) use ($data): object {
                $step['completed'] = $data['status'] === 'completed';
                $step['notes'] = array_map(static fn(array $note): object => (object) $note, $step['notes']);
                return (object) $step;
            }, $data['steps']);
        endif;
        return $record;
    }

    /* LINKS BAR ----------------------------------------------------------------------------------- */

    /**
     * Prepara il collegamento per aggiungere un task.
     * @return array Collegamenti della lista.
     */
    public function getLinksBarShowAll(): array
    {
        return [
            ['icon' => '<i class="fa-solid fa-plus"></i>', 'label' => lang('backend/tasks.linksBar.add'), 'route' => $this->studioContext ? 'backend/studio/tasks/add' : 'backend/tasks/add'],
            ['icon'=>'<i class="fa-solid fa-box-archive"></i>', 'label'=>lang('backend/tasks.archive.'.($this->archiveContext?'active':'title')), 'route'=>$this->studioContext ? ($this->archiveContext?'backend/studio':'backend/studio/archived') : ($this->archiveContext?'backend/tasks/showAll':'backend/tasks/archived')],
        ];
    }

    /**
     * Prepara il ritorno alla lista dei task.
     * @return array Configurazione consumata dal renderer comune del backend.
     */
    public function getLinksBarAdd(): array
    {
        return [
            ['icon' => '<i class="fa-solid fa-list"></i>', 'label' => lang('backend/tasks.linksBar.showAll'), 'route' => $this->studioContext ? ($this->archiveContext?'backend/studio/archived':'backend/studio') : ($this->archiveContext?'backend/tasks/archived':'backend/tasks/showAll')],
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
            ['icon' => '<i class="fa-solid fa-list"></i>', 'label' => lang('backend/tasks.linksBar.showAll'), 'route' => $this->studioContext ? ($this->archiveContext?'backend/studio/archived':'backend/studio') : ($this->archiveContext?'backend/tasks/archived':'backend/tasks/showAll')],
            ['icon' => '<i class="fa-solid fa-plus"></i>', 'label' => lang('backend/tasks.linksBar.add'), 'route' => $this->studioContext ? 'backend/studio/tasks/add' : 'backend/tasks/add'],
            ['icon' => '<i class="fa-solid fa-eye"></i>', 'label' => lang('backend/tasks.linksBar.show'), 'route' => ($this->studioContext ? "backend/studio/tasks/show/{$uuid}" : "backend/tasks/show/{$uuid}")],
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

        $links = [
            ['icon' => '<i class="fa-solid fa-list"></i>', 'label' => lang('backend/tasks.linksBar.showAll'), 'route' => $this->studioContext ? ($this->archiveContext?'backend/studio/archived':'backend/studio') : ($this->archiveContext?'backend/tasks/archived':'backend/tasks/showAll')],
            ['icon' => '<i class="fa-solid fa-plus"></i>', 'label' => lang('backend/tasks.linksBar.add'), 'route' => $this->studioContext ? 'backend/studio/tasks/add' : 'backend/tasks/add'],
            ['icon' => '<i class="fa-solid fa-pen"></i>', 'label' => lang('backend/tasks.linksBar.edit'), 'route' => ($this->studioContext ? "backend/studio/tasks/edit/{$uuid}" : "backend/tasks/edit/{$uuid}")],
        ];
        if ($this->archiveContext): array_pop($links); endif;
        return $links;
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
