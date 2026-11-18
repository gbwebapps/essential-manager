<!-- Struttura principale del form di aggiornamento (Partial) -->

<!-- Form per aggiornamento dati generali -->
<form id="getGeneralData"></form>

<!-- Form per aggiornamento metadati -->
<form id="getMetaData"></form>

<div class="row">
    <div class="col-12 col-lg-8 offset-lg-2">

        <div class="card">

            <!-- Form identificato per la gestione AJAX -->
            <form id="projects-edit" data-unsaved-check>

                <!-- General Data -->
                <div class="card-header rounded-0 d-flex justify-content-between align-items-center">
                    <h2 class="card-title text-start mb-0"><?= ui_text('backend/projects.panels.generalData', [], false); ?></h2>
                    <input type="hidden" name="uuid" value="<?= esc($project->uuid, 'attr'); ?>" form="getGeneralData">
                    <input type="hidden" name="context" value="edit" form="getGeneralData">
                    <button type="submit" class="btn btn-sm app-btn-secondary" form="getGeneralData">
                        <i class="fa-solid fa-arrows-rotate me-1"></i><?= ui_text('backend/projects.buttons.reload', [], false); ?>
                    </button>
                </div>
                <div id="generalData">
                    <?= $this->include(\Modules\Backend\Projects\Config\Views::EDIT_GENERAL_DATA_PARTIAL, $this->data); ?>
                </div>
                <!-- End General Data -->

                <!-- Meta Data -->
                <div class="card-header rounded-0 d-flex justify-content-between align-items-center" style="border-top: var(--bs-card-border-width) solid var(--bs-card-border-color);">
                    <h2 class="card-title text-start mb-0"><?= ui_text('backend/projects.panels.metaData', [], false); ?></h2>
                    <input type="hidden" name="uuid" value="<?= esc($project->uuid, 'attr'); ?>" form="getMetaData">
                    <button type="submit" class="btn btn-sm app-btn-secondary" form="getMetaData">
                        <i class="fa-solid fa-arrows-rotate me-1"></i><?= ui_text('backend/projects.buttons.reload', [], false); ?>
                    </button>
                </div>
                <div id="metaData">
                    <?= $this->include(\Modules\Backend\Projects\Config\Views::META_DATA_PARTIAL, $this->data); ?>
                </div>
                <!-- End Meta Data -->

                <!-- UUID -->
                <input type="hidden" name="uuid" id="uuid" value="<?= esc($project->uuid, 'attr'); ?>">

            </form>

        </div>

    </div>
</div>