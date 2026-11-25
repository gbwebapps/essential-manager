<?= $this->extend(\Modules\Backend\Template\Config\Views::MAIN_VIEW) ?>

<?= $this->section('content') ?>


    <?= $this->include(\Modules\Backend\Tasks\Config\Views::STEPS_STYLE_PARTIAL); ?>

    <div class="row">
        <div class="col-12">
            <div id="show-checklists-container">
                <?= $this->include(\Modules\Backend\Checklists\Config\Views::SHOW_PARTIAL); ?>
            </div>
        </div>
    </div>

    <?= $this->include('backend/template/exportPdfView', $this->data); ?>


<?= $this->endSection() ?>
