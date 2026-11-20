<div data-task-step-row>
    <div class="d-flex align-items-center gap-2 py-1">
        <span class="small app-text-muted text-end" style="min-width: 1.5rem;" data-step-number><?= is_int($stepIndex) ? $stepIndex + 1 : ''; ?></span>
        <label class="visually-hidden" for="<?= esc($fieldPrefix . '-step-' . $stepIndex, 'attr'); ?>" data-step-label><?= ui_text('backend/tasks.labels.step', [], false); ?></label>
        <input type="hidden" name="steps[<?= esc((string) $stepIndex, 'attr'); ?>][uuid]" value="<?= esc($stepUuid ?? '', 'attr'); ?>" data-step-uuid>
        <input type="text" id="<?= esc($fieldPrefix . '-step-' . $stepIndex, 'attr'); ?>" name="steps[<?= esc((string) $stepIndex, 'attr'); ?>][title]" class="form-control form-control-sm" value="<?= esc($stepTitle ?? '', 'attr'); ?>" data-step-title>
        <button type="button" class="btn btn-sm app-btn-secondary" data-task-step-remove aria-label="<?= esc(lang('backend/tasks.actions.removeStep'), 'attr'); ?>" title="<?= esc(lang('backend/tasks.actions.removeStep'), 'attr'); ?>"><i class="fa-solid fa-trash-can" aria-hidden="true"></i></button>
    </div>
    <div class="app-text-danger fw-bold small" data-work-field-error="steps.<?= esc((string) $stepIndex, 'attr'); ?>.title" aria-live="polite"></div>
</div>
