<?= $this->extend('backend/template/mainView'); ?>

<?= $this->section('content'); ?>

<div class="row">
    <div class="col-12">
        <div id="show-messages-container">
            <?= $this->include('backend/messages/partials/show/showPartial'); ?>
        </div>
    </div>
</div>

<?php if (\App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, 'messages_export')): ?>
    <?= $this->include('backend/template/exportPdfView', $this->data); ?>
<?php endif; ?>

<?= $this->endSection(); ?>
