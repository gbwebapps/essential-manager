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
                    <h1 class="account-section-heading h4 mb-4" id="account-section-title">
                        <?= $sections['deleteAccount']['icon']; ?> <?= ui_text('frontend/account.titles.deleteAccount', [], false); ?>
                    </h1>
                    <p><?= ui_text('frontend/account.messages.removalDescription', [$removalDays], true); ?></p>
                    <p><?= ui_text('frontend/account.messages.removalEmail', [], true); ?></p>

                    <form <?= ui_binding('frontend/account.messages.confirmRemoval', 'data-message', [$removalDays]); ?> id="account-delete" data-message="<?= esc(sprintf(lang('frontend/account.messages.confirmRemoval'), $removalDays), 'attr'); ?>">
                        <button type="submit" class="btn app-btn-danger">
                            <i class="fa-solid fa-user-xmark"></i> <?= ui_text('frontend/account.buttons.deleteAccount', [], false); ?>
                        </button>
                    </form>
                </section>
            </div>
        </main>
    </div>

    <?= $this->include('frontend/template/scriptsPartial'); ?>
</body>

</html>
