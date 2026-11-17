<?php declare(strict_types = 1);

use App\Filters\LanguageFilter;
use App\Libraries\Backend\InterfacePreferences;
use App\Libraries\HttpErrorLocale;
use Config\Backend\General;

helper('url');

$request = service('request');
$isBackend = LanguageFilter::isBackendRequest($request);
$locale = HttpErrorLocale::resolve($request, $isBackend, $status);
$errorKey = $status === 404 ? 'notFound' : 'internalError';
$heading = lang('httpErrors.' . $errorKey . '.title', [], $locale);
$description = lang('httpErrors.' . $errorKey . '.message', [], $locale);
$backPath = $isBackend ? 'backend/dashboard' : '';
$backLabel = lang('httpErrors.' . ($isBackend ? 'backToDashboard' : 'backToHome'), [], $locale);

if ( ! headers_sent()):
    header('Cache-Control: no-store');
endif;

if ($isBackend):
    $theme = config(General::class)->theme;
    $theme = in_array($theme, InterfacePreferences::THEMES, true) ? $theme : 'grey';
endif;
?>
<!DOCTYPE html>
<html lang="<?= esc($locale, 'attr'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title><?= $status; ?> · <?= esc($heading); ?></title>
    <link rel="stylesheet" href="<?= esc(base_url('assets/vendor/bootstrap/css/bootstrap.min.css'), 'attr'); ?>">
    <?php if ( ! headers_sent()):
    header('Cache-Control: no-store');
endif;

if ($isBackend): ?>
        <link rel="stylesheet" href="<?= esc(base_url('assets/css/backend/' . $theme . '.css'), 'attr'); ?>">
        <link rel="stylesheet" href="<?= esc(base_url('assets/css/backend/backend.css'), 'attr'); ?>">
    <?php else: ?>
        <link rel="stylesheet" href="<?= esc(base_url('assets/css/frontend/frontend.css?v=9'), 'attr'); ?>">
    <?php endif; ?>
</head>
<body class="p-0">
    <main class="container min-vh-100 d-flex align-items-center justify-content-center py-4">
        <section class="col-12 col-md-9 col-lg-7 col-xl-6 text-center p-4 p-md-5 rounded-3 border <?= $isBackend ? 'app-bg-surface-elevated shadow-sm' : 'bg-dark bg-opacity-25 border-light border-opacity-25'; ?>" aria-labelledby="error-title">
            <p class="display-3 fw-bold mb-2" aria-hidden="true"><?= $status; ?></p>
            <h1 id="error-title" class="h3 mb-3"><?= esc($heading); ?></h1>
            <p class="mb-4"><?= esc($description); ?></p>
            <a class="btn <?= $isBackend ? 'app-btn-secondary' : 'btn-outline-light'; ?>" href="<?= esc(site_url($backPath), 'attr'); ?>"><?= esc($backLabel); ?></a>
        </section>
    </main>
</body>
</html>
