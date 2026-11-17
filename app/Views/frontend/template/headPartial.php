<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" id="csrf-meta" content="<?= csrf_hash(); ?>">
<meta name="robots" content="noindex,nofollow">

<?php $titleKey = ($controller === 'auth') ? 'frontend/auth.titles.' . $action : 'frontend/account.titles.index'; ?>
<title <?= ui_binding($titleKey, 'text', [], true, ' | ' . $siteName); ?>><?= esc($title ?? lang('frontend/account.titles.index')); ?> | <?= esc($siteName); ?></title>

<link rel="shortcut icon" type="image/png" href="<?= base_url('favicon.ico'); ?>">
<link rel="stylesheet" href="<?= base_url('assets/vendor/bootstrap/css/bootstrap.min.css'); ?>">
<link rel="stylesheet" href="<?= base_url('assets/vendor/fontawesome/css/all.min.css'); ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/backend/backend.css'); ?>">
<link rel="stylesheet" href="<?= base_url('assets/vendor/tom-select/css/tom-select.bootstrap5.min.css'); ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/frontend/frontend.css?v=9'); ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/frontend/users.css?v=12'); ?>">
