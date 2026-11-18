<?php declare(strict_types = 1);

/** Permessi del modulo Projects, coerenti con i commenti del DDL. */
return [
    [
        'title' => lang('backend/permissions.projects.title'),
        'controller' => 'projects',
        'icon' => '<i class="fa-solid fa-folder-open"></i>',
        'perms' => [
            'projects_show_all' => lang('backend/permissions.projects.showAll'),
            'projects_show' => lang('backend/permissions.projects.show'),
            'projects_add' => lang('backend/permissions.projects.add'),
            'projects_edit' => lang('backend/permissions.projects.edit'),
            'projects_delete' => lang('backend/permissions.projects.delete'),
            'projects_restore' => lang('backend/permissions.projects.restore'),
        ],
    ],
];
