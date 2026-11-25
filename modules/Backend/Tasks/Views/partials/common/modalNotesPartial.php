<?php
    $canAddNotes = \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, 'notes_add');
    $canShowNotes = \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, 'notes_show');
?>
<section class="task-note-editor" data-task-modal-notes data-can-edit="<?= \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, 'notes_edit') ? 'true' : 'false'; ?>" data-can-delete="<?= \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, 'notes_delete') ? 'true' : 'false'; ?>" data-new-note="<?= esc(lang('backend/tasks.modal.newNote'), 'attr'); ?>">
    <?php if (($includeTaskNotes ?? true) && ($canAddNotes || $canShowNotes)): ?>
        <?= view(\Modules\Backend\Tasks\Config\Views::MODAL_NOTE_GROUP_PARTIAL, ['noteGroupForStep'=>false], ['saveData'=>false]); ?>
    <?php endif; ?>
    <template data-modal-note-template>
        <div class="task-modal-note task-related-item" data-modal-note-row>
            <div class="task-note-header d-flex align-items-center gap-1">
                    <button type="button" class="task-related-toggle task-modal-note-title" data-modal-note-toggle><i class="fa-solid fa-chevron-right me-2" aria-hidden="true"></i><span class="fw-bold text-truncate" data-modal-note-preview></span></button>
                    <span class="task-modal-note-actions">
                        <button type="button" class="btn btn-link btn-sm task-record-link" data-modal-note-read><?= ui_text('backend/tasks.actions.readNote', [], false); ?></button>
                        <button type="button" class="btn btn-link btn-sm task-record-link app-text-danger" data-modal-note-remove><i class="fa-solid fa-trash-can me-1" aria-hidden="true"></i><?= ui_text('backend/tasks.actions.hardDelete', [], false); ?></button>
                    </span>
            </div>
            <div class="collapse" data-note-collapse data-modal-note-panel>
                <div class="task-related-detail">
                    <label class="visually-hidden"><?= ui_text('backend/notes.labels.content', [], false); ?></label>
                    <textarea class="form-control" rows="3" placeholder="<?= esc(lang('backend/notes.labels.content'), 'attr'); ?>"></textarea>
                    <div class="app-text-danger fw-bold small" data-work-field-error aria-live="polite"></div>
                </div>
            </div>
        </div>
    </template>
</section>
