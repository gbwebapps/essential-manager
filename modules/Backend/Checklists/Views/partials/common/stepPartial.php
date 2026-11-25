<div data-task-step-row>
    <div class="task-step-field-row">
        <span class="task-step-number" data-step-number><?= is_int($stepIndex) ? $stepIndex + 1 : ''; ?></span>
        <label class="visually-hidden" for="checklists-main-step-<?= esc((string) $stepIndex, 'attr'); ?>" data-step-label><?= ui_text('backend/tasks.labels.step', [], false); ?></label>
        <input type="hidden" name="steps[<?= esc((string) $stepIndex, 'attr'); ?>][uuid]" value="<?= esc($stepUuid, 'attr'); ?>" data-step-uuid>
        <input <?= ui_binding('backend/tasks.placeholders.step', 'placeholder', [], true); ?> type="text" id="checklists-main-step-<?= esc((string) $stepIndex, 'attr'); ?>" name="steps[<?= esc((string) $stepIndex, 'attr'); ?>][title]" class="form-control form-control-sm" value="<?= esc($stepTitle, 'attr'); ?>" placeholder="<?= esc(lang('backend/tasks.placeholders.step'), 'attr'); ?>" data-step-title>
        <div class="task-step-actions">
            <button type="button" class="btn btn-link task-step-action" data-task-step-up aria-label="<?= esc(lang('backend/tasks.actions.moveUp'), 'attr'); ?>"><i class="fa-solid fa-arrow-up" aria-hidden="true"></i></button>
            <button type="button" class="btn btn-link task-step-action" data-task-step-down aria-label="<?= esc(lang('backend/tasks.actions.moveDown'), 'attr'); ?>"><i class="fa-solid fa-arrow-down" aria-hidden="true"></i></button>
            <button type="button" class="btn btn-link task-step-action" data-task-step-remove aria-label="<?= esc(lang('backend/tasks.actions.removeStep'), 'attr'); ?>"><i class="fa-solid fa-trash-can" aria-hidden="true"></i></button>
        </div>
    </div>
    <div class="error_step_<?= esc((string) $stepIndex, 'attr'); ?>_title app-text-danger fw-bold small pt-1" data-work-field-error="steps.<?= esc((string) $stepIndex, 'attr'); ?>.title" aria-live="polite">&nbsp;</div>
</div>
