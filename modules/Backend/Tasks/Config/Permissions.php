<?php declare(strict_types = 1);

/** Permessi del modulo Tasks, coerenti con i commenti del DDL. */
return [
    [
        'title' => lang('backend/permissions.tasks.title'),
        'controller' => 'tasks',
        'icon' => '<i class="fa-solid fa-list-check"></i>',
        'perms' => [
            'tasks_show_all' => lang('backend/permissions.tasks.showAll'),
            'tasks_show' => lang('backend/permissions.tasks.show'),
            'tasks_add' => lang('backend/permissions.tasks.add'),
            'tasks_edit' => lang('backend/permissions.tasks.edit'),
            'tasks_delete' => lang('backend/permissions.tasks.delete'),
            'tasks_restore' => lang('backend/permissions.tasks.restore'),
            'tasks_complete' => lang('backend/permissions.tasks.complete'),
            'tasks_set_current' => lang('backend/permissions.tasks.setCurrent'),
        ],
    ],
];
