<?= $this->extend(\Modules\Backend\Template\Config\Views::MAIN_VIEW) ?>

<?= $this->section('content') ?>

    <div class="row">
        <div class="col-12">
            <div id="add-applications-container">
                <?= $this->include(\Modules\Backend\Applications\Config\Views::ADD_PARTIAL); ?>
            </div>
        </div>
    </div>

    <?= $this->include(\Modules\Backend\Applications\Config\Views::PROJECT_ACTIONS_PARTIAL); ?>

<?= $this->endSection() ?>
