<!-- Form per la conferma finale, condiviso anche dai fogli dei pacchetti XLSX. -->
<?php if (empty($embedded)): ?>
<form id="importForm">
    <input type="hidden" name="importId" value="<?= esc($importId ?? ''); ?>">
    <input type="hidden" name="step" value="confirm">
    <?= view('backend/components/import/planSummaryPartial', ['plan' => $plan, 'syncNoChanges' => $syncNoChanges ?? lang('backend/components/import.labels.syncNoChanges')]); ?>
<?php endif; ?>

    <table class="table table-sm table-striped table-bordered align-middle transfer-data-table mb-0">
        <thead>
            <tr>
                <?php foreach ($headers as $header): ?>
                    <th scope="col"><?= esc($header); ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php if ( ! empty($rows)): ?>
                <?php foreach ($rows as $rowItem): ?>
                    <?php
                        $record = $rowItem['record'];
                        $action = $rowItem['action'];
                        $changed = $rowItem['changed'];
                        $rowClass = ($action === 'insert') ? 'app-table-success' : '';
                    ?>
                    <tr class="<?= $rowClass; ?>">
                        <?php foreach ($headers as $header): ?>
                            <?php
                                $cellValue = $record[$header] ?? '';
                                $isChanged = in_array($header, $changed, true);
                                $cellClass = $isChanged ? 'app-table-warning fw-bold' : '';
                            ?>
                            <td class="<?= $cellClass; ?>" data-column="<?= esc($header, 'attr'); ?>" <?= $isChanged ? 'title="' . esc(lang('backend/components/import.labels.changedValue'), 'attr') . '"' : ''; ?> <?= $isChanged ? ui_binding('backend/components/import.labels.changedValue', 'title', [], true) : ''; ?>>
                                <span class="transfer-cell-value"><?= esc((string) $cellValue); ?></span>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="<?= count($headers); ?>" class="text-center p-3 transfer-empty-preview"><?= ui_text('backend/components/import.messages.noDataToPreview', [], false); ?></td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php
        $totalToProcess = $plan['insert'] + $plan['update'];
        $previewCount = count($rows);
    ?>
    <?php if ($totalToProcess > $previewCount): ?>
        <p class="transfer-preview-notice mb-0"><i class="fa-solid fa-circle-info me-2" aria-hidden="true"></i><?= ui_text('backend/components/import.labels.previewNotice', [$previewCount, $totalToProcess - $previewCount], false); ?></p>
    <?php endif; ?>

<?php if (empty($embedded)): ?>
    <?php if ($totalToProcess > 0 && \App\Libraries\Backend\CurrentPasswordGuard::required()): ?>
    <section class="transfer-authorization">
        <?= view('backend/components/currentPasswordPartial', ['passwordFieldId' => 'import-current-password', 'passwordColumnClass' => 'col-12 col-md-8 col-lg-6 mx-auto']); ?>
    </section>
    <?php endif; ?>
</form>
<?php endif; ?>
