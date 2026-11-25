<?php
    $taskNotes = []; $stepNotes = [];
    if ($canReadNotes):
        foreach (model(\Modules\Backend\Notes\Models\NotesModel::class)->getTaskNotes($task->uuid) as $note):
            if ($note->deleted_at !== null): continue; endif;
            if ($note->step_uuid !== null): $stepNotes[$note->step_uuid][] = $note;
            else: $taskNotes[] = $note; endif;
        endforeach;
    endif;
?>
<div class="studio-task-body">
    <?php if (mb_strlen($task->title) > 50): ?><p class="fw-bold mb-2 text-break"><?= esc($task->title); ?></p><?php endif; ?>
    <?php if (trim((string) ($task->description ?? '')) !== ''): ?><p class="studio-task-description text-break"><?= esc($task->description); ?></p><?php endif; ?>
    <?= view(\Modules\Backend\Tasks\Config\Views::STEPS_PARTIAL, ['task' => $task, 'currentAdmin' => $currentAdmin, 'stepNotes' => $stepNotes], ['saveData' => false]); ?>
    <?php if ($canReadNotes || \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin, 'notes_add')): ?>
        <div class="studio-task-notes">
            <div class="d-flex justify-content-between align-items-center gap-2 mb-2"><h3><?= ui_text('backend/studio.taskNotes', [], false); ?></h3>
                <?php if ($task->archived_at === null && \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin, 'notes_add')): ?><button type="button" class="btn btn-link task-step-action" data-bs-toggle="modal" data-bs-target="#tasks-note-modal" data-task-uuid="<?= esc($task->uuid, 'attr'); ?>" data-task-title="<?= esc($task->title, 'attr'); ?>" aria-label="<?= esc(lang('backend/tasks.actions.addNote'), 'attr'); ?>"><i class="fa-solid fa-note-sticky" aria-hidden="true"></i></button><?php endif; ?>
            </div>
            <?php if ($canReadNotes): ?><?= view(\Modules\Backend\Tasks\Config\Views::NOTES_PARTIAL, ['task' => $task, 'currentAdmin' => $currentAdmin, 'taskNotes' => $taskNotes, 'notesForStep' => false], ['saveData' => false]); ?><?php endif; ?>
        </div>
    <?php endif; ?>
</div>
