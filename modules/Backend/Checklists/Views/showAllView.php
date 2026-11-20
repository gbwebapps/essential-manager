<?= $this->extend(\Modules\Backend\Template\Config\Views::MAIN_VIEW) ?>

<?= $this->section('content') ?>

    <div class="row">
        <div class="col-12">
            <div id="showAll-checklists-container">
                <?= $this->include(\Modules\Backend\Checklists\Config\Views::SHOW_ALL_PARTIAL); ?>
            </div>
        </div>
    </div>

<?= $this->endSection() ?>
