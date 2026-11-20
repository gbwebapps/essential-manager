<?php declare(strict_types = 1);

namespace Modules\Backend\Applications\Config;

/** Collega permessi e rotte del modulo alle configurazioni comuni di Essential. */
class Registrar
{
    /**
     * Registra la voce Applicazioni nel menu dei moduli.
     * @return array Integrazione letta dalla configurazione comune del backbone.
     */
    public static function Menu(): array
    {
        return [
            'bottomLeft' => [
                ['label' => lang('backend/permissions.applications.title'), 'route' => 'backend/applications/showAll', 'icon' => '<i class="fa-solid fa-desktop"></i>', 'controller' => 'applications', 'permission' => 'applications_show_all'],
            ],
        ];
    }

    /**
     * Espone i permessi del modulo alla schermata dei gruppi.
     * @return array Integrazione letta dalla configurazione comune del backbone.
     */
    public static function Permissions(): array
    {
        return [
            'permissionProviders' => [static fn (): array => require __DIR__ . '/Permissions.php'],
        ];
    }

    /**
     * Registra il file di rotte protette del modulo.
     * @return array Integrazione letta dalla configurazione comune del backbone.
     */
    public static function Routing(): array
    {
        return ['routeFiles' => [__DIR__ . '/Routes.php']];
    }
}
