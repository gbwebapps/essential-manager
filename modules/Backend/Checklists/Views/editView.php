<?= $this->extend('backend/template/mainView') ?>

<?= $this->section('content') ?>

    <div class="row">
        <div class="col-12">
            <div id="edit-checklists-container">
                <?= $this->include(\Modules\Backend\Checklists\Config\Views::EDIT_PARTIAL); ?>
            </div>
        </div>
    </div>

<?= $this->endSection() ?>
