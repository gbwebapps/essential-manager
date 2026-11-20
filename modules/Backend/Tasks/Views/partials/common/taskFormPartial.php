<?php $editing = ! empty($task->uuid); ?>
<div class="card-body pb-0">
    <div class="row">
        <div class="col-12 col-lg-6">
            <div class="mb-2">
                <label for="title" class="form-label required-field"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/tasks.labels.title', [], false); ?></label>
                <input <?= ui_binding('backend/tasks.placeholders.title', 'placeholder', [], true); ?> type="text" name="title" id="title" class="form-control" value="<?= esc($task->title ?? '', 'attr'); ?>" placeholder="<?= esc(lang('backend/tasks.placeholders.title'), 'attr'); ?>" aria-required="true">
                <div class="error_title app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="mb-2">
                <label for="task-context" class="form-label required-field"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/tasks.labels.context', [], false); ?></label>
                <?php if ($editing): ?>
                    <div class="form-control-plaintext fw-bold text-break"><?= esc($task->project_name); ?><?= $task->application_name !== null ? ' / ' . esc($task->application_name) : ''; ?></div>
                    <input type="hidden" name="context" value="<?= esc($task->project_uuid !== null ? 'project:' . $task->project_uuid : 'application:' . $task->application_uuid, 'attr'); ?>">
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
        <div class="col-12">
            <div class="mb-2">
                <label for="description" class="form-label"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/tasks.labels.description', [], false); ?></label>
                <textarea <?= ui_binding('backend/tasks.placeholders.description', 'placeholder', [], true); ?> name="description" id="description" rows="5" class="form-control" placeholder="<?= esc(lang('backend/tasks.placeholders.description'), 'attr'); ?>"><?= esc($task->description ?? ''); ?></textarea>
                <div class="error_description app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
            </div>
        </div>
        <div class="col-12">
            <fieldset class="mb-2" data-task-steps data-standalone="true" data-field-prefix="tasks-main" data-step-label="<?= esc(lang('backend/tasks.labels.step'), 'attr'); ?>">
                <legend class="form-label fs-6 float-none"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/tasks.labels.steps', [], false); ?></legend>
                <div data-task-step-list>
                    <?php foreach ($task->steps ?? [(object) ['title' => '']] as $stepIndex => $taskStep): ?>
                        <?= view(\Modules\Backend\Tasks\Config\Views::EDITOR_STEP_PARTIAL, ['stepIndex' => $stepIndex, 'stepTitle' => $taskStep->title, 'stepUuid' => $taskStep->uuid ?? '', 'task' => $task ?? null, 'stepNotes' => $stepNotes ?? [], 'currentAdmin' => $currentAdmin], ['saveData' => false]); ?>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="btn btn-sm app-btn-secondary mt-1" data-task-step-add><i class="fa-solid fa-plus me-1" aria-hidden="true"></i><?= ui_text('backend/tasks.actions.addStep', [], false); ?></button>
                <template data-task-step-template><?= view(\Modules\Backend\Tasks\Config\Views::EDITOR_STEP_PARTIAL, ['stepIndex' => '__new__', 'stepTitle' => '', 'stepUuid' => ''], ['saveData' => false]); ?></template>
                <div class="error_steps app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
            </fieldset>
        </div>
        <div class="col-12 col-lg-6">
            <div class="mb-2">
                <label for="status" class="form-label"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/tasks.labels.status', [], false); ?></label>
                <?php if ($editing): ?>
                    <div class="form-control-plaintext fw-bold"><?= ui_text('backend/tasks.statuses.' . $task->status, [], false); ?></div>
                    <input type="hidden" name="status" value="<?= esc($task->status, 'attr'); ?>">
                <?php else: ?>
                    <select name="status" id="status" class="form-select">
                        <?php foreach (['todo','in_progress','completed'] as $status): ?>
                            <option value="<?= $status; ?>"><?= ui_text('backend/tasks.statuses.' . $status, [], false); ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
                <div class="error_status app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
            </div>
        </div>
        <?php if ( ! $editing && \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin, 'notes_add')): ?>
            <div class="col-12">
                <div class="mb-2">
                    <label for="initial_note" class="form-label"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/tasks.labels.initialNote', [], false); ?></label>
                    <textarea name="initial_note" id="initial_note" rows="3" class="form-control"></textarea>
                    <div class="error_initial_note app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
                </div>
            </div>
        <?php endif; ?>
        <div class="col-12"><div class="error_payload app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div></div>
    </div>
</div>
