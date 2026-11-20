<?= $this->extend(\Modules\Backend\Template\Config\Views::MAIN_VIEW) ?>

<?= $this->section('content') ?>

    <div class="row">
        <div class="col-12">
            <div id="edit-notes-container">
                <?= $this->include(\Modules\Backend\Notes\Config\Views::EDIT_PARTIAL); ?>
            </div>
        </div>
    </div>

<?= $this->endSection() ?>
