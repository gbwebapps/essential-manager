<?= $this->extend('backend/template/mainView') ?>

<?= $this->section('content') ?>

    <div class="row">
        <div class="col-12">
            <div id="show-users-container">
                <?= $this->include('backend/users/partials/show/showPartial'); ?>
            </div>
        </div>
    </div>

    <?php if (\App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, 'users_export')): ?>
        <!-- ######################### MODALE ESPORTAZIONE PDF ######################### -->
        <?= $this->include('backend/template/exportPdfView', $this->data); ?>
        <!-- ######################### FINE MODALE ESPORTAZIONE PDF ######################### -->
    <?php endif; ?>
    
<?= $this->endSection() ?>