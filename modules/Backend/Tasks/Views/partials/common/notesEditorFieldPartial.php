<?php
    $canAddEditorNotes = \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, 'notes_add');
    $canReadEditorNotes = \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, 'notes_show');
?>
<?php if ($canAddEditorNotes || $canReadEditorNotes): ?>
<fieldset class="mb-2 task-editor-field" data-task-note-fieldset>
    <legend class="form-label fs-6 float-none"><i class="fa-solid fa-note-sticky fa-fw me-1" aria-hidden="true"></i><?= ui_text('backend/tasks.related.notes', [], false); ?></legend>
    <?php if ($persistedNotes ?? false): ?>
        <section class="task-related-panel task-editor-notes-panel" data-task-related-panel="note" data-task-uuid="<?= esc($task->uuid, 'attr'); ?>" data-reload-error="<?= esc(lang('backend/tasks.related.reloadError'), 'attr'); ?>">
            <div id="task-note-items" data-related-items>
                <?php if ($canReadEditorNotes && ! empty($taskNotes)): ?>
                    <?= view(\Modules\Backend\Tasks\Config\Views::NOTES_PARTIAL, ['task'=>$task, 'currentAdmin'=>$currentAdmin, 'taskNotes'=>$taskNotes, 'notesForStep'=>false], ['saveData'=>false]); ?>
                <?php endif; ?>
            </div>
            <?= view(\Modules\Backend\Tasks\Config\Views::EDITOR_ACTIONS_PARTIAL, ['task'=>$task, 'currentAdmin'=>$currentAdmin, 'persistedNoteAction'=>true, 'showAddStepAction'=>false, 'showAddNoteAction'=>true, 'showNoteReload'=>$canReadEditorNotes], ['saveData'=>false]); ?>
        </section>
    <?php else: ?>
        <?= view(\Modules\Backend\Tasks\Config\Views::MODAL_NOTES_PARTIAL, ['currentAdmin'=>$currentAdmin, 'includeTaskNotes'=>true], ['saveData'=>false]); ?>
        <?= view(\Modules\Backend\Tasks\Config\Views::EDITOR_ACTIONS_PARTIAL, ['currentAdmin'=>$currentAdmin, 'persistedNoteAction'=>false, 'showAddStepAction'=>false, 'showAddNoteAction'=>true, 'showNoteReload'=>false], ['saveData'=>false]); ?>
    <?php endif; ?>
</fieldset>
<?php endif; ?>
