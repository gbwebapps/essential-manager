<div class="d-flex flex-wrap align-items-center gap-2 mt-1" data-task-editor-actions>
    <?php if ($showAddStepAction ?? true): ?>
    <button type="button" class="btn btn-sm app-btn-secondary" data-task-step-add><i class="fa-solid fa-plus me-1" aria-hidden="true"></i><?= ui_text('backend/tasks.actions.addStep', [], false); ?></button>
    <?php endif; ?>
    <?php if (($showAddNoteAction ?? true) && \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, 'notes_add')): ?>
        <button type="button" class="btn btn-sm app-btn-secondary" <?= ($persistedNoteAction ?? false) ? 'data-bs-toggle="modal" data-bs-target="#tasks-note-modal"' : 'data-modal-note-add'; ?>
            <?php if ($persistedNoteAction ?? false): ?>data-task-uuid="<?= esc($task->uuid, 'attr'); ?>" data-task-title="<?= esc($task->title, 'attr'); ?>"<?php endif; ?>>
            <i class="fa-solid fa-note-sticky me-1" aria-hidden="true"></i><?= ui_text('backend/tasks.actions.addNote', [], false); ?>
        </button>
    <?php endif; ?>
    <?php if ($showNoteReload ?? false): ?>
        <button type="button" class="btn btn-sm app-btn-secondary" data-related-reload aria-controls="task-note-items"><i class="fa-solid fa-arrows-rotate me-1" aria-hidden="true"></i><?= ui_text('backend/tasks.buttons.reload', [], false); ?></button>
    <?php endif; ?>
</div>
