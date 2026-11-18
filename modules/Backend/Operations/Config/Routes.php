<?php declare(strict_types = 1);

/**
 * Rotte previste del modulo Operations.
 * Il registrar carica questo file; tutte le rotte appartengono al backend autorizzato.
 * I metodi del controller sono ancora da implementare.
 *
 * @var \CodeIgniter\Router\RouteCollection $routes
 */
$routes->group('backend', ['filter' => 'authorization'], function($routes) {
    $routes->group('operations', function($routes) {
        $routes->get('/', '\Modules\Backend\Operations\Controllers\OperationsController::index', ['filter' => 'permission:operations_show_all']);
        $routes->match(['GET', 'POST'], 'showAll', '\Modules\Backend\Operations\Controllers\OperationsController::showAll', ['filter' => 'permission:operations_show_all']);
        $routes->get('show/(:uuid)', '\Modules\Backend\Operations\Controllers\OperationsController::show/$1', ['filter' => 'permission:operations_show']);
        $routes->post('getMetaData', '\Modules\Backend\Operations\Controllers\OperationsController::getMetaData', ['filter' => 'permission:operations_show']);
        $routes->post('check', '\Modules\Backend\Operations\Controllers\OperationsController::check', ['filter' => 'permission:operations_check']);
        $routes->post('tests', '\Modules\Backend\Operations\Controllers\OperationsController::tests', ['filter' => 'permission:operations_tests']);
        $routes->post('update', '\Modules\Backend\Operations\Controllers\OperationsController::update', ['filter' => 'permission:operations_update']);
        $routes->post('commit', '\Modules\Backend\Operations\Controllers\OperationsController::commit', ['filter' => 'permission:operations_commit']);
        $routes->post('push', '\Modules\Backend\Operations\Controllers\OperationsController::push', ['filter' => 'permission:operations_push']);
        $routes->post('cloneApp', '\Modules\Backend\Operations\Controllers\OperationsController::cloneApp', ['filter' => 'permission:operations_clone']);
        $routes->post('getReport', '\Modules\Backend\Operations\Controllers\OperationsController::getReport', ['filter' => 'permission:operations_show']);
    });
});
