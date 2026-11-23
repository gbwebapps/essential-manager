<?php declare(strict_types = 1);

namespace Modules\Backend\Template\Config;

/** Le voci del menu applicativo restano in Manager; vista e comportamento sono del Framework. */
class Registrar
{
    /** @return array Menu superiore consumato da Config\Backend\Menu senza modificare il backbone. */
    public static function Menu(): array
    {
        return [
            'top' => [
                ['label' => lang('backend/global.menu.bottomLeft.dashboard'), 'route' => 'backend/dashboard', 'icon' => '<i class="fa-solid fa-gauge"></i>', 'controller' => 'dashboard'],
                ['label' => lang('backend/permissions.studio.title'), 'route' => 'backend/studio', 'icon' => '<i class="fa-solid fa-building"></i>', 'controller' => 'studio', 'permission' => 'studio_show'],
                ['label' => lang('backend/permissions.tasks.title'), 'route' => 'backend/tasks/showAll', 'icon' => '<i class="fa-solid fa-list-check"></i>', 'controller' => 'tasks', 'permission' => 'tasks_show_all'],
                ['label' => lang('backend/permissions.projects.title'), 'route' => 'backend/projects/showAll', 'icon' => '<i class="fa-solid fa-folder-open"></i>', 'controller' => 'projects', 'permission' => 'projects_show_all'],
                ['label' => lang('backend/permissions.applications.title'), 'route' => 'backend/applications/showAll', 'icon' => '<i class="fa-solid fa-desktop"></i>', 'controller' => 'applications', 'permission' => 'applications_show_all'],
                ['label' => lang('backend/permissions.checklists.title'), 'route' => 'backend/checklists/showAll', 'icon' => '<i class="fa-solid fa-clipboard-check"></i>', 'controller' => 'checklists', 'permission' => 'checklists_show_all'],
                ['label' => lang('backend/permissions.operations.title'), 'route' => 'backend/operations/showAll', 'icon' => '<i class="fa-solid fa-gears"></i>', 'controller' => 'operations', 'permission' => 'operations_show_all'],
            ],
        ];
    }
}
