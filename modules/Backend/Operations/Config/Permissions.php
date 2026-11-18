<?php declare(strict_types = 1);

/** Permessi del modulo Operations, coerenti con i commenti del DDL. */
return [
    [
        'title' => lang('backend/permissions.operations.title'),
        'controller' => 'operations',
        'icon' => '<i class="fa-solid fa-gears"></i>',
        'perms' => [
            'operations_show_all' => lang('backend/permissions.operations.showAll'),
            'operations_show' => lang('backend/permissions.operations.show'),
            'operations_check' => lang('backend/permissions.operations.check'),
            'operations_tests' => lang('backend/permissions.operations.tests'),
            'operations_update' => lang('backend/permissions.operations.update'),
            'operations_commit' => lang('backend/permissions.operations.commit'),
            'operations_push' => lang('backend/permissions.operations.push'),
            'operations_clone' => lang('backend/permissions.operations.clone'),
        ],
    ],
];
