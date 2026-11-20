<div data-task-step-row>
    <div class="d-flex align-items-center gap-2 py-1">
        <span class="small app-text-muted text-end" style="min-width: 1.5rem;" data-step-number><?= is_int($stepIndex) ? $stepIndex + 1 : ''; ?></span>
        <label class="visually-hidden" for="tasks-main-step-<?= esc((string) $stepIndex, 'attr'); ?>" data-step-label><?= ui_text('backend/tasks.labels.step', [], false); ?></label>
        <input type="hidden" name="steps[<?= esc((string) $stepIndex, 'attr'); ?>][uuid]" value="<?= esc($stepUuid, 'attr'); ?>" data-step-uuid>
        <input type="text" id="tasks-main-step-<?= esc((string) $stepIndex, 'attr'); ?>" name="steps[<?= esc((string) $stepIndex, 'attr'); ?>][title]" class="form-control form-control-sm" value="<?= esc($stepTitle, 'attr'); ?>" data-step-title>
        <button type="button" class="btn btn-sm app-btn-secondary" data-task-step-up aria-label="<?= esc(lang('backend/tasks.actions.moveUp'), 'attr'); ?>"><i class="fa-solid fa-arrow-up" aria-hidden="true"></i></button>
        <button type="button" class="btn btn-sm app-btn-secondary" data-task-step-down aria-label="<?= esc(lang('backend/tasks.actions.moveDown'), 'attr'); ?>"><i class="fa-solid fa-arrow-down" aria-hidden="true"></i></button>
        <button type="button" class="btn btn-sm app-btn-secondary" data-task-step-remove aria-label="<?= esc(lang('backend/tasks.actions.removeStep'), 'attr'); ?>"><i class="fa-solid fa-trash-can" aria-hidden="true"></i></button>
        <?php if ( ! empty($stepUuid) && ! empty($task) && $task->deleted_at === null && $task->owner_deleted_at === null && \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin, 'notes_add')): ?>
            <button type="button" class="btn btn-sm app-btn-secondary" data-bs-toggle="modal" data-bs-target="#tasks-note-modal" data-task-uuid="<?= esc($task->uuid, 'attr'); ?>" data-task-step-title="<?= esc($stepTitle, 'attr'); ?>" data-task-note-step="<?= esc($stepUuid, 'attr'); ?>" data-task-title="<?= esc($task->title, 'attr'); ?>" aria-label="<?= esc(lang('backend/tasks.actions.addNote'), 'attr'); ?>"><i class="fa-solid fa-note-sticky" aria-hidden="true"></i></button>
        <?php endif; ?>

    </div>
    <div class="error_step_<?= esc((string) $stepIndex, 'attr'); ?>_title app-text-danger fw-bold small pt-1" data-work-field-error="steps.<?= esc((string) $stepIndex, 'attr'); ?>.title" aria-live="polite">&nbsp;</div>

    <?php if ( ! empty($stepUuid) && ! empty($task) && \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin, 'notes_show')): ?>
        <div class="task-related-panel ms-3" data-step-note-items="<?= esc($stepUuid, 'attr'); ?>">
            <?= view(\Modules\Backend\Tasks\Config\Views::NOTES_PARTIAL, ['task'=>$task,'currentAdmin'=>$currentAdmin,'taskNotes'=>$stepNotes[$stepUuid]??[],'notesForStep'=>true], ['saveData'=>false]); ?>
        </div>
    <?php endif; ?>
</div>
