<?php declare(strict_types = 1);

if (ENVIRONMENT === 'development' && filter_var(ini_get('display_errors'), FILTER_VALIDATE_BOOL)):
    require __DIR__ . '/error_exception.php';
    return;
endif;

$status = 500;
require __DIR__ . '/statusPage.php';
