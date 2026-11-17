<!DOCTYPE html>
<html lang="<?= esc(service('request')->getLocale(), 'attr'); ?>" class="h-100">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php $description = esc(lang($page['description']), 'attr'); ?>
    <meta name="description" data-frontend-site-description data-page-description="<?= $description; ?>" <?= ui_binding($page['description'], 'data-page-description'); ?> content="<?= $description; ?>">
    <meta name="csrf-token" id="csrf-meta" content="<?= csrf_hash(); ?>">

    <title <?= ui_binding($page['title'], 'text', [], true); ?>><?= esc(lang($page['title'])); ?></title>

    <link rel="shortcut icon" type="image/png" href="<?= base_url('favicon.ico'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/bootstrap/css/bootstrap.min.css'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/fontawesome/css/all.min.css'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/tom-select/css/tom-select.bootstrap5.min.css'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/frontend/frontend.css?v=10'); ?>">
</head>

<body class="d-flex h-100 text-center">
    <div class="cover-container d-flex w-100 h-100 p-3 mx-auto flex-column">
        <header>
            <?= $this->include('frontend/template/headerPartial'); ?>
        </header>

        <main class="px-3 py-4 flex-grow-1 text-start">
            <article class="col-lg-9 mx-auto">
                <h1 class="border-bottom pb-2 mb-4"><?= ui_text($page['title'], [], true); ?></h1>
                <?= $this->include($page['view']); ?>
            </article>
        </main>
    </div>
    <?= $this->include('frontend/template/scriptsPartial'); ?>
</body>

</html>
