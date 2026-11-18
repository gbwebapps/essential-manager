<?php declare(strict_types = 1);

namespace Modules\Backend\Notes\Config;

/** Collega permessi e rotte del modulo alle configurazioni comuni di Essential. */
class Registrar
{
    public static function Menu(): array
    {
        return [
            'bottomLeft' => [
                ['label' => lang('backend/permissions.notes.title'), 'route' => 'backend/notes/showAll', 'icon' => '<i class="fa-solid fa-note-sticky"></i>', 'controller' => 'notes', 'permission' => 'notes_show_all'],
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
