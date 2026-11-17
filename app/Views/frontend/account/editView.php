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
                            <?= $sections['edit']['icon']; ?> <?= ui_text('frontend/account.titles.edit', [], false); ?>
                        </h1>
                    </div>
                    <div id="edit-account-container">
                        <?= $this->include('frontend/account/partials/edit/editPartial'); ?>
                    </div>
                    <div class="d-flex flex-wrap justify-content-end gap-2 mt-3">
                        <form <?= ui_binding('frontend/account.messages.areYouSureRefreshData', 'data-message'); ?> id="edit-refresh" data-message="<?= esc(lang('frontend/account.messages.areYouSureRefreshData'), 'attr'); ?>">
                            <input type="hidden" name="action" value="refresh">
                            <button type="submit" class="btn app-btn-warning btn-sm">
                                <i class="fa-solid fa-arrows-rotate"></i> <?= ui_text('frontend/account.buttons.refreshData', [], false); ?>
                            </button>
                        </form>
                        <button type="submit" form="account-edit" class="btn app-btn-success btn-sm">
                            <i class="fa-solid fa-floppy-disk"></i> <?= ui_text('frontend/account.buttons.sendData', [], false); ?>
                        </button>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <?= $this->include('frontend/template/scriptsPartial'); ?>
</body>

</html>
