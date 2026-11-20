<div class="row">
    <div class="col-12 col-lg-8 offset-lg-2">
        <div class="card" id="show-wrapper">

            <!-- General Data -->
            <div class="card-header rounded-0 d-flex justify-content-between align-items-center">
                <h2 class="card-title text-start mb-0"><?= ui_text('backend/projects.panels.generalData', [], false); ?></h2>
                <form novalidate id="getGeneralData">
                    <input type="hidden" name="uuid" value="<?= esc($project->uuid, 'attr'); ?>">
                    <input type="hidden" name="context" value="show">
                    <button type="submit" class="btn btn-sm app-btn-secondary">
                        <i class="fa-solid fa-arrows-rotate me-1"></i><?= ui_text('backend/projects.buttons.reload', [], false); ?>
                    </button>
                </form>
            </div>
            <div id="generalData">
                <?= $this->include(\Modules\Backend\Projects\Config\Views::SHOW_GENERAL_DATA_PARTIAL, $this->data); ?>
            </div>
            <!-- End General Data -->

            <!-- Meta Data -->
            <div class="card-header rounded-0 d-flex justify-content-between align-items-center" style="border-top: var(--bs-card-border-width) solid var(--bs-card-border-color);">
                <h2 class="card-title text-start mb-0"><?= ui_text('backend/projects.panels.metaData', [], false); ?></h2>
                <form novalidate id="getMetaData">
                    <input type="hidden" name="uuid" value="<?= esc($project->uuid, 'attr'); ?>">
                    <button type="submit" class="btn btn-sm app-btn-secondary">
                        <i class="fa-solid fa-arrows-rotate me-1"></i><?= ui_text('backend/projects.buttons.reload', [], false); ?>
                    </button>
                </form>
            </div>
            <div id="metaData">
                <?= $this->include(\Modules\Backend\Projects\Config\Views::META_DATA_PARTIAL, $this->data); ?>
            </div>
            <!-- End Meta Data -->

            <?= $this->include(\Modules\Backend\Projects\Config\Views::APPLICATIONS_PARTIAL); ?>
            <?= $this->include(\Modules\Backend\Projects\Config\Views::RELATED_PANELS_PARTIAL); ?>

        </div>
    </div>
</div>
