<?php 

/**
 * Backend Routing Configuration
 *
 * Mappatura completa degli URL, raggruppamento logico dei moduli amministrativi
 * e applicazione chirurgica dei filtri di protezione (guest, authorization, superadmin).
 */

/* REDIRECT BACKEND */
$routes->get('backend', function() {
    return redirect()->to('backend/dashboard');
});

/* BACKEND */
$routes->group('backend', function($routes) {

    /* AUTH GUEST */
    $routes->group('auth', ['filter' => 'guest'], function($routes) {
        $routes->match(['GET', 'POST'], '/', '\App\Controllers\Backend\AuthController::index');
        $routes->match(['GET', 'POST'], 'login', '\App\Controllers\Backend\AuthController::login');
        $routes->match(['GET', 'POST'], 'resetPassword', '\App\Controllers\Backend\AuthController::resetPassword');
        $routes->get('setPassword/(:token)', '\App\Controllers\Backend\AuthController::setPassword/$1');
        $routes->post('setPassword', '\App\Controllers\Backend\AuthController::setPassword');
        $routes->match(['GET', 'POST'], 'verify', '\App\Controllers\Backend\AuthController::verify');
    });

    /* LOGOUT (AUTHORIZED) */
    $routes->post('auth/logout', '\App\Controllers\Backend\AuthController::logout', ['filter' => 'authorization']);

    /* AUTHORIZED */
    $routes->group('', ['filter' => 'authorization'], function($routes) {
        
        /* Preferenze temporanee dell'interfaccia, disponibili agli amministratori autenticati. */
        $routes->post('interface/change', '\App\Controllers\Backend\InterfaceController::change');

        /* DASHBOARD */
        $routes->get('dashboard', '\App\Controllers\Backend\DashboardController::index');
        $routes->post('dashboard/summary/(:segment)', '\App\Controllers\Backend\DashboardController::reloadSummary/$1');
        
        /* ACCOUNT */
        $routes->group('account', function($routes) {
            $routes->get('/', '\App\Controllers\Backend\AccountController::index');
            $routes->get('general', '\App\Controllers\Backend\AccountController::general');
            $routes->match(['GET', 'POST'], 'edit', '\App\Controllers\Backend\AccountController::edit');
            $routes->match(['GET', 'POST'], 'permissions', '\App\Controllers\Backend\AccountController::permissions');
            $routes->get('images', '\App\Controllers\Backend\AccountController::images');

            $routes->group('uploadPreview', function($routes) {
                $routes->post('saveImages', '\App\Controllers\Backend\Components\UploadPreviewController::saveImages');
            });

            $routes->group('galleryOne', function($routes) {
                $routes->post('showGallery', '\App\Controllers\Backend\Components\GalleryOneController::showGallery/account');
                $routes->post('deleteImage', '\App\Controllers\Backend\Components\GalleryOneController::deleteImage/account');
                $routes->post('removeCover', '\App\Controllers\Backend\Components\GalleryOneController::removeCover/account');
                $routes->post('setCover', '\App\Controllers\Backend\Components\GalleryOneController::setCover/account');
            });

            $routes->match(['GET', 'POST'], 'tokens', '\App\Controllers\Backend\AccountController::tokens');
            $routes->post('deleteToken', '\App\Controllers\Backend\AccountController::deleteToken');
            
            $routes->match(['GET', 'POST'], 'resetPassword', '\App\Controllers\Backend\AccountController::resetPassword');

            $routes->get('security', '\App\Controllers\Backend\AccountController::security');
            $routes->post('verifyCurrentPassword', '\App\Controllers\Backend\AccountController::verifyCurrentPassword');
            $routes->post('saveBasicMethod', '\App\Controllers\Backend\AccountController::saveBasicMethod');
            $routes->post('setupTotp', '\App\Controllers\Backend\AccountController::setupTotp');
            $routes->post('confirmTotp', '\App\Controllers\Backend\AccountController::confirmTotp');
			$routes->post('cancelTotpSetup', '\App\Controllers\Backend\AccountController::cancelTotpSetup');
        });




        /* USERS */
        if (config(\Config\Backend\Features::class)->usersEnabled):
            $routes->group('users', function($routes) {
                $routes->get('/', function() { return redirect()->to('backend/users/showAll'); }, ['filter' => 'permission:users_show_all']);
                $routes->match(['GET', 'POST'], 'showAll', '\App\Controllers\Backend\UsersController::showAll', ['filter' => 'permission:users_show_all']);
                $routes->get('show/(:uuid)', '\App\Controllers\Backend\UsersController::show/$1', ['filter' => 'permission:users_show']);
                $routes->post('getGeneralData', '\App\Controllers\Backend\UsersController::getGeneralData', ['filter' => 'permission:users_show']);
                $routes->post('getMetaData', '\App\Controllers\Backend\UsersController::getMetaData', ['filter' => 'permission:users_show']);
                $routes->post('getTokens', '\App\Controllers\Backend\UsersController::getTokens', ['filter' => 'permission:users_show']);
                $routes->post('restoreDelete', '\App\Controllers\Backend\UsersController::restoreDelete', ['filter' => 'permission:users_restore']);
                $routes->post('hardDelete', '\App\Controllers\Backend\UsersController::hardDelete', ['filter' => 'permission:users_delete']);
                $routes->post('changeStatus', '\App\Controllers\Backend\UsersController::changeStatus', ['filter' => 'permission:users_change_status']);
                $routes->post('resetPassword', '\App\Controllers\Backend\UsersController::resetPassword', ['filter' => 'permission:users_reset_password']);
                $routes->post('deleteToken', '\App\Controllers\Backend\UsersController::deleteToken', ['filter' => 'permission:users_delete_token']);
            });
        endif;

        /* MESSAGES */
        if (config(\Config\Backend\Features::class)->contactsEnabled):
            $routes->group('messages', function($routes) {
                $routes->match(['GET', 'POST'], 'showAll', '\App\Controllers\Backend\MessagesController::showAll', ['filter' => 'permission:messages_show_all']);
                $routes->get('show/(:num)', '\App\Controllers\Backend\MessagesController::show/$1', ['filter' => 'permission:messages_show']);
                $routes->post('getGeneralData', '\App\Controllers\Backend\MessagesController::getGeneralData', ['filter' => 'permission:messages_show']);
                $routes->post('changeStatus/(:num)', '\App\Controllers\Backend\MessagesController::changeStatus/$1', ['filter' => 'permission:messages_mark']);
            });
        endif;

        /* EXPORT CSV */
        $routes->group('export', ['filter' => 'permission:all,users_export,messages_export'], function($routes) {
            $routes->post('showModal', '\App\Controllers\Backend\Components\ExportController::showModal');
            $routes->post('generate', '\App\Controllers\Backend\Components\ExportController::generate');
            $routes->post('remove', '\App\Controllers\Backend\Components\ExportController::remove');
            $routes->get('download/(:any)', '\App\Controllers\Backend\Components\ExportController::download/$1');
        });

        /* IMAGES PREVIEW */
        $routes->group('uploadPreview', ['filter' => 'authorization'], function($routes) {
            $routes->post('saveImages', '\App\Controllers\Backend\Components\UploadPreviewController::saveImages');
        });

        /* GALLERY ONE */
        $routes->group('galleryOne', ['filter' => 'authorization'], function($routes) {
            $routes->post('showGallery', '\App\Controllers\Backend\Components\GalleryOneController::showGallery');
            $routes->post('deleteImage', '\App\Controllers\Backend\Components\GalleryOneController::deleteImage');
            $routes->post('removeCover', '\App\Controllers\Backend\Components\GalleryOneController::removeCover');
            $routes->post('setCover', '\App\Controllers\Backend\Components\GalleryOneController::setCover');
        });

    });

    /* SUPERADMIN */
    $routes->group('', ['filter' => ['authorization', 'superadmin']], function($routes) {


        
        /* IMPORT CSV */
        $routes->group('import', function($routes) {
            /* Import applicativo CRUD: le regole del model di dominio sono opt-in. */
            $routes->post('showModal', '\App\Controllers\Backend\Components\ImportController::showModal');
            $routes->get('download/(:any)', '\App\Controllers\Backend\Components\ImportController::download/$1');
            $routes->post('processCsv', '\App\Controllers\Backend\Components\ImportController::processCsv');


            /* Esecuzione e cleanup sono condivisi: il mode è già vincolato nel manifest server-side. */
            $routes->post('executeImport', '\App\Controllers\Backend\Components\ImportController::executeImport');
            $routes->post('deleteFile', '\App\Controllers\Backend\Components\ImportController::deleteFile');
        });

        
        /* SETTINGS */
        $routes->group('settingsBackend', function($routes) {
            $routes->get('/', '\App\Controllers\Backend\SettingsBackendController::index');

            $routes->post('openSettings', '\App\Controllers\Backend\SettingsBackendController::openSettings');
            $routes->post('saveSettings', '\App\Controllers\Backend\SettingsBackendController::saveSettings');
            $routes->post('deleteSettings', '\App\Controllers\Backend\SettingsBackendController::deleteSettings');
            $routes->post('checkDeleteSettings', '\App\Controllers\Backend\SettingsBackendController::checkDeleteSettings');
        });

        /* SETTINGS FRONTEND: le stesse protezioni superadmin dei settings amministrativi. */
        $routes->group('settingsFrontend', function($routes) {
            $routes->get('/', '\App\Controllers\Backend\SettingsFrontendController::index');
            $routes->post('openSettings', '\App\Controllers\Backend\SettingsFrontendController::openSettings');
            $routes->post('saveSettings', '\App\Controllers\Backend\SettingsFrontendController::saveSettings');
            $routes->post('deleteSettings', '\App\Controllers\Backend\SettingsFrontendController::deleteSettings');
            $routes->post('checkDeleteSettings', '\App\Controllers\Backend\SettingsFrontendController::checkDeleteSettings');
        });

        /* TOOLS */
        $routes->group('tools', function($routes) {
            $routes->get('/', '\App\Controllers\Backend\ToolsController::index');

            $routes->post('openTools', '\App\Controllers\Backend\ToolsController::openTools');

            $routes->post('deleteAudits', '\App\Controllers\Backend\ToolsController::deleteAudits');
            $routes->post('validateAuditsDateRequest', '\App\Controllers\Backend\ToolsController::validateAuditsDateRequest');

            $routes->post('deleteLogs', '\App\Controllers\Backend\ToolsController::deleteLogs');
            $routes->post('validateLogsDateRequest', '\App\Controllers\Backend\ToolsController::validateLogsDateRequest');

            $routes->post('optimizeTable', '\App\Controllers\Backend\ToolsController::optimizeTable');

            $routes->post('backups', '\App\Controllers\Backend\ToolsController::backups');
            $routes->get('downloadBackups/(:segment)', '\App\Controllers\Backend\ToolsController::downloadBackups/$1');

            $routes->post('cleanFolder', '\App\Controllers\Backend\ToolsController::cleanFolder');
        });

        /* ADMIN TRANSFER */
        $routes->group('admintransfer', function($routes) {
            $routes->get('/', '\App\Controllers\Backend\AdminTransferController::index');
            $routes->get('downloadTemplate', '\App\Controllers\Backend\AdminTransferController::downloadTemplate');
            $routes->get('download/(:segment)', '\App\Controllers\Backend\AdminTransferController::download/$1');
            $routes->post('showExportModal', '\App\Controllers\Backend\AdminTransferController::showExportModal');
            $routes->post('export', '\App\Controllers\Backend\AdminTransferController::export');
            $routes->post('removeExport', '\App\Controllers\Backend\AdminTransferController::removeExport');
            $routes->post('showImportModal', '\App\Controllers\Backend\AdminTransferController::showImportModal');
            $routes->post('prepareImport', '\App\Controllers\Backend\AdminTransferController::prepareImport');
            $routes->post('executeImport', '\App\Controllers\Backend\AdminTransferController::executeImport');
            $routes->post('deleteImport', '\App\Controllers\Backend\AdminTransferController::deleteImport');
        });

        /* GROUPS */
        $routes->group('groups', function($routes) {
            $routes->get('/', '\App\Controllers\Backend\GroupsController::index');
            $routes->post('getGroups', '\App\Controllers\Backend\GroupsController::getGroups');
            $routes->post('getGroup', '\App\Controllers\Backend\GroupsController::getGroup'); 
            $routes->post('openAdd', '\App\Controllers\Backend\GroupsController::openAdd'); 
            $routes->post('add', '\App\Controllers\Backend\GroupsController::add'); 
            $routes->post('edit', '\App\Controllers\Backend\GroupsController::edit'); 
            $routes->post('del', '\App\Controllers\Backend\GroupsController::del'); 
            $routes->post('checkDeleteConstraints', '\App\Controllers\Backend\GroupsController::checkDeleteConstraints'); 
            $routes->post('openExceptions', '\App\Controllers\Backend\GroupsController::openExceptions'); 
            $routes->post('getDropdownAdmins', '\App\Controllers\Backend\GroupsController::getDropdownAdmins'); 
            $routes->post('getAdminPermissions', '\App\Controllers\Backend\GroupsController::getAdminPermissions'); 
            $routes->post('saveExceptions', '\App\Controllers\Backend\GroupsController::saveExceptions'); 
        });

        /* AUDITS */
        $routes->group('audits', function($routes) {
            $routes->match(['GET', 'POST'], '/', '\App\Controllers\Backend\AuditsController::index');
        });

        /* TOKENS */
        $routes->group('tokens', function($routes) {
            $routes->match(['GET', 'POST'], '/', '\App\Controllers\Backend\TokensController::index');
            $routes->post('hardDelete', '\App\Controllers\Backend\TokensController::hardDelete');
        });

        /* LOGS */
        $routes->group('logs', function($routes) {
            $routes->match(['GET', 'POST'], '/', '\App\Controllers\Backend\LogsController::index');
            $routes->post('disconnectSession', '\App\Controllers\Backend\LogsController::disconnectSession');
        });


        /* ADMINS */
        $routes->group('admins', function($routes) {
            // $routes->get('/', '\App\Controllers\Backend\AdminsController::index');

            $routes->get('/', function() {
                return redirect()->to('backend/admins/showAll');
            });

            $routes->match(['GET', 'POST'], 'showAll', '\App\Controllers\Backend\AdminsController::showAll');

            $routes->match(['GET', 'POST'], 'add', '\App\Controllers\Backend\AdminsController::add');

            $routes->get('edit/(:uuid)', '\App\Controllers\Backend\AdminsController::edit/$1');
            $routes->post('edit', '\App\Controllers\Backend\AdminsController::edit');

            $routes->get('show/(:uuid)', '\App\Controllers\Backend\AdminsController::show/$1');

            $routes->post('hardDelete', '\App\Controllers\Backend\AdminsController::hardDelete');
            $routes->post('softDelete', '\App\Controllers\Backend\AdminsController::softDelete');
            $routes->post('restoreDelete', '\App\Controllers\Backend\AdminsController::restoreDelete');

            $routes->post('changeStatus', '\App\Controllers\Backend\AdminsController::changeStatus');
            $routes->post('resetPassword', '\App\Controllers\Backend\AdminsController::resetPassword');

            $routes->post('getGeneralData', '\App\Controllers\Backend\AdminsController::getGeneralData');
            $routes->post('getMetaData', '\App\Controllers\Backend\AdminsController::getMetaData');
            $routes->post('getPermissions', '\App\Controllers\Backend\AdminsController::getPermissions');
            $routes->post('getTokens', '\App\Controllers\Backend\AdminsController::getTokens');
            
            $routes->post('changePermission', '\App\Controllers\Backend\AdminsController::changePermission');
            $routes->post('changeGroup', '\App\Controllers\Backend\AdminsController::changeGroup');
            $routes->post('deleteToken', '\App\Controllers\Backend\AdminsController::deleteToken');
        });
    });
});
