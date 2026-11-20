<style>
    .applications-related-modal .modal-dialog { max-width: min(56rem, calc(100% - 2rem)) !important; }
    .applications-related-modal .modal-content { color: var(--app-text); background: var(--app-surface); border: 1px solid var(--app-border); border-radius: .75rem; }
    body:has(.applications-related-modal.show) .scrollup { display: none; }
</style>
<?php foreach (['task', 'note'] as $applicationAction): ?>
    <div class="modal fade transfer-modal applications-related-modal" id="applications-add-<?= $applicationAction; ?>-modal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="applications-add-<?= $applicationAction; ?>-title" aria-hidden="true" data-application-action-modal data-save-label="<?= esc(lang('backend/applications.buttons.sendData'), 'attr'); ?>" data-application-action-kind="<?= $applicationAction; ?>" data-draft-mode="<?= ($draftMode ?? false) ? 'true' : 'false'; ?>" data-add-title="<?= esc(lang('backend/' . ($applicationAction === 'task' ? 'tasks' : 'notes') . '.titles.add'), 'attr'); ?>" data-edit-title="<?= esc(lang('backend/' . ($applicationAction === 'task' ? 'tasks' : 'notes') . '.titles.edit'), 'attr'); ?>" data-application-uuid="<?= esc($application->uuid ?? '', 'attr'); ?>" data-application-name="<?= esc($application->name ?? '', 'attr'); ?>" data-no-changes="<?= esc(lang('backend/applications.messages.noDataChanged'), 'attr'); ?>" data-save-error="<?= esc(lang('backend/applications.messages.saveError'), 'attr'); ?>" data-save-notice="<?= esc(lang('backend/applications.messages.saveNotice'), 'attr'); ?>">
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
                        <div class="mb-3">
                            <div class="form-label"><?= ui_text('backend/applications.labels.name', [], false); ?></div>
                            <p id="applications-<?= $applicationAction; ?>-application" class="fw-bold text-break mb-0" data-application-action-name><?= esc($application->name ?? ''); ?></p>
                        </div>
                        <?php if ($applicationAction === 'task'): ?>
                            <?= view(\Modules\Backend\Tasks\Config\Views::FORM_FIELDS_PARTIAL, ['fieldPrefix' => 'applications-task', 'task' => null], ['saveData' => false]); ?>
                        <?php else: ?>
                            <?= view(\Modules\Backend\Notes\Config\Views::FORM_FIELDS_PARTIAL, ['fieldPrefix' => 'applications-note', 'note' => null], ['saveData' => false]); ?>
                        <?php endif; ?>
                        <div class="app-text-danger fw-bold small" data-work-form-errors role="status"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn app-btn-primary"><i class="fa-solid fa-floppy-disk me-1" aria-hidden="true"></i><span data-application-action-submit-label><?= ui_text(($draftMode ?? false) ? 'backend/applications.draft.addButton' : 'backend/applications.buttons.sendData', [], false); ?></span></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>
