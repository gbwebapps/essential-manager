<?php 

/* Rotte Frontend */
$routes->get('/', '\App\Controllers\Frontend\HomeController::index');
$routes->post('interface/language', '\App\Controllers\Frontend\InterfaceController::changeLanguage');

/** Enables public contact routes without affecting the administrative inbox. */
if (config(\Config\Frontend\Contacts::class)->enabled):
    $routes->get('contacts', '\App\Controllers\Frontend\ContactsController::index');
    $routes->post('contacts', '\App\Controllers\Frontend\ContactsController::send');
    $routes->get('contacts/privacy', '\App\Controllers\Frontend\ContactsController::privacy');
endif;


/* Accesso clienti: non usa i filtri di autenticazione degli amministratori. */
$routes->group('', ['filter' => 'usersEnabled'], function($routes) {
    $routes->group('auth', ['filter' => 'userGuest'], function($routes) {
        $routes->get('/', '\App\Controllers\Frontend\AuthController::index');
        $routes->match(['GET', 'POST'], 'login', '\App\Controllers\Frontend\AuthController::login');
        $routes->match(['GET', 'POST'], 'register', '\App\Controllers\Frontend\AuthController::register');
        $routes->match(['GET', 'POST'], 'resetPassword', '\App\Controllers\Frontend\AuthController::resetPassword');
        $routes->get('setPassword/(:token)', '\App\Controllers\Frontend\AuthController::setPassword/$1');
        $routes->post('setPassword', '\App\Controllers\Frontend\AuthController::setPassword');
        $routes->match(['GET', 'POST'], 'verify', '\App\Controllers\Frontend\AuthController::verify');
        $routes->match(['GET', 'POST'], 'restore', '\App\Controllers\Frontend\AuthController::restore');
    });
    $routes->post('auth/logout', '\App\Controllers\Frontend\AuthController::logout', ['filter' => 'userAuthorization']);
    $routes->group('account', ['filter' => 'userAuthorization'], function($routes) {
        $routes->get('/', '\App\Controllers\Frontend\AccountController::index');
        $routes->get('general', '\App\Controllers\Frontend\AccountController::general');
        $routes->match(['GET', 'POST'], 'edit', '\App\Controllers\Frontend\AccountController::edit');
        $routes->get('images', '\App\Controllers\Frontend\AccountController::images');
        $routes->match(['GET', 'POST'], 'tokens', '\App\Controllers\Frontend\AccountController::tokens');
        $routes->post('deleteToken', '\App\Controllers\Frontend\AccountController::deleteToken');
        $routes->match(['GET', 'POST'], 'resetPassword', '\App\Controllers\Frontend\AccountController::resetPassword');
        $routes->get('security', '\App\Controllers\Frontend\AccountController::security');
        $routes->match(['GET', 'POST'], 'deleteAccount', '\App\Controllers\Frontend\AccountController::deleteAccount');
        $routes->post('verifyCurrentPassword', '\App\Controllers\Frontend\AccountController::verifyCurrentPassword');
        $routes->post('saveBasicMethod', '\App\Controllers\Frontend\AccountController::saveBasicMethod');
        $routes->post('setupTotp', '\App\Controllers\Frontend\AccountController::setupTotp');
        $routes->post('confirmTotp', '\App\Controllers\Frontend\AccountController::confirmTotp');
        $routes->post('cancelTotpSetup', '\App\Controllers\Frontend\AccountController::cancelTotpSetup');
        $routes->post('uploadPreview/saveImages', '\App\Controllers\Frontend\Components\UploadPreviewController::saveImages');
        $routes->post('galleryOne/showGallery', '\App\Controllers\Frontend\Components\GalleryOneController::showGallery');
        $routes->post('galleryOne/deleteImage', '\App\Controllers\Frontend\Components\GalleryOneController::deleteImage');
        $routes->post('galleryOne/removeCover', '\App\Controllers\Frontend\Components\GalleryOneController::removeCover');
        $routes->post('galleryOne/setCover', '\App\Controllers\Frontend\Components\GalleryOneController::setCover');
    });
});

/** Registers public routes for the configured information pages. */
$pages = config(\Config\Frontend\Pages::class);
if ($pages->enabled):
    foreach (array_keys($pages->items) as $slug):
        $routes->get($slug, '\App\Controllers\Frontend\PagesController::show/' . $slug, ['as' => 'frontend-page-' . $slug]);
    endforeach;
endif;
