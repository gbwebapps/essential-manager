<?php declare(strict_types = 1);

namespace Config\Backend;

/** Schema di scambio: gli ID dei gruppi sono riferimenti interni al file. */
class AdminTransfer
{
    public const TABLES = ['admins_groups', 'admins', 'admins_groups_permissions', 'admins_permissions'];
    public const FIELDS = [
        'admins_groups' => ['id', 'uuid', 'name', 'description'],
        'admins' => ['uuid', 'firstname', 'lastname', 'email', 'phone', 'status', 'group_id', 'note'],
        'admins_groups_permissions' => ['group_id', 'permission'],
        'admins_permissions' => ['admin_uuid', 'permission', 'allow'],
    ];
    public const INDEX_VIEW = 'backend/admintransfer/indexView';
    public const INDEX_PARTIAL = 'backend/admintransfer/partials/indexPartial';
    public const IMPORT_PREVIEW_PARTIAL = 'backend/admintransfer/partials/previewPartial';
    public const IMPORT_MODAL = 'backend/components/import/showModalView';
    public const IMPORT_ERRORS_PARTIAL = 'backend/components/import/errorsModalPartial';
    public const IMPORT_LOADING_PARTIAL = 'backend/components/import/loadingModalPartial';
    public const EXPORT_MODAL = 'backend/components/export/showModalView';
}
