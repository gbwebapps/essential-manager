<?= $this->extend(\Modules\Backend\Template\Config\Views::MAIN_VIEW) ?>

<?= $this->section('content') ?>

    <div class="row">
        <div class="col-12">
            <div id="show-projects-container">
                <?= $this->include(\Modules\Backend\Projects\Config\Views::SHOW_PARTIAL); ?>
            </div>
        </div>
    </div>

    <?= $this->include('backend/template/exportPdfView', $this->data); ?>

    <?= $this->include(\Modules\Backend\Projects\Config\Views::PROJECT_ACTIONS_PARTIAL); ?>

<?= $this->endSection() ?>
