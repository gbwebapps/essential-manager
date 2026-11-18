<?php declare(strict_types = 1);

namespace Modules\Backend\Tasks\Config;

/** Collega permessi e rotte del modulo alle configurazioni comuni di Essential. */
class Registrar
{
    public static function Menu(): array
    {
        return [
            'bottomLeft' => [
                ['label' => lang('backend/permissions.tasks.title'), 'route' => 'backend/tasks/showAll', 'icon' => '<i class="fa-solid fa-list-check"></i>', 'controller' => 'tasks', 'permission' => 'tasks_show_all'],
            ],
        ];
    }

    public static function Permissions(): array
    {
        return [
            'permissionProviders' => [static fn (): array => require __DIR__ . '/Permissions.php'],
        ];
    }

    public static function Routing(): array
    {
        return ['routeFiles' => [__DIR__ . '/Routes.php']];
    }
}
