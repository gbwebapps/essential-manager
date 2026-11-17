<?php declare(strict_types = 1);

namespace Config\Backend;

use App\Libraries\Backend\AuthorizationClass;
use CodeIgniter\Config\BaseConfig;

/**
 * Configura l'elaborazione a blocchi del componente globale Export CSV.
 */
class Export extends BaseConfig
{
    /** Numero di record elaborati per ciascuna richiesta di esportazione. */
    public const CHUNK_SIZE = 5;

    private const ENTITY_PERMISSIONS = [
        'users' => 'users_export',
        'contact_messages' => 'messages_export',
    ];

    /** Keeps existing superadmin exports available and delegates only the configured entities. */
    public static function allowsEntity(?object $admin, string $entity): bool
    {
        $permission = self::ENTITY_PERMISSIONS[$entity] ?? null;
        return $permission !== null
            ? AuthorizationClass::allows($admin, $permission)
            : (int) ($admin->superadmin ?? 0) === 1;
    }
}
