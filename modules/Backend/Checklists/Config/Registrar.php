<?php declare(strict_types = 1);

namespace Modules\Backend\Checklists\Config;

/** Collega permessi e rotte del modulo alle configurazioni comuni di Essential. */
class Registrar
{
    public static function Menu(): array
    {
        return [
            'bottomLeft' => [
                ['label' => lang('backend/permissions.checklists.title'), 'route' => 'backend/checklists/showAll', 'icon' => '<i class="fa-solid fa-clipboard-check"></i>', 'controller' => 'checklists', 'permission' => 'checklists_show_all'],
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
