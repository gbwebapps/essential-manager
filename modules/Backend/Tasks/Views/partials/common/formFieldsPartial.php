<?php $fieldPrefix = $fieldPrefix ?? 'task'; ?>
<div class="mb-3">
    <label for="<?= esc($fieldPrefix, 'attr'); ?>-title" class="form-label required-field"><?= ui_text('backend/tasks.labels.title', [], false); ?></label>
    <input type="text" id="<?= esc($fieldPrefix, 'attr'); ?>-title" name="title" class="form-control" value="<?= esc($task->title ?? '', 'attr'); ?>">
    <div class="app-text-danger fw-bold small pt-1" data-work-field-error="title" aria-live="polite"></div>
</div>
<div class="mb-3">
    <label for="<?= esc($fieldPrefix, 'attr'); ?>-description" class="form-label"><?= ui_text('backend/tasks.labels.description', [], false); ?></label>
    <textarea id="<?= esc($fieldPrefix, 'attr'); ?>-description" name="description" class="form-control" rows="3"><?= esc($task->description ?? ''); ?></textarea>
    <div class="app-text-danger fw-bold small pt-1" data-work-field-error="description" aria-live="polite"></div>
</div>
<fieldset class="mb-3" data-task-steps data-field-prefix="<?= esc($fieldPrefix, 'attr'); ?>" data-step-label="<?= esc(lang('backend/tasks.labels.step'), 'attr'); ?>">
    <legend class="form-label fs-6 float-none mb-1"><?= ui_text('backend/tasks.labels.steps', [], false); ?></legend>
    <div data-task-step-list>
        <?php foreach ($task->steps ?? [(object) ['title' => '']] as $stepIndex => $taskStep): ?>
            <?= view(\Modules\Backend\Tasks\Config\Views::STEP_FIELD_PARTIAL, ['fieldPrefix' => $fieldPrefix, 'stepIndex' => $stepIndex, 'stepTitle' => $taskStep->title, 'stepUuid' => $taskStep->uuid ?? ''], ['saveData' => false]); ?>
        <?php endforeach; ?>
    </div>
    <button type="button" class="btn btn-sm app-btn-secondary mt-1" data-task-step-add><i class="fa-solid fa-plus me-1" aria-hidden="true"></i><?= ui_text('backend/tasks.actions.addStep', [], false); ?></button>
    <template data-task-step-template>
        <?= view(\Modules\Backend\Tasks\Config\Views::STEP_FIELD_PARTIAL, ['fieldPrefix' => $fieldPrefix, 'stepIndex' => '__new__', 'stepTitle' => '', 'stepUuid' => ''], ['saveData' => false]); ?>
    </template>
    <div class="app-text-danger fw-bold small pt-1" data-work-field-error="steps" aria-live="polite"></div>
</fieldset>

<div>
    <label for="<?= esc($fieldPrefix, 'attr'); ?>-status" class="form-label"><?= ui_text('backend/tasks.labels.status', [], false); ?></label>
    <select id="<?= esc($fieldPrefix, 'attr'); ?>-status" name="status" class="form-select">
        <?php foreach (['todo', 'in_progress', 'completed'] as $taskStatus): ?>
            <option <?= ui_binding('backend/tasks.statuses.' . $taskStatus, 'text', [], false); ?> value="<?= $taskStatus; ?>" <?= ($task->status ?? 'todo') === $taskStatus ? 'selected' : ''; ?>><?= esc(lang('backend/tasks.statuses.' . $taskStatus)); ?></option>
        <?php endforeach; ?>
    </select>
    <div class="app-text-danger fw-bold small pt-1" data-work-field-error="status" aria-live="polite"></div>
</div>
