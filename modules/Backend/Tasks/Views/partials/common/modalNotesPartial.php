<?php
    $canAddNotes = \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, 'notes_add');
    $canShowNotes = \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, 'notes_show');
?>
<section class="mt-3" data-task-modal-notes data-can-edit="<?= \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, 'notes_edit') ? 'true' : 'false'; ?>" data-can-delete="<?= \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, 'notes_delete') ? 'true' : 'false'; ?>" data-new-note="<?= esc(lang('backend/tasks.modal.newNote'), 'attr'); ?>">
    <?php if ($canAddNotes || $canShowNotes): ?>
        <?= view(\Modules\Backend\Tasks\Config\Views::MODAL_NOTE_GROUP_PARTIAL, ['noteGroupLabel' => lang('backend/tasks.related.notes'), 'canAddNotes' => $canAddNotes], ['saveData' => false]); ?>
        <p class="small app-text-muted mt-2 mb-0"><?= ui_text('backend/tasks.modal.draftNotice', [], false); ?></p>
    <?php endif; ?>
    <template data-modal-note-template>
        <div class="task-modal-note" data-modal-note-row>
            <details>
                <summary>
                    <span class="task-modal-note-title"><i class="fa-solid fa-chevron-right me-2" aria-hidden="true"></i><span data-modal-note-preview></span></span>
                    <span class="task-modal-note-actions">
                        <button type="button" class="btn btn-link btn-sm" data-modal-note-read><?= ui_text('backend/tasks.actions.readNote', [], false); ?></button>
                        <button type="button" class="btn btn-link btn-sm text-danger" data-modal-note-remove><i class="fa-solid fa-trash-can me-1" aria-hidden="true"></i><?= ui_text('backend/tasks.actions.hardDelete', [], false); ?></button>
                    </span>
                </summary>
                <div class="pt-2">
                    <label class="visually-hidden"><?= ui_text('backend/notes.labels.content', [], false); ?></label>
                    <textarea class="form-control" rows="3" placeholder="<?= esc(lang('backend/notes.labels.content'), 'attr'); ?>"></textarea>
                    <div class="app-text-danger fw-bold small" data-work-field-error aria-live="polite"></div>
                </div>
            </details>
        </div>
    </template>
</section>
