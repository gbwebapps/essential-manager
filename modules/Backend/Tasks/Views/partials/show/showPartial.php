<div class="row">
    <div class="col-12 col-lg-8 offset-lg-2">
        <div class="card" id="show-wrapper">

            <!-- General Data -->
            <div class="card-header rounded-0 d-flex justify-content-between align-items-center">
                <h2 class="card-title text-start mb-0"><?= ui_text('backend/tasks.panels.generalData', [], false); ?></h2>
                <form novalidate id="getGeneralData">
                    <input type="hidden" name="uuid" value="<?= esc($task->uuid, 'attr'); ?>">
                    <input type="hidden" name="context" value="show">
                    <button type="submit" class="btn btn-sm app-btn-secondary">
                        <i class="fa-solid fa-arrows-rotate me-1"></i><?= ui_text('backend/tasks.buttons.reload', [], false); ?>
                    </button>
                </form>
            </div>
            <div id="generalData">
                <?= $this->include(\Modules\Backend\Tasks\Config\Views::SHOW_GENERAL_DATA_PARTIAL, $this->data); ?>
            </div>
            <!-- End General Data -->



            <?= $this->include(\Modules\Backend\Tasks\Config\Views::NOTES_PANEL_PARTIAL); ?>

            <!-- Meta Data -->
            <div class="card-header rounded-0 d-flex justify-content-between align-items-center" style="border-top: var(--bs-card-border-width) solid var(--bs-card-border-color);">
                <h2 class="card-title text-start mb-0"><?= ui_text('backend/tasks.panels.metaData', [], false); ?></h2>
                <form novalidate id="getMetaData">
                    <input type="hidden" name="uuid" value="<?= esc($task->uuid, 'attr'); ?>">
                    <button type="submit" class="btn btn-sm app-btn-secondary">
                        <i class="fa-solid fa-arrows-rotate me-1"></i><?= ui_text('backend/tasks.buttons.reload', [], false); ?>
                    </button>
                </form>
            </div>
            <div id="metaData"><div id="task-meta-content">
                <?= $this->include(\Modules\Backend\Tasks\Config\Views::META_DATA_PARTIAL, $this->data); ?>
            </div>
            </div><!-- End Meta Data -->

        </div>
    </div>
</div>
