<?= $this->extend(\Modules\Backend\Template\Config\Views::MAIN_VIEW) ?>

<?= $this->section('content') ?>

    <div class="row">
        <div class="col-12">
            <div id="show-notes-container">
                <?= $this->include(\Modules\Backend\Notes\Config\Views::SHOW_PARTIAL); ?>
            </div>
        </div>
    </div>

<?= $this->endSection() ?>
