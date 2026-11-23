<?php declare(strict_types = 1);

/** Accesso allo Studio; creazione, modifica e cancellazione usano i permessi Tasks e Notes. */
return [[
    'title' => lang('backend/permissions.studio.title'),
    'controller' => 'studio',
    'icon' => '<i class="fa-solid fa-building"></i>',
    'perms' => ['studio_show' => lang('backend/permissions.studio.show')],
]];
