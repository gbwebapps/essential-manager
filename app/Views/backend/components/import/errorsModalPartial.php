<?php
    $validationErrors = $validationErrors ?? [];
    $errorCount = count($validationErrors);
?>
<div class="alert app-alert-danger fade show" role="alert">
    <div class="d-flex align-items-center justify-content-between gap-3">
        <strong><i class="fa-solid fa-triangle-exclamation me-2" aria-hidden="true"></i><?= ui_text('backend/components/import.messages.validationErrors', [$errorCount], false); ?></strong>
        <button type="button" class="btn app-btn-danger btn-sm" data-bs-dismiss="alert"><i class="fa-solid fa-xmark me-2" aria-hidden="true"></i><?= ui_text('backend/components/import.buttons.remove', [], false); ?></button>
    </div>
    <details class="transfer-help" id="collapseValidationErrors" open>
        <summary><?= ui_text('backend/components/import.labels.validationDetails', [], false); ?></summary>
        <ul class="transfer-error-list">
            <?php foreach ($validationErrors as $error): ?>
                <li><?= esc($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </details>
</div>
