<?= $this->extend(\Modules\Backend\Template\Config\Views::MAIN_VIEW) ?>

<?= $this->section('content') ?>

    <div class="row">
        <div class="col-12">
            <div id="index-operations-container">
                <?= $this->include(\Modules\Backend\Operations\Config\Views::INDEX_PARTIAL); ?>
            </div>
        </div>
    </div>

<?= $this->endSection() ?>
