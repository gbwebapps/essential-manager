<?= view(\Modules\Backend\Template\Config\Views::MODAL_STYLE_PARTIAL, [], ['saveData' => false]); ?>
<?= view(\Modules\Backend\Tasks\Config\Views::MODAL_EDITOR_STYLE_PARTIAL, [], ['saveData' => false]); ?>
<?= view(\Modules\Backend\Tasks\Config\Views::STEPS_STYLE_PARTIAL, [], ['saveData' => false]); ?>
<?= view(\Modules\Backend\Tasks\Config\Views::SECTION_STYLE_PARTIAL, [], ['saveData' => false]); ?>
<?= view(\Modules\Backend\Tasks\Config\Views::ACTION_LINKS_STYLE_PARTIAL, [], ['saveData' => false]); ?>
<?php foreach (['task', 'note'] as $applicationAction): ?>
    <div class="modal fade transfer-modal manager-work-modal applications-related-modal <?= $applicationAction === 'task' ? 'task-modal-complete' : ''; ?>" id="applications-add-<?= $applicationAction; ?>-modal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="applications-add-<?= $applicationAction; ?>-title" aria-hidden="true" data-application-action-modal data-save-label="<?= esc(lang('backend/applications.buttons.sendData'), 'attr'); ?>" data-application-action-kind="<?= $applicationAction; ?>" data-draft-mode="<?= ($draftMode ?? false) ? 'true' : 'false'; ?>" data-add-title="<?= esc(lang('backend/' . ($applicationAction === 'task' ? 'tasks' : 'notes') . '.titles.add'), 'attr'); ?>" data-edit-title="<?= esc(lang('backend/' . ($applicationAction === 'task' ? 'tasks' : 'notes') . '.titles.edit'), 'attr'); ?>" data-application-uuid="<?= esc($application->uuid ?? '', 'attr'); ?>" data-application-name="<?= esc($application->name ?? '', 'attr'); ?>" data-no-changes="<?= esc(lang('backend/applications.messages.noDataChanged'), 'attr'); ?>" data-save-error="<?= esc(lang('backend/applications.messages.saveError'), 'attr'); ?>" data-save-notice="<?= esc(lang('backend/applications.messages.saveNotice'), 'attr'); ?>">
        <div class="modal-dialog modal-dialog-centered transfer-export-dialog">
            <div class="modal-content shadow">
                <form novalidate method="post" data-application-action-form>
                    <div class="modal-header">
                        <div>
                            <h2 class="modal-title" id="applications-add-<?= $applicationAction; ?>-title"><?= ui_text('backend/applications.actions.' . ($applicationAction === 'task' ? 'addTask' : 'addNote'), [], false); ?></h2>
                            <p class="transfer-intro mb-0" data-application-action-feedback role="status"><?= ui_text(($draftMode ?? false) ? 'backend/applications.draft.notice' : 'backend/applications.messages.saveNotice', [], false); ?></p>
                        </div>
                        <button type="button" class="btn app-btn-danger btn-sm transfer-modal-close" data-bs-dismiss="modal">
                            <i class="fa-solid fa-xmark me-2" aria-hidden="true"></i><?= ui_text('backend/applications.buttons.close', [], false); ?>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="uuid" value="">
                        <input type="hidden" name="application_uuid" value="<?= esc($application->uuid ?? '', 'attr'); ?>">
                        <?php if ($applicationAction !== 'task'): ?>
                        <div class="mb-3">
                            <div class="form-label"><?= ui_text('backend/applications.labels.name', [], false); ?></div>
                            <p id="applications-<?= $applicationAction; ?>-application" class="fw-bold text-break mb-0" data-application-action-name><?= esc($application->name ?? ''); ?></p>
                        </div>
                        <?php endif; ?>
                        <?php if ($applicationAction === 'task'): ?>
                            <?= view(\Modules\Backend\Tasks\Config\Views::FORM_FIELDS_PARTIAL, ['fieldPrefix' => 'applications-task', 'task' => null, 'taskOwnerName' => $application->name ?? '', 'currentAdmin' => $currentAdmin ?? null], ['saveData' => false]); ?>
                        <?php else: ?>
                            <?= view(\Modules\Backend\Notes\Config\Views::FORM_FIELDS_PARTIAL, ['fieldPrefix' => 'applications-note', 'note' => null], ['saveData' => false]); ?>
                        <?php endif; ?>
                        <div class="app-text-danger fw-bold small" data-work-form-errors role="status"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm app-btn-secondary" data-modal-reset><i class="fa-solid fa-arrow-rotate-left me-1" aria-hidden="true"></i><?= ui_text('backend/applications.buttons.resetData', [], false); ?></button>
                        <button type="submit" class="btn btn-sm app-btn-success"><i class="fa-solid fa-floppy-disk me-1" aria-hidden="true"></i><span data-application-action-submit-label><?= ui_text(($draftMode ?? false) ? 'backend/applications.draft.addButton' : 'backend/applications.buttons.sendData', [], false); ?></span></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>
