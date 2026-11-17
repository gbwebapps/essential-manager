<div class="transfer-progress">
    <div class="spinner-border app-text-success mb-3" role="status" style="width: 3rem; height: 3rem;"></div>
    <h5 class="fw-bold app-text"><?= ui_text('backend/components/import.labels.importingInProgress', [], false); ?></h5>
    <p class="fw-bold app-text-success mb-0" id="import-progress-text" role="status"><?= ui_value($progressMessage ?? lang('backend/components/import.labels.preparingData')); ?></p>
</div>
