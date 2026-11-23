<?php declare(strict_types = 1);

namespace Modules\Backend\Studio\Config;

/** Integra Studio nei menu, nelle autorizzazioni e nelle rotte senza cambiare il backbone. */
class Registrar
{
    /** @return array Voce del menu per gli amministratori autorizzati. */
    public static function Menu(): array
    {
        return ['bottomLeft' => [['label' => lang('backend/permissions.studio.title'), 'route' => 'backend/studio', 'icon' => '<i class="fa-solid fa-building"></i>', 'controller' => 'studio', 'permission' => 'studio_show']]];
    }

    /** @return array Provider dei permessi del modulo. */
    public static function Permissions(): array
    {
        return ['permissionProviders' => [static fn (): array => require __DIR__ . '/Permissions.php']];
    }

    /** @return array File di rotte caricato dal sistema comune. */
    public static function Routing(): array
    {
        return ['routeFiles' => [__DIR__ . '/Routes.php']];
    }
}
