<?php $statusInputId = ($fieldPrefix ?? '') !== '' ? $fieldPrefix . '-status' : 'status'; ?>
<?php if ($modalStatus ?? false): ?>
    <div class="mb-2">
        <label for="<?= esc($statusInputId, 'attr'); ?>" class="form-label"><i class="fa-solid fa-circle-half-stroke fa-fw me-1" aria-hidden="true"></i><?= ui_text('backend/tasks.labels.status', [], false); ?></label>
        <input type="hidden" name="status" value="todo">
        <div data-modal-status-choice>
            <select id="<?= esc($statusInputId, 'attr'); ?>" class="form-select" data-modal-status-select>
                <?php foreach (['todo', 'in_progress', 'completed'] as $status): ?>
                    <option <?= ui_binding('backend/tasks.statuses.' . $status, 'text', [], false); ?> value="<?= $status; ?>"><?= esc(lang('backend/tasks.statuses.' . $status)); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="fw-bold py-1" data-modal-status-display hidden>
            <?php foreach (['todo', 'in_progress', 'completed'] as $status): ?>
                <span data-modal-status-value="<?= $status; ?>" hidden><?= view(\Modules\Backend\Tasks\Config\Views::STATUS_PARTIAL, ['task' => (object) ['status' => $status]], ['saveData' => false]); ?></span>
            <?php endforeach; ?>
        </div>
        <div class="error_status app-text-danger fw-bold small pt-1" data-work-field-error="status" aria-live="polite">&nbsp;</div>
    </div>
<?php else: ?>
<div class="<?= $editing ? 'mb-0' : 'mb-2'; ?>">
    <label for="status" class="form-label"><i class="fa-solid fa-circle-half-stroke fa-fw me-1" aria-hidden="true"></i><?= ui_text('backend/tasks.labels.status', [], false); ?></label>
    <?php if ($editing): ?>
        <div class="fw-bold py-1"><?= view(\Modules\Backend\Tasks\Config\Views::STATUS_PARTIAL, ['task' => $task, 'currentTask' => $currentTask ?? null], ['saveData' => false]); ?></div>
        <input type="hidden" name="status" value="<?= esc($task->status, 'attr'); ?>">
    <?php else: ?>
        <select name="status" id="status" class="form-select">
            <?php foreach (['todo','in_progress','completed'] as $status): ?>
                <option value="<?= $status; ?>"><?= ui_text('backend/tasks.statuses.' . $status, [], false); ?></option>
            <?php endforeach; ?>
        </select>
    <?php endif; ?>
    <?php if ($editing): ?>
        <div class="d-flex flex-wrap column-gap-2">
            <div class="error_status app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
            <div class="error_payload app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
        </div>
    <?php else: ?>
        <div class="error_status app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
    <?php endif; ?>
</div>
<?php endif; ?>
