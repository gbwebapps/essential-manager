<?= $this->extend('backend/template/mainView') ?>

<?= $this->section('content') ?>

    <?= $this->include(\Config\Backend\AdminTransfer::INDEX_PARTIAL); ?>

<?= $this->endSection() ?>
