<?= $this->extend(\Modules\Backend\Template\Config\Views::MAIN_VIEW) ?>

<?= $this->section('content') ?>

    <div class="row">
        <div class="col-12">
            <div id="add-projects-container">
                <?= $this->include(\Modules\Backend\Projects\Config\Views::ADD_PARTIAL); ?>
            </div>
        </div>
    </div>

    <?= $this->include(\Modules\Backend\Projects\Config\Views::PROJECT_ACTIONS_PARTIAL); ?>

<?= $this->endSection() ?>
