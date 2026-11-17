<!DOCTYPE html>
<html lang="<?= esc(service('request')->getLocale(), 'attr'); ?>" class="h-100">

<head>
    <?= $this->include('frontend/template/headPartial'); ?>
</head>

<body class="d-flex text-center frontend-users-layout">
    <div class="cover-container d-flex w-100 h-100 p-3 mx-auto flex-column">
        <header class="mb-auto" id="navbar-top-view">
            <?= $this->include('frontend/template/headerPartial'); ?>
        </header>

        <main class="px-3 py-4 frontend-users-main">
            <?= $this->include('frontend/template/messagesPartial'); ?>
            <div class="frontend-auth-content mx-auto w-100 text-start">
                <h1 class="h4 mb-4 text-center"><?= ui_text('frontend/auth.titles.restore', [], true); ?></h1>
                <p><?= ui_text('frontend/auth.messages.confirmAccountRestore', [], true); ?></p>
                <form id="restore-account-form" method="post" action="<?= esc(base_url('auth/restore'), 'attr'); ?>">
                    <input type="hidden" name="confirmation" value="<?= esc($removalConfirmation, 'attr'); ?>">
                    <div class="d-flex flex-wrap gap-2">
                        <button type="submit" class="btn app-btn-success"><?= ui_text('frontend/auth.buttons.restoreAccount', [], true); ?></button>
                        <a class="btn app-btn-secondary" href="<?= esc(base_url('auth/login'), 'attr'); ?>"><?= ui_text('frontend/auth.links.returnToLogin', [], true); ?></a>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <?= $this->include('frontend/template/scriptsPartial'); ?>
</body>

</html>
