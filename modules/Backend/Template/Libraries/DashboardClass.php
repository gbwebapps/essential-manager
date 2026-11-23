<?php declare(strict_types = 1);

namespace Modules\Backend\Template\Libraries;

use App\Libraries\Backend\AuthorizationClass;
use Modules\Backend\Template\Models\DashboardModel;

/** Dati della Dashboard e capacità di lettura stabilite dal server. */
class DashboardClass
{
    private ?array $capabilities = null;

    /** @param object|null $admin Identità server. @param DashboardModel $model Letture applicative. */
    public function __construct(protected ?object $admin, protected DashboardModel $model) {}

    /** @return array Anteprime limitate, conteggi completi e capacità di accesso per la Dashboard. */
    public function data(): array
    {
        $access = $this->access();
        $snapshot = $access['activities']
            ? $this->model->activitySnapshot((string) $this->admin->uuid, $access['projects'], $access['applications'], $access['notes'])
            : ['records' => [], 'current' => null, 'overview' => ['total' => 0, 'open' => 0, 'in_progress' => 0, 'remaining_steps' => 0]];
        $studioTasks = $access['studioTasks'] ? $this->model->studioTasks((string) $this->admin->uuid, $access['notes']) : [];
        return ['dashboardTasks' => $snapshot['records'], 'currentTask' => $snapshot['current'], 'taskOverview' => $snapshot['overview'],
            'currentAdmin' => $this->admin, 'dashboardAccess' => $access, 'dashboardPresenter' => $this,
            'dashboardStudioTasks' => $studioTasks, 'dashboardStudioTaskCount' => (int) ($studioTasks[0]->studio_total ?? 0),
            'dashboardStudioNotes' => $access['studio'] && $access['notes'] ? $this->model->studioNoteCount() : 0];
    }

    /** @return array Script del modulo per il posizionamento del corrente Studio sotto le barre superiori. */
    public function getJsIndex(): array
    {
        return [['id' => 'manager-dashboard-js', 'path' => 'assets/js/backend/manager-dashboard.js', 'isModule' => true, 'position' => 'after', 'target' => 'dashboard-js']];
    }

    /** @param string $uuid UUID persistito. @return string Colore pastello distribuito senza sequenza fissa e stabile per il task fra pagine e ricaricamenti. */
    public function paper(string $uuid): string
    {
        return ['blue', 'green', 'rose', 'violet'][hexdec(substr(hash('sha256', $uuid), 0, 2)) % 4];
    }

    /** @param string|null $value Testo salvato. @param int $limit Lunghezza della sola anteprima. @return string Anteprima senza modifiche al dato persistito. */
    public function preview(?string $value, int $limit = 50): string
    {
        $value = trim((string) preg_replace('/\s+/u', ' ', $value ?? ''));
        return mb_strlen($value) > $limit ? mb_substr($value, 0, $limit - 1) . '…' : $value;
    }

    /** @param object $task Record autorizzato. @param bool $edit Destinazione di modifica anziché dettaglio. @return string URL della sezione proprietaria. */
    public function taskUrl(object $task, bool $edit = false): string
    {
        return base_url('backend/' . ((int) $task->is_studio === 1 ? 'studio/tasks/' : 'tasks/') . ($edit ? 'edit/' : 'show/') . $task->uuid);
    }

    /** @return array Capacità derivate dai permessi comuni, memorizzate soltanto per la richiesta corrente. */
    private function access(): array
    {
        if ($this->capabilities !== null): return $this->capabilities; endif;
        $projects = $this->allows('projects_show_all') || $this->allows('projects_show') || $this->allows('projects_edit');
        $applications = $this->allows('applications_show_all') || $this->allows('applications_show') || $this->allows('applications_edit');
        $list = $this->allows('tasks_show_all');
        $details = $this->allows('tasks_show') || $this->allows('tasks_edit');
        $studio = $this->allows('studio_show');
        $studioTasks = $studio && ($list || $details);
        return $this->capabilities = ['activities' => $list && ($projects || $applications), 'projects' => $projects, 'applications' => $applications,
            'studio' => $studio, 'studioTasks' => $studioTasks, 'notes' => $this->allows('notes_show'),
            'show' => $this->allows('tasks_show'), 'edit' => $this->allows('tasks_edit'), 'addStudioTask' => $studioTasks && $this->allows('tasks_add')];
    }

    /** @param string $permission Permesso interno. @return bool Decisione del backbone. */
    private function allows(string $permission): bool
    {
        return AuthorizationClass::allows($this->admin, $permission);
    }
}
