<?php
    $fieldPrefix = $fieldPrefix ?? 'task';
?>
<div class="row">
    <div class="col-12 col-lg-6">
        <?= view(\Modules\Backend\Tasks\Config\Views::TITLE_FIELD_PARTIAL, ['fieldPrefix' => $fieldPrefix, 'task' => $task ?? null], ['saveData' => false]); ?>
    </div>
    <div class="col-12 col-lg-6">
        <?= view(\Modules\Backend\Tasks\Config\Views::OWNER_FIELD_PARTIAL, ['fieldPrefix' => $fieldPrefix, 'taskOwnerName' => $taskOwnerName ?? ''], ['saveData' => false]); ?>
    </div>
    <div class="col-12 col-lg-6">
        <?= view('Modules\Backend\Checklists\Views\partials\common\pickerPartial', ['currentAdmin' => $currentAdmin ?? null, 'pickerId' => $fieldPrefix . '-checklist'], ['saveData' => false]); ?>
    </div>
    <div class="col-12 col-lg-6">
        <?= view(\Modules\Backend\Tasks\Config\Views::STATUS_FIELD_PARTIAL, ['fieldPrefix' => $fieldPrefix, 'modalStatus' => true], ['saveData' => false]); ?>
    </div>
    <div class="col-12">
        <?= view(\Modules\Backend\Tasks\Config\Views::DESCRIPTION_FIELD_PARTIAL, ['fieldPrefix' => $fieldPrefix, 'task' => $task ?? null], ['saveData' => false]); ?>
    </div>
    <div class="col-12">
        <fieldset class="mb-2 task-editor-field" data-task-steps data-optional-steps="true" data-field-prefix="<?= esc($fieldPrefix, 'attr'); ?>" data-step-label="<?= esc(lang('backend/tasks.labels.step'), 'attr'); ?>">
            <legend class="form-label fs-6 float-none"><i class="fa-solid fa-list-check fa-fw me-1" aria-hidden="true"></i><?= ui_text('backend/tasks.labels.steps', [], false); ?></legend>
            <div data-task-step-list>
                <?php foreach ($task->steps ?? [] as $stepIndex => $taskStep): ?>
                    <?= view(\Modules\Backend\Tasks\Config\Views::EDITOR_STEP_PARTIAL, ['currentAdmin' => $currentAdmin ?? null, 'fieldPrefix' => $fieldPrefix, 'stepNotesMode' => 'draft', 'stepIndex' => $stepIndex, 'stepTitle' => $taskStep->title, 'stepUuid' => $taskStep->uuid ?? '', 'task' => null], ['saveData' => false]); ?>
                <?php endforeach; ?>
            </div>
            <?= view(\Modules\Backend\Tasks\Config\Views::EDITOR_ACTIONS_PARTIAL, ['currentAdmin' => $currentAdmin ?? null, 'persistedNoteAction' => false, 'showAddStepAction' => true, 'showAddNoteAction' => false], ['saveData' => false]); ?>
            <template data-task-step-template><?= view(\Modules\Backend\Tasks\Config\Views::EDITOR_STEP_PARTIAL, ['currentAdmin' => $currentAdmin ?? null, 'fieldPrefix' => $fieldPrefix, 'stepNotesMode' => 'draft', 'stepIndex' => '__new__', 'stepTitle' => '', 'stepUuid' => '', 'task' => null], ['saveData' => false]); ?></template>
            <div class="app-text-danger fw-bold small pt-1" data-work-field-error="steps" aria-live="polite">&nbsp;</div>
        </fieldset>
    </div>

    <div class="col-12">
        <?= view(\Modules\Backend\Tasks\Config\Views::NOTES_EDITOR_FIELD_PARTIAL, ['task'=>null, 'currentAdmin'=>$currentAdmin ?? null, 'persistedNotes'=>false], ['saveData'=>false]); ?>
    </div>

</div>
