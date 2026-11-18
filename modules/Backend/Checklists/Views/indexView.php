<?= $this->extend('backend/template/mainView') ?>

<?= $this->section('content') ?>

    <div class="row">
        <div class="col-12">
            <div id="index-checklists-container">
                <?= $this->include(\Modules\Backend\Checklists\Config\Views::INDEX_PARTIAL); ?>
            </div>
        </div>
    </div>

<?= $this->endSection() ?>
