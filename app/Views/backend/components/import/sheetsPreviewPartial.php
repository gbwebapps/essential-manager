<!-- Riepilogo e conferma comuni ai pacchetti con piu fogli. -->
<form id="importForm">
    <input type="hidden" name="importId" value="<?= esc($importId ?? '', 'attr'); ?>">
    <input type="hidden" name="step" value="confirm">

    <?= view('backend/components/import/planSummaryPartial', ['plan' => $plan, 'syncNoChanges' => $syncNoChanges]); ?>
    <div class="transfer-accordion" data-import-accordion>
        <?php foreach ($sheets as $tableName => $sheet): ?>
        <details class="transfer-disclosure" data-import-section>
            <summary>
                <span class="transfer-table-name"><i class="fa-solid fa-table me-2" aria-hidden="true"></i><?= esc($tableName); ?></span>
                <span class="transfer-sheet-counts">
                    <span><?= ui_text('backend/components/import.labels.newRecords', [], false); ?>: <strong><?= esc((string) $sheet['plan']['insert']); ?></strong></span>
                    <span><?= ui_text('backend/components/import.labels.updatedRecords', [], false); ?>: <strong><?= esc((string) $sheet['plan']['update']); ?></strong></span>
                    <span><?= ui_text('backend/components/import.labels.unchangedRecords', [], false); ?>: <strong><?= esc((string) $sheet['plan']['skip']); ?></strong></span>
                </span>
                <i class="fa-solid fa-chevron-right transfer-disclosure-icon" aria-hidden="true"></i>
            </summary>
            <div class="transfer-disclosure-body">
                <?= view('backend/components/import/previewModalPartial', $sheet + ['embedded' => true, 'syncNoChanges' => $syncNoChanges]); ?>
            </div>
        </details>
        <?php endforeach; ?>
    </div>

    <p class="transfer-preview-notice mb-0"><?= ui_text($reExportKey, [], true); ?></p>
    <?php if ($plan['insert'] + $plan['update'] > 0 && \App\Libraries\Backend\CurrentPasswordGuard::required()): ?>
    <section class="transfer-authorization">
        <?= view('backend/components/currentPasswordPartial', ['passwordFieldId' => $passwordFieldId, 'passwordColumnClass' => 'col-12 col-md-8 col-lg-6 mx-auto']); ?>
    </section>
    <?php endif; ?>
</form>
