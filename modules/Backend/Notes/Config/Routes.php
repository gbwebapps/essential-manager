<?php declare(strict_types = 1);

/**
 * Rotte previste del modulo Notes.
 * Il registrar carica questo file; tutte le rotte appartengono al backend autorizzato.
 * I metodi del controller sono ancora da implementare.
 *
 * @var \CodeIgniter\Router\RouteCollection $routes
 */
$routes->group('backend', ['filter' => 'authorization'], function($routes) {
    $routes->group('notes', function($routes) {
        $routes->get('/', '\Modules\Backend\Notes\Controllers\NotesController::index', ['filter' => 'permission:notes_show_all']);
        $routes->match(['GET', 'POST'], 'showAll', '\Modules\Backend\Notes\Controllers\NotesController::showAll', ['filter' => 'permission:notes_show_all']);
        $routes->match(['GET', 'POST'], 'add', '\Modules\Backend\Notes\Controllers\NotesController::add', ['filter' => 'permission:notes_add']);
        $routes->get('edit/(:uuid)', '\Modules\Backend\Notes\Controllers\NotesController::edit/$1', ['filter' => 'permission:notes_edit']);
        $routes->post('edit', '\Modules\Backend\Notes\Controllers\NotesController::edit', ['filter' => 'permission:notes_edit']);
        $routes->get('show/(:uuid)', '\Modules\Backend\Notes\Controllers\NotesController::show/$1', ['filter' => 'permission:notes_show']);
        $routes->post('hardDelete', '\Modules\Backend\Notes\Controllers\NotesController::hardDelete', ['filter' => 'permission:notes_delete']);
        $routes->post('softDelete', '\Modules\Backend\Notes\Controllers\NotesController::softDelete', ['filter' => 'permission:notes_delete']);
        $routes->post('restoreDelete', '\Modules\Backend\Notes\Controllers\NotesController::restoreDelete', ['filter' => 'permission:notes_restore']);
        $routes->post('getGeneralData', '\Modules\Backend\Notes\Controllers\NotesController::getGeneralData', ['filter' => 'permission:notes_show']);
        $routes->post('getMetaData', '\Modules\Backend\Notes\Controllers\NotesController::getMetaData', ['filter' => 'permission:notes_show']);
    });
});
