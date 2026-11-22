<?php declare(strict_types = 1);

/**
 * Rotte operative del modulo Applications.
 * Il registrar carica questo file; tutte le rotte appartengono al backend autorizzato.
 * Permessi e contesto dei record sono verificati anche dai controller applicativi.
 *
 * @var \CodeIgniter\Router\RouteCollection $routes
 */
$routes->group('backend', ['filter' => 'authorization'], function($routes) {
    $routes->group('applications', function($routes) {
        $routes->get('/', '\Modules\Backend\Applications\Controllers\ApplicationsController::index', ['filter' => 'permission:applications_show_all']);
        $routes->match(['GET', 'POST'], 'showAll', '\Modules\Backend\Applications\Controllers\ApplicationsController::showAll', ['filter' => 'permission:applications_show_all']);
        $routes->match(['GET', 'POST'], 'add', '\Modules\Backend\Applications\Controllers\ApplicationsController::add', ['filter' => 'permission:applications_add']);
        $routes->get('edit/(:uuid)', '\Modules\Backend\Applications\Controllers\ApplicationsController::edit/$1', ['filter' => ['permission:applications_edit']]);
        $routes->post('edit', '\Modules\Backend\Applications\Controllers\ApplicationsController::edit', ['filter' => ['permission:applications_edit']]);
        $routes->get('show/(:uuid)', '\Modules\Backend\Applications\Controllers\ApplicationsController::show/$1', ['filter' => ['permission:applications_show']]);
        $routes->post('hardDelete', '\Modules\Backend\Applications\Controllers\ApplicationsController::hardDelete', ['filter' => ['permission:applications_delete']]);
        $routes->post('softDelete', '\Modules\Backend\Applications\Controllers\ApplicationsController::softDelete', ['filter' => ['permission:applications_delete']]);
        $routes->post('restoreDelete', '\Modules\Backend\Applications\Controllers\ApplicationsController::restoreDelete', ['filter' => ['permission:applications_restore']]);
        $routes->post('getGeneralData', '\Modules\Backend\Applications\Controllers\ApplicationsController::getGeneralData', ['filter' => ['permission:applications_show,applications_edit']]);
        $routes->post('getMetaData', '\Modules\Backend\Applications\Controllers\ApplicationsController::getMetaData', ['filter' => ['permission:applications_show,applications_edit']]);
        $routes->post('validateTaskDraft', '\Modules\Backend\Applications\Controllers\ApplicationsController::validateTaskDraft', ['filter' => 'permission:applications_add']);
        $routes->post('validateNoteDraft', '\Modules\Backend\Applications\Controllers\ApplicationsController::validateNoteDraft', ['filter' => 'permission:applications_add']);
        $routes->post('saveTask', '\Modules\Backend\Applications\Controllers\ApplicationsController::saveTask', ['filter' => 'permission:applications_show_all,applications_show,applications_edit']);
        $routes->post('saveNote', '\Modules\Backend\Applications\Controllers\ApplicationsController::saveNote', ['filter' => 'permission:applications_show_all,applications_show,applications_edit']);
        $routes->post('deleteTask', '\Modules\Backend\Applications\Controllers\ApplicationsController::deleteTask', ['filter' => 'permission:applications_edit']);
        $routes->post('deleteNote', '\Modules\Backend\Applications\Controllers\ApplicationsController::deleteNote', ['filter' => 'permission:applications_edit']);
        $routes->post('getTasks', '\Modules\Backend\Applications\Controllers\ApplicationsController::getTasks', ['filter' => ['permission:applications_show_all,applications_show,applications_edit']]);
        $routes->post('getNotes', '\Modules\Backend\Applications\Controllers\ApplicationsController::getNotes', ['filter' => ['permission:applications_show_all,applications_show,applications_edit']]);
    });
});
