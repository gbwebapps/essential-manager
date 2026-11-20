<?= $this->extend(\Modules\Backend\Template\Config\Views::MAIN_VIEW) ?>

<?= $this->section('content') ?>

    <div class="row">
        <div class="col-12">
            <div id="add-operations-container">
                <?= $this->include(\Modules\Backend\Operations\Config\Views::ADD_PARTIAL); ?>
            </div>
        </div>
    </div>

<?= $this->endSection() ?>
