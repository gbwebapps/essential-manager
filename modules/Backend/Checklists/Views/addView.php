<?= $this->extend(\Modules\Backend\Template\Config\Views::MAIN_VIEW) ?>

<?= $this->section('content') ?>

    <?= $this->include(\Modules\Backend\Tasks\Config\Views::STEPS_STYLE_PARTIAL); ?>

    <div class="row">
        <div class="col-12">
            <div id="add-checklists-container">
                <?= $this->include(\Modules\Backend\Checklists\Config\Views::ADD_PARTIAL); ?>
            </div>
        </div>
    </div>


<?= $this->endSection() ?>
