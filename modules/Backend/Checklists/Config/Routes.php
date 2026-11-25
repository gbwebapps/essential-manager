<?php declare(strict_types = 1);

/**
 * Rotte previste del modulo Checklists.
 * Il registrar carica questo file; tutte le rotte appartengono al backend autorizzato.
 *
 * @var \CodeIgniter\Router\RouteCollection $routes
 */
$routes->group('backend', ['filter' => 'authorization'], function($routes) {
    $routes->group('checklists', function($routes) {
        $routes->get('/', '\Modules\Backend\Checklists\Controllers\ChecklistsController::index', ['filter' => 'permission:checklists_show_all']);
        $routes->match(['GET', 'POST'], 'showAll', '\Modules\Backend\Checklists\Controllers\ChecklistsController::showAll', ['filter' => 'permission:checklists_show_all']);
        $routes->match(['GET', 'POST'], 'add', '\Modules\Backend\Checklists\Controllers\ChecklistsController::add', ['filter' => 'permission:checklists_add']);
        $routes->get('edit/(:uuid)', '\Modules\Backend\Checklists\Controllers\ChecklistsController::edit/$1', ['filter' => 'permission:checklists_edit']);
        $routes->post('edit', '\Modules\Backend\Checklists\Controllers\ChecklistsController::edit', ['filter' => 'permission:checklists_edit']);
        $routes->get('show/(:uuid)', '\Modules\Backend\Checklists\Controllers\ChecklistsController::show/$1', ['filter' => 'permission:checklists_show']);
        $routes->post('hardDelete', '\Modules\Backend\Checklists\Controllers\ChecklistsController::hardDelete', ['filter' => 'permission:checklists_delete']);
        $routes->post('softDelete', '\Modules\Backend\Checklists\Controllers\ChecklistsController::softDelete', ['filter' => 'permission:checklists_delete']);
        $routes->post('restoreDelete', '\Modules\Backend\Checklists\Controllers\ChecklistsController::restoreDelete', ['filter' => 'permission:checklists_restore']);
        $routes->post('getGeneralData', '\Modules\Backend\Checklists\Controllers\ChecklistsController::getGeneralData');
        $routes->post('getMetaData', '\Modules\Backend\Checklists\Controllers\ChecklistsController::getMetaData');
        $routes->post('getItems', '\Modules\Backend\Checklists\Controllers\ChecklistsController::getItems', ['filter' => 'permission:checklists_apply']);
    });
});
