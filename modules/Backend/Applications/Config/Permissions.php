<?php declare(strict_types = 1);

/** Permessi del modulo Applications, coerenti con i commenti del DDL. */
return [
    [
        'title' => lang('backend/permissions.applications.title'),
        'controller' => 'applications',
        'icon' => '<i class="fa-solid fa-desktop"></i>',
        'perms' => [
            'applications_show_all' => lang('backend/permissions.applications.showAll'),
            'applications_show' => lang('backend/permissions.applications.show'),
            'applications_add' => lang('backend/permissions.applications.add'),
            'applications_edit' => lang('backend/permissions.applications.edit'),
            'applications_delete' => lang('backend/permissions.applications.delete'),
            'applications_restore' => lang('backend/permissions.applications.restore'),
        ],
    ],
];
