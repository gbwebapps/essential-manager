<div class="task-modal-step" data-task-step-row>
    <div class="d-flex align-items-center gap-2">
        <span class="task-modal-number" data-step-number><?= is_int($stepIndex) ? $stepIndex + 1 : ''; ?></span>
        <label class="visually-hidden" for="<?= esc($fieldPrefix . '-step-' . $stepIndex, 'attr'); ?>" data-step-label><?= ui_text('backend/tasks.labels.step', [], false); ?></label>
        <input type="hidden" name="steps[<?= esc((string) $stepIndex, 'attr'); ?>][uuid]" value="<?= esc($stepUuid ?? '', 'attr'); ?>" data-step-uuid>
        <input type="text" id="<?= esc($fieldPrefix . '-step-' . $stepIndex, 'attr'); ?>" name="steps[<?= esc((string) $stepIndex, 'attr'); ?>][title]" class="form-control form-control-sm" value="<?= esc($stepTitle ?? '', 'attr'); ?>" placeholder="<?= esc(lang('backend/tasks.labels.step'), 'attr'); ?>" data-step-title>
        <div class="d-flex task-modal-step-actions">
            <?php foreach (['up' => 'arrow-up', 'down' => 'arrow-down', 'remove' => 'trash-can'] as $stepAction => $stepIcon): ?>
                <button type="button" class="btn btn-link" data-task-step-<?= $stepAction; ?> aria-label="<?= esc(lang('backend/tasks.actions.' . ['up' => 'moveUp', 'down' => 'moveDown', 'remove' => 'removeStep'][$stepAction]), 'attr'); ?>"><i class="fa-solid fa-<?= $stepIcon; ?>" aria-hidden="true"></i></button>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="task-modal-step-error app-text-danger fw-bold small" data-work-field-error="steps.<?= esc((string) $stepIndex, 'attr'); ?>.title" aria-live="polite"></div>
    <?php if (($canAddNotes ?? false) || ($canShowNotes ?? false)): ?>
        <?= view(\Modules\Backend\Tasks\Config\Views::MODAL_NOTE_GROUP_PARTIAL, ['noteGroupLabel' => lang('backend/tasks.modal.stepNotes'), 'canAddNotes' => $canAddNotes ?? false], ['saveData' => false]); ?>
    <?php endif; ?>
</div>
