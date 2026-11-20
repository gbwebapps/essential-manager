<!-- Struttura principale del form di aggiornamento (Partial) -->

<!-- Form per aggiornamento dati generali -->
<form novalidate id="getGeneralData"></form>

<!-- Form per aggiornamento metadati -->
<form novalidate id="getMetaData"></form>

<div class="row">
    <div class="col-12 col-lg-8 offset-lg-2">

        <div class="card">

            <!-- Form identificato per la gestione AJAX -->
            <form novalidate id="tasks-edit" data-unsaved-check>

                <!-- General Data -->
                <div class="card-header rounded-0 d-flex justify-content-between align-items-center">
                    <h2 class="card-title text-start mb-0"><?= ui_text('backend/tasks.panels.generalData', [], false); ?></h2>
                    <input type="hidden" name="uuid" value="<?= esc($task->uuid, 'attr'); ?>" form="getGeneralData">
                    <input type="hidden" name="context" value="edit" form="getGeneralData">
                    <button type="submit" class="btn btn-sm app-btn-secondary" form="getGeneralData">
                        <i class="fa-solid fa-arrows-rotate me-1"></i><?= ui_text('backend/tasks.buttons.reload', [], false); ?>
                    </button>
                </div>
                <div id="generalData">
                    <?= $this->include(\Modules\Backend\Tasks\Config\Views::EDIT_GENERAL_DATA_PARTIAL, $this->data); ?>
                </div>
                <!-- End General Data -->



                <!-- UUID -->
                <input type="hidden" name="uuid" id="uuid" value="<?= esc($task->uuid, 'attr'); ?>">

            </form>

            <?= $this->include(\Modules\Backend\Tasks\Config\Views::NOTES_PANEL_PARTIAL); ?>

                <!-- Meta Data -->
                <div class="card-header rounded-0 d-flex justify-content-between align-items-center" style="border-top: var(--bs-card-border-width) solid var(--bs-card-border-color);">
                    <h2 class="card-title text-start mb-0"><?= ui_text('backend/tasks.panels.metaData', [], false); ?></h2>
                    <input type="hidden" name="uuid" value="<?= esc($task->uuid, 'attr'); ?>" form="getMetaData">
                    <button type="submit" class="btn btn-sm app-btn-secondary" form="getMetaData">
                        <i class="fa-solid fa-arrows-rotate me-1"></i><?= ui_text('backend/tasks.buttons.reload', [], false); ?>
                    </button>
                </div>
                <div id="metaData"><div id="task-meta-content">
                    <?= $this->include(\Modules\Backend\Tasks\Config\Views::META_DATA_PARTIAL, $this->data); ?>
                </div>
                </div><!-- End Meta Data -->

        </div>

    </div>
</div>