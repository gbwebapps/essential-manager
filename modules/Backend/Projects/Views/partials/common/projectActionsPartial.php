<style>
    .projects-related-modal .modal-dialog { max-width: min(56rem, calc(100% - 2rem)) !important; }
    .projects-related-modal .modal-content { color: var(--app-text); background: var(--app-surface); border: 1px solid var(--app-border); border-radius: .75rem; }
    body:has(.projects-related-modal.show) .scrollup { display: none; }
</style>
<?php foreach (['task', 'note'] as $projectAction): ?>
    <div class="modal fade transfer-modal projects-related-modal" id="projects-add-<?= $projectAction; ?>-modal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="projects-add-<?= $projectAction; ?>-title" aria-hidden="true" data-project-action-modal data-save-label="<?= esc(lang('backend/projects.buttons.sendData'), 'attr'); ?>" data-project-action-kind="<?= $projectAction; ?>" data-draft-mode="<?= ($draftMode ?? false) ? 'true' : 'false'; ?>" data-add-title="<?= esc(lang('backend/' . ($projectAction === 'task' ? 'tasks' : 'notes') . '.titles.add'), 'attr'); ?>" data-edit-title="<?= esc(lang('backend/' . ($projectAction === 'task' ? 'tasks' : 'notes') . '.titles.edit'), 'attr'); ?>" data-project-uuid="<?= esc($project->uuid ?? '', 'attr'); ?>" data-project-name="<?= esc($project->name ?? '', 'attr'); ?>" data-no-changes="<?= esc(lang('backend/projects.messages.noDataChanged'), 'attr'); ?>" data-save-error="<?= esc(lang('backend/projects.messages.saveError'), 'attr'); ?>" data-save-notice="<?= esc(lang('backend/projects.messages.saveNotice'), 'attr'); ?>">
        <div class="modal-dialog modal-dialog-centered transfer-export-dialog">
            <div class="modal-content shadow">
                <form novalidate method="post" data-project-action-form>
                    <div class="modal-header">
                        <div>
                            <h2 class="modal-title" id="projects-add-<?= $projectAction; ?>-title"><?= ui_text('backend/projects.actions.' . ($projectAction === 'task' ? 'addTask' : 'addNote'), [], false); ?></h2>
                            <p class="transfer-intro mb-0" data-project-action-feedback role="status"><?= ui_text(($draftMode ?? false) ? 'backend/projects.draft.notice' : 'backend/projects.messages.saveNotice', [], false); ?></p>
                        </div>
                        <button type="button" class="btn app-btn-danger btn-sm transfer-modal-close" data-bs-dismiss="modal">
                            <i class="fa-solid fa-xmark me-2" aria-hidden="true"></i><?= ui_text('backend/projects.buttons.close', [], false); ?>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="uuid" value="">
                        <input type="hidden" name="project_uuid" value="<?= esc($project->uuid ?? '', 'attr'); ?>">
                        <div class="mb-3">
                            <div class="form-label"><?= ui_text('backend/projects.labels.name', [], false); ?></div>
                            <p id="projects-<?= $projectAction; ?>-project" class="fw-bold text-break mb-0" data-project-action-name><?= esc($project->name ?? ''); ?></p>
                        </div>
                        <?php if ($projectAction === 'task'): ?>
                            <?= view(\Modules\Backend\Tasks\Config\Views::FORM_FIELDS_PARTIAL, ['fieldPrefix' => 'projects-task', 'task' => null], ['saveData' => false]); ?>
                        <?php else: ?>
                            <?= view(\Modules\Backend\Notes\Config\Views::FORM_FIELDS_PARTIAL, ['fieldPrefix' => 'projects-note', 'note' => null], ['saveData' => false]); ?>
                        <?php endif; ?>
                        <div class="app-text-danger fw-bold small" data-work-form-errors role="status"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn app-btn-primary"><i class="fa-solid fa-floppy-disk me-1" aria-hidden="true"></i><span data-project-action-submit-label><?= ui_text(($draftMode ?? false) ? 'backend/projects.draft.addButton' : 'backend/projects.buttons.sendData', [], false); ?></span></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; ?>
