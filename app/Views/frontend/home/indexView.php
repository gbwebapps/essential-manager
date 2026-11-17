<!DOCTYPE html>
<html lang="<?= esc(service('request')->getLocale(), 'attr'); ?>" class="h-100">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" data-frontend-site-description content="<?= esc($siteDescription, 'attr'); ?>">
    <meta name="csrf-token" id="csrf-meta" content="<?= csrf_hash(); ?>">

    <title data-frontend-site-title><?= esc($siteTitle); ?></title>

    <link rel="shortcut icon" type="image/png" href="<?= base_url('favicon.ico'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/bootstrap/css/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/fontawesome/css/all.min.css'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/tom-select/css/tom-select.bootstrap5.min.css'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/frontend/frontend.css?v=9'); ?>">
</head>

<body class="d-flex h-100 text-center">
    <div class="cover-container d-flex w-100 h-100 p-3 mx-auto flex-column">
        <header>
            <?= $this->include('frontend/template/headerPartial'); ?>
        </header>

        <main class="px-3 flex-grow-1 text-start">
            <?php /* Vista facoltativa dell'applicazione, lasciata fuori dal package. */ ?>
            <?php if (is_file(APPPATH . 'Views/frontend/home/contentView.php')): ?>
                <?= $this->include('frontend/home/contentView'); ?>
            <?php endif; ?>
        </main>
    </div>
    <?= $this->include('frontend/template/scriptsPartial'); ?>
</body>

</html>
