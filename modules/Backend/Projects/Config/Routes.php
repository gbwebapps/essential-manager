<?php declare(strict_types = 1);

/**
 * Rotte previste del modulo Projects.
 * Il registrar carica questo file; tutte le rotte appartengono al backend autorizzato.
 * Le pagine mostrano dati fittizi; i salvataggi non sono attivi.
 *
 * @var \CodeIgniter\Router\RouteCollection $routes
 */
$routes->group('backend', ['filter' => 'authorization'], function($routes) {
    $routes->group('projects', function($routes) {
        $routes->get('/', '\Modules\Backend\Projects\Controllers\ProjectsController::index', ['filter' => 'permission:projects_show_all']);
        $routes->match(['GET', 'POST'], 'showAll', '\Modules\Backend\Projects\Controllers\ProjectsController::showAll', ['filter' => 'permission:projects_show_all']);
        $routes->match(['GET', 'POST'], 'add', '\Modules\Backend\Projects\Controllers\ProjectsController::add', ['filter' => 'permission:projects_add']);
        $routes->get('edit/(:uuid)', '\Modules\Backend\Projects\Controllers\ProjectsController::edit/$1', ['filter' => 'permission:projects_edit']);
        $routes->post('edit', '\Modules\Backend\Projects\Controllers\ProjectsController::edit', ['filter' => 'permission:projects_edit']);
        $routes->get('show/(:uuid)', '\Modules\Backend\Projects\Controllers\ProjectsController::show/$1', ['filter' => 'permission:projects_show']);
        $routes->post('hardDelete', '\Modules\Backend\Projects\Controllers\ProjectsController::hardDelete', ['filter' => 'permission:projects_delete']);
        $routes->post('softDelete', '\Modules\Backend\Projects\Controllers\ProjectsController::softDelete', ['filter' => 'permission:projects_delete']);
        $routes->post('restoreDelete', '\Modules\Backend\Projects\Controllers\ProjectsController::restoreDelete', ['filter' => 'permission:projects_restore']);
        $routes->post('getGeneralData', '\Modules\Backend\Projects\Controllers\ProjectsController::getGeneralData', ['filter' => 'permission:projects_show,projects_edit']);
        $routes->post('getMetaData', '\Modules\Backend\Projects\Controllers\ProjectsController::getMetaData', ['filter' => 'permission:projects_show,projects_edit']);
    });
});
