<?php declare(strict_types = 1);

namespace Modules\Backend\Template\Libraries;

use App\Libraries\Backend\AuthorizationClass;
use Modules\Backend\Projects\Models\ProjectsModel;
use Modules\Backend\Applications\Models\ApplicationsModel;
use Modules\Backend\Tasks\Models\TasksModel;

/** Conteggi della navigazione Manager, limitati ai contesti leggibili. */
class NavigationClass
{
    /**
     * Rilegge i conteggi dopo una modifica senza esporre sezioni non autorizzate.
     * @param object|null $admin Amministratore autenticato dal server.
     * @return array<string,int> Conteggi attivi delle sezioni autorizzate.
     */
    public static function counts(?object $admin): array
    {
        $counts = [];
        foreach (['projects' => ProjectsModel::class, 'applications' => ApplicationsModel::class] as $section => $model):
            if (AuthorizationClass::allows($admin, $section . '_show_all')): $counts[$section] = model($model)->activeCount(); endif;
        endforeach;
        if (AuthorizationClass::allows($admin, 'tasks_show_all')):
            $projects = AuthorizationClass::allows($admin, 'projects_show_all') || AuthorizationClass::allows($admin, 'projects_show') || AuthorizationClass::allows($admin, 'projects_edit');
            $applications = AuthorizationClass::allows($admin, 'applications_show_all') || AuthorizationClass::allows($admin, 'applications_show') || AuthorizationClass::allows($admin, 'applications_edit');
            $counts['tasks'] = model(TasksModel::class)->activeCount($projects, $applications);
        endif;
        if (AuthorizationClass::allows($admin, 'studio_show') && (AuthorizationClass::allows($admin, 'tasks_show_all') || AuthorizationClass::allows($admin, 'tasks_show') || AuthorizationClass::allows($admin, 'tasks_edit'))):
            $counts['studio'] = model(\Modules\Backend\Studio\Models\StudioModel::class)->taskCount();
        endif;
        return $counts;
    }
}
