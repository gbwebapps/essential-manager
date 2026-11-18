<?= $this->extend('backend/template/mainView') ?>

<?= $this->section('content') ?>

    <div class="row">
        <div class="col-12">
            <div id="show-operations-container">
                <?= $this->include(\Modules\Backend\Operations\Config\Views::SHOW_PARTIAL); ?>
            </div>
        </div>
    </div>

<?= $this->endSection() ?>
