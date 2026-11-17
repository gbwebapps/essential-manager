<!-- Modale condiviso per esportazioni CSV e pacchetti XLSX. -->
<div class="modal fade transfer-modal" id="exportModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="export-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered transfer-export-dialog">
        <div class="modal-content shadow">

            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="export-modal-title">
                        <i class="fa-solid fa-file-export me-2" aria-hidden="true"></i><?= isset($modalTitle) ? ui_value($modalTitle) : ui_text('backend/components/export.panels.main', [esc($entity)]); ?>
                    </h5>
                    <p class="transfer-intro mb-0"><?= isset($fixedPackage) ? ui_value($packageDescription) : ui_text('backend/components/export.labels.selectColumns', [], false); ?></p>
                </div>
                <button <?= ui_binding('backend/global.buttons.close', 'aria-label', [], true); ?> type="button" class="btn app-btn-danger btn-sm transfer-modal-close" data-export-close="<?= esc($controlsPrefix ?? 'export', 'attr'); ?>" aria-label="<?= esc(lang('backend/global.buttons.close'), 'attr'); ?>">
                    <i class="fa-solid fa-xmark me-2" aria-hidden="true"></i><?= ui_text('backend/global.buttons.close', [], false); ?>
                </button>
            </div>

            <div class="modal-body">
                <div id="export-alert-container"></div>
                <div id="export-selection-area">
                    <section class="transfer-section">
                        <?php if (isset($fixedPackage)): ?>
                        <h6 class="transfer-section-title"><?= ui_text('backend/components/export.labels.includedTables', [], false); ?></h6>
                        <ul class="transfer-package-list">
                            <?php foreach ($fixedPackage as $table): ?>
                                <li><i class="fa-solid fa-table me-2" aria-hidden="true"></i><span><?= esc($table); ?></span></li>
                            <?php endforeach; ?>
                        </ul>
                        <?php else: ?>
                        <form id="export-columns-form">
                            <div class="form-check transfer-select-all">
                                <input class="form-check-input" type="checkbox" id="export-check-all" checked>
                                <label class="form-check-label fw-bold" for="export-check-all"><?= ui_text('backend/components/export.labels.selectDeselectAll', [], false); ?></label>
                            </div>
                            <div class="transfer-column-list">
                                <?php foreach ($columns as $column): ?>
                                    <?php $isRequired = in_array($column, $requiredColumns ?? [], true); ?>
                                    <div class="transfer-column-item">
                                        <div class="form-check mb-0">
                                            <input class="form-check-input export-col-cb" type="checkbox" name="selected_columns[]" value="<?= esc($column, 'attr'); ?>" id="col_<?= esc($column, 'attr'); ?>" <?= $isRequired ? 'data-required="1" aria-required="true" disabled' : ''; ?> checked>
                                            <label class="form-check-label<?= $isRequired ? ' fw-bold required-field' : ''; ?>" for="col_<?= esc($column, 'attr'); ?>"><?= esc($column); ?></label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </form>
                        <?php endif; ?>
                    </section>
                    <?php if (\App\Libraries\Backend\CurrentPasswordGuard::required()): ?>
                    <section class="transfer-authorization">
                        <?= view('backend/components/currentPasswordPartial', ['passwordFieldId' => 'export-current-password', 'passwordColumnClass' => 'col-12 col-md-8 col-lg-6 mx-auto']); ?>
                    </section>
                    <?php endif; ?>
                </div>

                <div id="export-spinner-area" class="d-none transfer-progress">
                    <div class="spinner-border app-text-success mb-3" role="status" style="width: 3rem; height: 3rem;"></div>
                    <h5 class="fw-bold app-text"><?= ui_text('backend/components/export.labels.runningExportation', [], false); ?></h5>
                    <p class="fw-bold app-text-success mb-0" id="export-progress-text" role="status"><?= ui_text('backend/components/export.labels.dataElaboration', [], false); ?></p>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-sm app-btn-danger no-general-disabled" id="<?= esc($controlsPrefix ?? 'export', 'attr'); ?>-cancel-btn">
                    <i class="fa-solid fa-xmark me-2" aria-hidden="true"></i><?= ui_text('backend/components/export.buttons.operationUndo', [], false); ?>
                </button>
                <button type="button" class="btn btn-sm app-btn-success" id="<?= esc($controlsPrefix ?? 'export', 'attr'); ?>-start-btn">
                    <i class="fa-solid fa-play me-2" aria-hidden="true"></i><?= ui_text('backend/components/export.buttons.exportationStart', [], false); ?>
                </button>
            </div>
        </div>
    </div>
</div>
