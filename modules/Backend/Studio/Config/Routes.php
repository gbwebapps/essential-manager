<?php declare(strict_types = 1);

/** @var \CodeIgniter\Router\RouteCollection $routes Rotte del contesto Studio autorizzato. */
$routes->group('backend', ['filter' => 'authorization'], function ($routes) {
    $routes->group('studio', ['filter' => 'permission:studio_show'], function ($routes) {
        $routes->get('archived', '\Modules\Backend\Studio\Controllers\StudioController::archived');
        $routes->get('/', '\Modules\Backend\Studio\Controllers\StudioController::index');
        $routes->get('notes', '\Modules\Backend\Studio\Controllers\StudioController::index/notes', ['filter'=>'permission:notes_show']);
        $routes->group('tasks', function ($routes) {
            $controller = '\\Modules\\Backend\\Studio\\Controllers\\StudioTasksController::';
            $routes->match(['GET','POST'], 'add', $controller . 'add', ['filter'=>'permission:tasks_add']);
            $routes->get('edit/(:uuid)', $controller . 'edit/$1', ['filter'=>'permission:tasks_edit']);
            $routes->post('edit', $controller . 'edit', ['filter'=>'permission:tasks_edit']);
            $routes->get('show/(:uuid)', $controller . 'show/$1', ['filter'=>'permission:tasks_show']);
            foreach (['getGeneralData','getMetaData','getNotes','saveNote','deleteNote'] as $method):
                $routes->post($method, $controller . $method, ['filter'=>'permission:tasks_show,tasks_edit']);
            endforeach;
            foreach (['complete','reopen','completeStep','reopenStep'] as $method):
                $routes->post($method, $controller . $method, ['filter'=>'permission:tasks_complete']);
            endforeach;
            foreach (['archive','unarchive'] as $method):
                $routes->post($method, $controller . $method, ['filter'=>'permission:tasks_archive']);
            endforeach;
            $routes->post('setCurrent', $controller . 'setCurrent', ['filter'=>'permission:tasks_set_current']);
        });
        $routes->post('getTasks', '\Modules\Backend\Studio\Controllers\StudioController::getTasks');
        $routes->post('getNotes', '\Modules\Backend\Studio\Controllers\StudioController::getNotes');
        $routes->post('getTask', '\Modules\Backend\Studio\Controllers\StudioController::getTask');
        $routes->post('saveNote', '\Modules\Backend\Studio\Controllers\StudioController::saveNote');
        $routes->post('deleteTask', '\Modules\Backend\Studio\Controllers\StudioController::deleteTask');
        $routes->post('deleteNote', '\Modules\Backend\Studio\Controllers\StudioController::deleteNote');
    });
});
