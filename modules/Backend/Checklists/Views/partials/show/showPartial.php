<div class="row">
    <div class="col-12 col-lg-8 offset-lg-2">
        <div class="card" id="show-wrapper">

            <!-- General Data -->
            <div class="card-header rounded-0 d-flex justify-content-between align-items-center">
                <h2 class="card-title text-start mb-0"><?= ui_text('backend/checklists.panels.generalData', [], false); ?></h2>
                <form novalidate id="getGeneralData">
                    <input type="hidden" name="uuid" value="<?= esc($checklist->uuid, 'attr'); ?>">
                    <input type="hidden" name="context" value="show">
                    <button type="submit" class="btn btn-sm app-btn-secondary">
                        <i class="fa-solid fa-arrows-rotate me-1"></i><?= ui_text('backend/checklists.buttons.reload', [], false); ?>
                    </button>
                </form>
            </div>
            <div id="generalData">
                <?= $this->include(\Modules\Backend\Checklists\Config\Views::SHOW_GENERAL_DATA_PARTIAL, $this->data); ?>
            </div>
            <!-- End General Data -->

            <!-- Meta Data -->
            <div class="card-header rounded-0 d-flex justify-content-between align-items-center" style="border-top: var(--bs-card-border-width) solid var(--bs-card-border-color);">
                <h2 class="card-title text-start mb-0"><?= ui_text('backend/checklists.panels.metaData', [], false); ?></h2>
                <form novalidate id="getMetaData">
                    <input type="hidden" name="uuid" value="<?= esc($checklist->uuid, 'attr'); ?>">
                    <button type="submit" class="btn btn-sm app-btn-secondary">
                        <i class="fa-solid fa-arrows-rotate me-1"></i><?= ui_text('backend/checklists.buttons.reload', [], false); ?>
                    </button>
                </form>
            </div>
            <div id="metaData">
                <?= $this->include(\Modules\Backend\Checklists\Config\Views::META_DATA_PARTIAL, $this->data); ?>
            </div>
            <!-- End Meta Data -->


        </div>
    </div>
</div>
