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

                <div class="row">
                    <div class="col-12">
                        <div id="login-auth-container">
                            <?= $this->include('frontend/auth/partials/login/loginPartial'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <?= $this->include('frontend/template/scriptsPartial'); ?>
</body>

</html>
