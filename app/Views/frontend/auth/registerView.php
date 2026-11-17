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
            <div class="frontend-auth-content mx-auto w-100 text-start">
                <h1 class="h4 mb-4 text-center"><?= ui_text('frontend/auth.titles.' . $action, [], true); ?></h1>

                <form id="register_form" method="post" action="<?= base_url('auth/register'); ?>" novalidate>
                    <?php foreach (['firstname', 'lastname', 'email', 'phone'] as $field): ?>
                        <div class="mb-3">
                            <label for="<?= esc($field, 'attr'); ?>" class="form-label required-field">
                                <?= ui_text('frontend/account.labels.' . $field, [], true); ?>
                            </label>
                            <input type="<?= $field === 'email' ? 'email' : ($field === 'phone' ? 'tel' : 'text'); ?>"
                                   name="<?= esc($field, 'attr'); ?>"
                                   id="<?= esc($field, 'attr'); ?>"
                                   class="form-control"
                                   required
                                   aria-required="true"
                                   maxlength="<?= ['firstname' => 30, 'lastname' => 30, 'email' => 255, 'phone' => 16][$field]; ?>"
                                   <?= ui_binding('frontend/account.placeholders.' . $field, 'placeholder'); ?> placeholder="<?= esc(lang('frontend/account.placeholders.' . $field), 'attr'); ?>"
                                   autocomplete="<?= ['firstname' => 'given-name', 'lastname' => 'family-name', 'email' => 'email', 'phone' => 'tel'][$field]; ?>">
                            <div class="error_<?= esc($field, 'attr'); ?> app-text-danger small fw-bold pt-1" aria-live="polite">&nbsp;</div>
                        </div>
                    <?php endforeach; ?>

                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div class="d-flex flex-wrap gap-3">
                            <a href="<?= base_url('auth/login'); ?>"><i class="fa-solid fa-right-to-bracket"></i> <?= ui_text('frontend/auth.titles.login', [], true); ?></a>
                            <a href="<?= base_url('auth/resetPassword'); ?>"><i class="fa-solid fa-unlock"></i> <?= ui_text('frontend/auth.links.resetPassword', [], true); ?></a>
                        </div>
                        <button type="submit" class="btn btn-sm app-btn-secondary">
                            <i class="fa-solid fa-user-plus"></i> <?= ui_text('frontend/auth.buttons.register', [], true); ?>
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <?= $this->include('frontend/template/scriptsPartial'); ?>
</body>

</html>
