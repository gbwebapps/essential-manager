<section class="transfer-structures" aria-labelledby="import-structures-title">
    <h6 class="transfer-section-title" id="import-structures-title"><?= ui_text('backend/components/import.labels.fileStructure', [], false); ?></h6>
    <p class="transfer-key-legend">
        <span><i class="fa-solid fa-key app-text-warning me-1" aria-hidden="true"></i><?= ui_text('backend/components/import.labels.primaryKey', [], false); ?></span>
        <span><i class="fa-solid fa-key me-1" aria-hidden="true"></i><?= ui_text('backend/components/import.labels.index', [], false); ?></span>
    </p>
    <div class="transfer-accordion" data-import-accordion>
        <?php foreach ($importStructures as $tableName => $tableStructure): ?>
        <details class="transfer-disclosure" data-import-section>
            <summary>
                <span class="transfer-table-name"><i class="fa-solid fa-table me-2" aria-hidden="true"></i><?= esc($tableName); ?></span>
                <span class="transfer-summary-meta"><?= ui_text('backend/components/import.labels.columnCount', [count($tableStructure)], false); ?></span>
                <i class="fa-solid fa-chevron-right transfer-disclosure-icon" aria-hidden="true"></i>
            </summary>
            <div class="transfer-disclosure-body">
                <table class="table table-sm table-bordered align-middle transfer-structure-table mb-0">
                    <colgroup><col class="transfer-schema-name"><col class="transfer-schema-type"><col class="transfer-schema-length"><col class="transfer-schema-key"></colgroup>
                    <thead>
                        <tr>
                            <th scope="col"><?= ui_text('backend/components/import.labels.columnName', [], false); ?></th>
                            <th scope="col"><?= ui_text('backend/components/import.labels.dataType', [], false); ?></th>
                            <th scope="col"><?= ui_text('backend/components/import.labels.maxLength', [], false); ?></th>
                            <th scope="col"><?= ui_text('backend/components/import.labels.keysAndIndexes', [], false); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ( ! empty($tableStructure)): ?>
                            <?php foreach ($tableStructure as $field): ?>
                            <tr>
                                <th scope="row"><?= esc($field['name']); ?></th>
                                <td><?= esc(strtoupper($field['type'])); ?></td>
                                <td><?= esc($field['max_length'] ?? '-'); ?></td>
                                <td>
                                    <?php if ($field['primary_key'] === 1): ?>
                                        <i class="fa-solid fa-key app-text-warning me-1" aria-hidden="true"></i><?= ui_text('backend/components/import.labels.primaryKey', [], false); ?>
                                    <?php elseif ($field['is_index']): ?>
                                        <i class="fa-solid fa-key me-1" aria-hidden="true"></i><?= ui_text('backend/components/import.labels.index', [], false); ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center p-3"><?= ui_text('backend/components/import.messages.noStructure', [], false); ?></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </details>
        <?php endforeach; ?>
    </div>
</section>
