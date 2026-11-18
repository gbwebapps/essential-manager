<?php declare(strict_types = 1);

/** Permessi del modulo Checklists, coerenti con i commenti del DDL. */
return [
    [
        'title' => lang('backend/permissions.checklists.title'),
        'controller' => 'checklists',
        'icon' => '<i class="fa-solid fa-clipboard-check"></i>',
        'perms' => [
            'checklists_show_all' => lang('backend/permissions.checklists.showAll'),
            'checklists_show' => lang('backend/permissions.checklists.show'),
            'checklists_add' => lang('backend/permissions.checklists.add'),
            'checklists_edit' => lang('backend/permissions.checklists.edit'),
            'checklists_delete' => lang('backend/permissions.checklists.delete'),
            'checklists_restore' => lang('backend/permissions.checklists.restore'),
            'checklists_apply' => lang('backend/permissions.checklists.apply'),
        ],
    ],
];
