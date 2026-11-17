<!-- Modale condiviso per importazioni CSV e pacchetti XLSX. -->
<div class="modal fade transfer-modal" id="importModal" tabindex="-1" aria-labelledby="import-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered transfer-import-dialog">
        <div class="modal-content shadow">

            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="import-modal-title">
                        <i class="fa-solid fa-file-import me-2" aria-hidden="true"></i><?= isset($modalTitle) ? ui_value($modalTitle) : ui_text('backend/components/import.panels.main', [esc($entity)]); ?>
                    </h5>
                    <p class="transfer-intro mb-0"><?= ui_text('backend/components/import.labels.workflowHelp', [], false); ?></p>
                </div>
                <button <?= ui_binding('backend/components/import.buttons.close', 'aria-label', [], true); ?> type="button" id="btnCancelImportTop" class="btn app-btn-danger btn-sm transfer-modal-close" data-bs-dismiss="modal" aria-label="<?= esc(lang('backend/components/import.buttons.close'), 'attr'); ?>">
                    <i class="fa-solid fa-xmark me-2" aria-hidden="true"></i><?= ui_text('backend/components/import.buttons.close', [], false); ?>
                </button>
            </div>

            <div class="modal-body">
                <div id="import-alert-container"></div>
                <div id="import-content-area">
                    <?php if (isset($initialOutput)): ?>
                        <?= $initialOutput; ?>
                    <?php else: ?>
                    <form id="importForm" enctype="multipart/form-data">
                        <input type="hidden" name="entity" id="importEntity" value="<?= esc($entity); ?>">

                        <section class="transfer-section">
                            <div class="transfer-upload-heading">
                                <label for="csvFile" class="form-label fw-bold required-field mb-0"><?= ui_value($uploadLabel ?? lang('backend/components/import.labels.uploadCsv')); ?></label>
                                <a class="btn app-btn-secondary btn-sm" href="<?= esc($downloadUrl, 'attr'); ?>">
                                    <i class="fa-solid fa-download me-2" aria-hidden="true"></i><?= isset($templateLabel) ? ui_value($templateLabel) : ui_text('backend/components/import.links.downloadTemplate', [esc($entity)]); ?>
                                </a>
                            </div>
                            <input class="form-control" type="file" id="csvFile" name="<?= esc($uploadName ?? 'csvFile', 'attr'); ?>" accept="<?= esc($uploadAccept ?? '.csv,.txt,text/csv,text/plain', 'attr'); ?>" aria-required="true">
                        </section>

                        <?php if (isset($uploadHelp)): ?>
                        <details class="transfer-help">
                            <summary><i class="fa-solid fa-circle-info me-2" aria-hidden="true"></i><?= ui_text('backend/components/import.labels.importGuidance', [], false); ?></summary>
                            <p class="mb-0" <?= isset($uploadHelpParts) ? ui_parts($uploadHelpParts, 'text') : ui_value_binding($uploadHelp); ?>><?= esc($uploadHelp); ?></p>
                        </details>
                        <?php endif; ?>

                        <?= view('backend/components/import/structuresPartial', ['importStructures' => $structures ?? [$entity => $structure]]); ?>
                    </form>
                    <?php endif; ?>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" id="btnCancelImport" class="btn app-btn-danger btn-sm" data-bs-dismiss="modal">
                    <i class="fa-solid fa-xmark me-2" aria-hidden="true"></i><span <?= ui_binding('backend/components/import.buttons.close', 'data-close', [], true); ?> <?= ui_binding('backend/components/import.buttons.cancel', 'data-cancel', [], true); ?> data-import-close-label data-close="<?= esc(lang('backend/components/import.buttons.close'), 'attr'); ?>" data-cancel="<?= esc(lang('backend/components/import.buttons.cancel'), 'attr'); ?>"><?= ui_text('backend/components/import.buttons.close', [], false); ?></span>
                </button>
                <button type="submit" class="btn app-btn-success btn-sm" form="importForm">
                    <i class="fa-solid fa-file-import me-2" aria-hidden="true"></i><span <?= ui_binding('backend/components/import.buttons.analyzeFile', 'data-analyze', [], true); ?> <?= ui_binding('backend/components/import.buttons.confirmImport', 'data-confirm', [], true); ?> data-import-submit-label data-analyze="<?= esc(lang('backend/components/import.buttons.analyzeFile'), 'attr'); ?>" data-confirm="<?= esc(lang('backend/components/import.buttons.confirmImport'), 'attr'); ?>"><?= ui_text(isset($initialOutput) ? 'backend/components/import.buttons.confirmImport' : 'backend/components/import.buttons.analyzeFile', [], false); ?></span>
                </button>
            </div>
        </div>
    </div>
</div>
