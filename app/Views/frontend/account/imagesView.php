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

        <main class="px-3 py-4 frontend-users-main frontend-account-main">
            <?= $this->include('frontend/template/messagesPartial'); ?>
            <div class="account-layout">
                <?= $this->include('frontend/account/partials/common/leftMenuPartial'); ?>

                <section class="account-section text-start" aria-labelledby="account-section-title">
                    <div class="account-section-heading d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                        <h1 class="h4 mb-0" id="account-section-title">
                            <?= $sections['images']['icon']; ?> <?= ui_text('frontend/account.titles.images', [], false); ?>
                        </h1>
                    </div>
                    <div id="images-account-container">
                        <?= $this->include('frontend/account/partials/images/imagesPartial'); ?>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <?= $this->include('frontend/template/scriptsPartial'); ?>
</body>

</html>
