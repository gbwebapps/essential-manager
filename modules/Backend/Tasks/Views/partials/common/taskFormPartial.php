<?php $editing = ! empty($task->uuid); ?>
<div class="card-body pb-0">
    <div class="row">
        <div class="col-12 col-lg-6">
            <?= view(\Modules\Backend\Tasks\Config\Views::TITLE_FIELD_PARTIAL, ['task' => $task ?? null, 'fieldPrefix' => ''], ['saveData' => false]); ?>
        </div>
        <div class="col-12 col-lg-6">
            <div class="mb-2">
                <label for="task-context" class="form-label required-field"><i class="fa-solid fa-folder-tree fa-fw me-1" aria-hidden="true"></i><?= ui_text(($studioTaskContext ?? false) ? 'backend/studio.title' : 'backend/tasks.labels.context', [], false); ?></label>
                <?php if ($editing || ($studioTaskContext ?? false)): ?>
                    <input type="text" id="task-context" class="form-control" disabled value="<?= esc(($studioTaskContext ?? false) ? lang('backend/studio.title') : \Modules\Backend\Tasks\Libraries\TasksClass::contextLabel($task), 'attr'); ?>">
                    <input type="hidden" name="context" value="<?= esc(($studioTaskContext ?? false) ? 'studio' : ($task->project_uuid !== null ? 'project:' . $task->project_uuid : 'application:' . $task->application_uuid), 'attr'); ?>">
                <?php else: ?>
                    <select name="context" id="task-context" class="form-select" aria-required="true">
                        <option value=""><?= ui_text('backend/tasks.labels.chooseContext', [], false); ?></option>
                        <?php foreach (['project', 'application'] as $kind): ?>
                            <optgroup label="<?= esc(lang('backend/tasks.labels.' . $kind), 'attr'); ?>">
                                <?php foreach ($ownerOptions as $owner): if ($owner->kind !== $kind): continue; endif; ?>
                                    <option value="<?= esc($owner->value, 'attr'); ?>"><?= esc($owner->label); ?></option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
                <div class="error_context app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
            </div>
        </div>
        <?php if ($studioTaskContext ?? false): ?>
        <div class="col-12 col-lg-6">
            <div class="mb-2">
                <label for="studio-reference" class="form-label"><i class="fa-solid fa-link fa-fw me-1" aria-hidden="true"></i><?= ui_text('backend/studio.reference', [], false); ?></label>
                <select name="reference" id="studio-reference" class="form-select">
                    <option <?= ui_binding('backend/studio.noReference', 'text', [], false); ?> value=""><?= esc(lang('backend/studio.noReference')); ?></option>
                    <?php $referenceValue = $task->reference->value ?? ''; $referenceFound = false; ?>
                    <?php foreach ($ownerOptions as $option): ?>
                        <?php if ($option->value === $referenceValue): $referenceFound = true; endif; ?>
                        <option value="<?= esc($option->value, 'attr'); ?>" <?= $option->value === $referenceValue ? 'selected' : ''; ?>><?= esc($option->label); ?></option>
                    <?php endforeach; ?>
                    <?php if ($referenceValue !== '' && ! $referenceFound): ?><option value="<?= esc($referenceValue, 'attr'); ?>" selected><?= ui_text('backend/studio.retainedReference', [], false); ?></option><?php endif; ?>
                </select>
                <div class="error_reference app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
            </div>
        </div>
        <?php endif; ?>
        <div class="col-12 col-lg-6">
                <?= view('Modules\Backend\Checklists\Views\partials\common\pickerPartial', ['currentAdmin' => $currentAdmin ?? null], ['saveData' => false]); ?>
        </div>
        <div class="col-12 col-lg-6">
            <?= view(\Modules\Backend\Tasks\Config\Views::STATUS_FIELD_PARTIAL, ['task' => $task ?? null, 'editing' => $editing, 'fieldPrefix' => '', 'modalStatus' => false], ['saveData' => false]); ?>
        </div>
        <div class="col-12">
            <?= view(\Modules\Backend\Tasks\Config\Views::DESCRIPTION_FIELD_PARTIAL, ['task' => $task ?? null, 'fieldPrefix' => ''], ['saveData' => false]); ?>
        </div>
        <div class="col-12">
            <fieldset class="mb-2 task-editor-field" data-task-steps data-optional-steps="true" data-standalone="true" data-field-prefix="tasks-main" data-step-label="<?= esc(lang('backend/tasks.labels.step'), 'attr'); ?>">
                <legend class="form-label fs-6 float-none"><i class="fa-solid fa-list-check fa-fw me-1" aria-hidden="true"></i><?= ui_text('backend/tasks.labels.steps', [], false); ?></legend>
                <div data-task-step-list>
                    <?php foreach ($task->steps ?? [] as $stepIndex => $taskStep): ?>
                        <?= view(\Modules\Backend\Tasks\Config\Views::EDITOR_STEP_PARTIAL, ['fieldPrefix' => 'tasks-main', 'stepNotesMode' => empty($taskStep->uuid) ? 'draft' : 'persisted', 'stepIndex' => $stepIndex, 'stepTitle' => $taskStep->title, 'stepUuid' => $taskStep->uuid ?? '', 'task' => $task ?? null, 'stepNotes' => $stepNotes ?? [], 'currentAdmin' => $currentAdmin], ['saveData' => false]); ?>
                    <?php endforeach; ?>
                </div>
                <?= view(\Modules\Backend\Tasks\Config\Views::EDITOR_ACTIONS_PARTIAL, ['currentAdmin' => $currentAdmin, 'task' => $task ?? null, 'persistedNoteAction' => false, 'showAddStepAction'=>true, 'showAddNoteAction'=>false], ['saveData' => false]); ?>
                <template data-task-step-template><?= view(\Modules\Backend\Tasks\Config\Views::EDITOR_STEP_PARTIAL, ['fieldPrefix' => 'tasks-main', 'stepNotesMode' => 'draft', 'stepIndex' => '__new__', 'stepTitle' => '', 'stepUuid' => '', 'task' => $task ?? null, 'currentAdmin' => $currentAdmin], ['saveData' => false]); ?></template>
                <div class="error_steps app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
            </fieldset>
        </div>
        <div class="col-12">
            <?= view(\Modules\Backend\Tasks\Config\Views::NOTES_EDITOR_FIELD_PARTIAL, ['task'=>$task ?? null, 'currentAdmin'=>$currentAdmin, 'persistedNotes'=>$editing, 'taskNotes'=>$taskNotes ?? []], ['saveData'=>false]); ?>
        </div>
        <?php if ( ! $editing): ?>
            <div class="col-12"><div class="error_payload app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div></div>
        <?php endif; ?>
    </div>
</div>
<?php if ($editing): ?>
<?= view(\Modules\Backend\Tasks\Config\Views::MODAL_NOTES_PARTIAL, ['currentAdmin' => $currentAdmin, 'includeTaskNotes' => false], ['saveData' => false]); ?>
<?php endif; ?>
