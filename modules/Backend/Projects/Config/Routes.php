<?php declare(strict_types = 1);

/**
 * Rotte operative del modulo Projects.
 * Il registrar carica questo file; tutte le rotte appartengono al backend autorizzato.
 * Permessi e contesto dei record sono verificati anche dai controller applicativi.
 *
 * @var \CodeIgniter\Router\RouteCollection $routes
 */
$routes->group('backend', ['filter' => 'authorization'], function($routes) {
    $routes->group('projects', function($routes) {
        $routes->get('/', '\Modules\Backend\Projects\Controllers\ProjectsController::index', ['filter' => 'permission:projects_show_all']);
        $routes->match(['GET', 'POST'], 'showAll', '\Modules\Backend\Projects\Controllers\ProjectsController::showAll', ['filter' => 'permission:projects_show_all']);
        $routes->match(['GET', 'POST'], 'add', '\Modules\Backend\Projects\Controllers\ProjectsController::add', ['filter' => 'permission:projects_add']);
        $routes->get('edit/(:uuid)', '\Modules\Backend\Projects\Controllers\ProjectsController::edit/$1', ['filter' => ['permission:projects_edit']]);
        $routes->post('edit', '\Modules\Backend\Projects\Controllers\ProjectsController::edit', ['filter' => ['permission:projects_edit']]);
        $routes->get('show/(:uuid)', '\Modules\Backend\Projects\Controllers\ProjectsController::show/$1', ['filter' => ['permission:projects_show']]);
        $routes->post('hardDelete', '\Modules\Backend\Projects\Controllers\ProjectsController::hardDelete', ['filter' => ['permission:projects_delete']]);
        $routes->post('softDelete', '\Modules\Backend\Projects\Controllers\ProjectsController::softDelete', ['filter' => ['permission:projects_delete']]);
        $routes->post('restoreDelete', '\Modules\Backend\Projects\Controllers\ProjectsController::restoreDelete', ['filter' => ['permission:projects_restore']]);
        $routes->post('getGeneralData', '\Modules\Backend\Projects\Controllers\ProjectsController::getGeneralData', ['filter' => ['permission:projects_show,projects_edit']]);
        $routes->post('getMetaData', '\Modules\Backend\Projects\Controllers\ProjectsController::getMetaData', ['filter' => ['permission:projects_show,projects_edit']]);
        $routes->post('validateTaskDraft', '\Modules\Backend\Projects\Controllers\ProjectsController::validateTaskDraft', ['filter' => 'permission:projects_add']);
        $routes->post('validateNoteDraft', '\Modules\Backend\Projects\Controllers\ProjectsController::validateNoteDraft', ['filter' => 'permission:projects_add']);
        $routes->post('saveTask', '\Modules\Backend\Projects\Controllers\ProjectsController::saveTask', ['filter' => 'permission:projects_show_all,projects_show,projects_edit']);
        $routes->post('getTaskForModal', '\Modules\Backend\Projects\Controllers\ProjectsController::getTaskForModal', ['filter' => 'permission:projects_show_all,projects_show,projects_edit']);
        $routes->post('saveNote', '\Modules\Backend\Projects\Controllers\ProjectsController::saveNote', ['filter' => 'permission:projects_show_all,projects_show,projects_edit']);
        $routes->post('deleteTask', '\Modules\Backend\Projects\Controllers\ProjectsController::deleteTask', ['filter' => 'permission:projects_edit']);
        $routes->post('deleteNote', '\Modules\Backend\Projects\Controllers\ProjectsController::deleteNote', ['filter' => 'permission:projects_edit']);
        $routes->post('getTasks', '\Modules\Backend\Projects\Controllers\ProjectsController::getTasks', ['filter' => ['permission:projects_show_all,projects_show,projects_edit']]);
        $routes->post('getNotes', '\Modules\Backend\Projects\Controllers\ProjectsController::getNotes', ['filter' => ['permission:projects_show_all,projects_show,projects_edit']]);
    });
});
