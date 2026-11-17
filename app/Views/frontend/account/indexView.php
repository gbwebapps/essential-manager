<!DOCTYPE html>
<html lang="<?= esc(service('request')->getLocale(), 'attr'); ?>" class="h-100">

<head>
    <?= $this->include('frontend/template/headPartial'); ?>
</head>

<body class="d-flex text-center frontend-users-layout">
    <div class="cover-container d-flex w-100 p-3 mx-auto flex-column">
        <header class="mb-auto" id="navbar-top-view">
            <?= $this->include('frontend/template/headerPartial'); ?>
        </header>

        <main class="px-3 py-4 frontend-users-main">
            <?= $this->include('frontend/template/messagesPartial'); ?>
            <section class="account-section text-start" <?= ui_binding('frontend/account.titles.index', 'aria-label'); ?> aria-label="<?= esc(lang('frontend/account.titles.index'), 'attr'); ?>">
                <!-- La pagina iniziale presenta le sezioni; i dati personali restano in General Data. -->
                <div class="row g-3 justify-content-center" id="buttons-block">
                    <?php foreach ($sections as $key => $section): ?>
                        <div class="col-12 col-md-6 col-xl-4">
                            <a href="<?= esc(base_url($section['route']), 'attr'); ?>" class="d-block h-100 text-decoration-none text-reset">
                                <div class="card h-100 app-bg-surface app-text-muted shadow-sm" id="<?= esc($key, 'attr'); ?>">
                                    <div class="card-body d-flex align-items-center justify-content-between gap-3 p-4">
                                        <div class="fs-2 flex-shrink-0" aria-hidden="true"><?= $section['icon']; ?></div>
                                        <div class="fw-bold text-end"><?= ui_text('frontend/account.leftMenu.' . $key, [], true); ?></div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </main>
    </div>

    <?= $this->include('frontend/template/scriptsPartial'); ?>
</body>

</html>
