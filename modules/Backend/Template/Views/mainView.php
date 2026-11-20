<?= $this->extend('backend/template/mainView') ?>

<?= $this->section('afterNavbarTopView') ?>

    <?= $this->include(\Modules\Backend\Template\Config\Views::NAVIGATION_PARTIAL); ?>

<?= $this->endSection() ?>
