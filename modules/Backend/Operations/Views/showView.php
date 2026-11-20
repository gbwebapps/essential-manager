<?= $this->extend(\Modules\Backend\Template\Config\Views::MAIN_VIEW) ?>

<?= $this->section('content') ?>

    <div class="row">
        <div class="col-12">
            <div id="show-operations-container">
                <?= $this->include(\Modules\Backend\Operations\Config\Views::SHOW_PARTIAL); ?>
            </div>
        </div>
    </div>

<?= $this->endSection() ?>
