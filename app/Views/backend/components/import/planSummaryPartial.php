<?php if ($plan['insert'] === 0 && $plan['update'] === 0): ?>
<div class="alert app-alert-success d-flex align-items-center mb-3">
    <i class="fa-solid fa-check-circle fa-2x me-3" aria-hidden="true"></i>
    <div>
        <strong><?= ui_text('backend/components/import.labels.perfectSync', [], false); ?></strong><br>
        <?= ui_value_format($syncNoChanges ?? lang('backend/components/import.labels.syncNoChanges'), [$plan['skip']]); ?>
    </div>
</div>
<?php else: ?>
<p class="transfer-intro"><?= ui_text('backend/components/import.labels.previewHelp', [], false); ?></p>
<?php endif; ?>
<div class="transfer-plan" role="group" aria-label="<?= esc(lang('backend/components/import.labels.previewSummary'), 'attr'); ?>" <?= ui_binding('backend/components/import.labels.previewSummary', 'aria-label', [], true); ?>>
    <div class="transfer-stat"><span><i class="fa-solid fa-circle-plus me-2" aria-hidden="true"></i><?= ui_text('backend/components/import.labels.newRecords', [], false); ?></span><strong><?= esc((string) $plan['insert']); ?></strong></div>
    <div class="transfer-stat"><span><i class="fa-solid fa-pen-to-square me-2" aria-hidden="true"></i><?= ui_text('backend/components/import.labels.updatedRecords', [], false); ?></span><strong><?= esc((string) $plan['update']); ?></strong></div>
    <div class="transfer-stat"><span><i class="fa-solid fa-equals me-2" aria-hidden="true"></i><?= ui_text('backend/components/import.labels.unchangedRecords', [], false); ?></span><strong><?= esc((string) $plan['skip']); ?></strong></div>
</div>
