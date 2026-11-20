<?= $this->extend(\Modules\Backend\Template\Config\Views::MAIN_VIEW) ?>

<?= $this->section('content') ?>

    <?= $this->include(\Modules\Backend\Tasks\Config\Views::ACTION_LINKS_STYLE_PARTIAL); ?>

    <div class="row">
        <div class="col-12">
            <div id="show-tasks-container">
                <?= $this->include(\Modules\Backend\Tasks\Config\Views::SHOW_PARTIAL); ?>
            </div>
        </div>
    </div>

    <?= $this->include('backend/template/exportPdfView', $this->data); ?>

    <?= $this->include(\Modules\Backend\Tasks\Config\Views::NOTE_MODAL_PARTIAL); ?>

<?= $this->endSection() ?>
