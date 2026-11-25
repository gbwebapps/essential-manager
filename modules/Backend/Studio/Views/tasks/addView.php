<?= $this->extend(\Modules\Backend\Template\Config\Views::MAIN_VIEW) ?>

<?= $this->section('content') ?>

    <?= $this->include(\Modules\Backend\Tasks\Config\Views::ACTION_LINKS_STYLE_PARTIAL); ?>
    <?= $this->include(\Modules\Backend\Tasks\Config\Views::SECTION_STYLE_PARTIAL); ?>
    <?= $this->include(\Modules\Backend\Tasks\Config\Views::STEPS_STYLE_PARTIAL); ?>
    <?= $this->include(\Modules\Backend\Tasks\Config\Views::MODAL_EDITOR_STYLE_PARTIAL); ?>

    <div class="row" data-task-route-base="backend/studio/tasks/">
        <div class="col-12">
            <div id="add-tasks-container">
                <?= $this->include(\Modules\Backend\Tasks\Config\Views::ADD_PARTIAL); ?>
            </div>
        </div>
    </div>

    <?= $this->include(\Modules\Backend\Tasks\Config\Views::NOTE_MODAL_PARTIAL); ?>

<?= $this->endSection() ?>
