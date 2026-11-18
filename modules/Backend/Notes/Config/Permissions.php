<?php declare(strict_types = 1);

/** Permessi del modulo Notes, coerenti con i commenti del DDL. */
return [
    [
        'title' => lang('backend/permissions.notes.title'),
        'controller' => 'notes',
        'icon' => '<i class="fa-solid fa-note-sticky"></i>',
        'perms' => [
            'notes_show_all' => lang('backend/permissions.notes.showAll'),
            'notes_show' => lang('backend/permissions.notes.show'),
            'notes_add' => lang('backend/permissions.notes.add'),
            'notes_edit' => lang('backend/permissions.notes.edit'),
            'notes_delete' => lang('backend/permissions.notes.delete'),
            'notes_restore' => lang('backend/permissions.notes.restore'),
        ],
    ],
];
