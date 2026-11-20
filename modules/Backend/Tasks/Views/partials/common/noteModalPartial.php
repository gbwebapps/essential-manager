<style>
    .tasks-related-modal .modal-dialog { max-width: min(56rem, calc(100% - 2rem)) !important; }
    .tasks-related-modal .modal-content { color: var(--app-text); background: var(--app-surface); border: 1px solid var(--app-border); border-radius: .75rem; }
    body:has(.tasks-related-modal.show) .scrollup { display: none; }
</style>
<div class="modal fade transfer-modal tasks-related-modal" id="tasks-note-modal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="tasks-note-title" aria-hidden="true" data-save-error="<?= esc(lang('backend/tasks.messages.saveError'), 'attr'); ?>">
    <div class="modal-dialog modal-dialog-centered transfer-export-dialog">
        <div class="modal-content shadow">
            <form novalidate method="post" id="tasks-note-form">
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title" id="tasks-note-title"><?= ui_text('backend/tasks.actions.addNote', [], false); ?></h2>
                        <p class="transfer-intro mb-0" data-note-feedback role="status" data-default-feedback="<?= esc(lang('backend/tasks.messages.noteSaveNotice'), 'attr'); ?>"><?= ui_text('backend/tasks.messages.noteSaveNotice', [], false); ?></p>
                    </div>
                    <button type="button" class="btn app-btn-danger btn-sm transfer-modal-close" data-bs-dismiss="modal">
                        <i class="fa-solid fa-xmark me-2" aria-hidden="true"></i><?= ui_text('backend/tasks.buttons.close', [], false); ?>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="uuid" value="">
                    <input type="hidden" name="task_uuid" value="<?= esc($task->uuid ?? '', 'attr'); ?>">
                    <input type="hidden" name="step_uuid" value="">
                    <div class="mb-3">
                        <div class="form-label"><?= ui_text('backend/tasks.labels.title', [], false); ?></div>
                        <p class="fw-bold text-break mb-0" data-note-task-title><?= esc($task->title ?? ''); ?></p>
                    </div>
                    <div class="mb-3" data-note-step-context hidden>
                        <div class="form-label"><?= ui_text('backend/tasks.labels.step', [], false); ?></div>
                        <p class="fw-bold text-break mb-0" data-note-step-title></p>
                    </div>
                    <?= view(\Modules\Backend\Notes\Config\Views::FORM_FIELDS_PARTIAL, ['fieldPrefix' => 'tasks-note', 'note' => null], ['saveData' => false]); ?>
                    <div class="app-text-danger fw-bold small" data-work-form-errors role="status"></div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn app-btn-primary"><i class="fa-solid fa-floppy-disk me-1" aria-hidden="true"></i><?= ui_text('backend/tasks.buttons.sendData', [], false); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<form novalidate id="tasks-delete-note" class="task-action" data-endpoint="deleteNote" data-confirm="<?= esc(lang('backend/tasks.messages.confirmDeleteNote'), 'attr'); ?>">
    <input type="hidden" name="task_uuid" value="<?= esc($task->uuid ?? '', 'attr'); ?>">
</form>
