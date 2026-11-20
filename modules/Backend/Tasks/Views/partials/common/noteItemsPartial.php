<?php $workable = $task->deleted_at === null && $task->owner_deleted_at === null; ?>
<?php if (empty($taskNotes)): ?>
    <?php if (empty($notesForStep)): ?><p class="small app-text-muted px-3 py-2 mb-0"><?= ui_text('backend/tasks.related.noNotes', [], false); ?></p><?php endif; ?>
<?php else: ?>
    <?php foreach ($taskNotes as $note): ?>
        <?php $collapseId = 'task-note-' . $note->uuid; $record = ['uuid' => $note->uuid, 'content' => $note->content, 'step_uuid' => $note->step_uuid ?? '']; ?>
        <article class="task-related-item" data-task-note="<?= esc($note->uuid, 'attr'); ?>">
            <div class="task-note-header d-flex align-items-center gap-1">
                <button type="button" id="<?= esc($collapseId, 'attr'); ?>-toggle" class="task-related-toggle flex-grow-1" data-bs-toggle="collapse" data-bs-target="#<?= esc($collapseId, 'attr'); ?>" aria-controls="<?= esc($collapseId, 'attr'); ?>" aria-expanded="false">
                    <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                    <span class="task-related-summary"><span class="task-related-title fw-bold text-truncate"><?= esc(mb_strimwidth(strtok($note->content, "\r\n"), 0, 150, '…')); ?></span></span>
                </button>
                <div class="d-flex flex-row flex-nowrap justify-content-end align-items-center gap-1 flex-shrink-0 pe-2">
                    <?php if ($note->deleted_at === null && $workable && \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin, 'notes_edit')): ?>
                        <button type="button" class="btn btn-link btn-sm p-1 border-0 shadow-none task-record-link" data-bs-toggle="modal" data-bs-target="#tasks-note-modal" data-task-step-title="<?= esc($note->step_title ?? '', 'attr'); ?>" aria-label="<?= esc(lang('backend/tasks.actions.edit'), 'attr'); ?>" data-task-note-record="<?= esc(json_encode($record, JSON_THROW_ON_ERROR), 'attr'); ?>" data-task-uuid="<?= esc($task->uuid, 'attr'); ?>" data-task-title="<?= esc($task->title, 'attr'); ?>"><i class="fa-solid fa-pen fa-fw" aria-hidden="true"></i><span class="d-none d-sm-inline"> <?= ui_text('backend/tasks.actions.edit', [], false); ?></span></button>
                    <?php endif; ?>
                    <?php if (\App\Libraries\Backend\AuthorizationClass::allows($currentAdmin, 'notes_delete')): ?>
                        <button type="submit" form="tasks-delete-note" aria-label="<?= esc(lang('backend/tasks.actions.hardDelete'), 'attr'); ?>" name="uuid" value="<?= esc($note->uuid, 'attr'); ?>" class="btn btn-link btn-sm p-1 border-0 shadow-none task-record-link app-text-danger text-nowrap"><i class="fa-solid fa-trash-can fa-fw" aria-hidden="true"></i><span class="d-none d-sm-inline"> <?= ui_text('backend/tasks.actions.hardDelete', [], false); ?></span></button>
                    <?php endif; ?>
                </div>
            </div>
            <div id="<?= esc($collapseId, 'attr'); ?>" class="collapse" role="region" aria-labelledby="<?= esc($collapseId, 'attr'); ?>-toggle">
                <div class="task-related-detail"><p class="task-related-text"><?= esc($note->content); ?></p></div>
            </div>
        </article>
    <?php endforeach; ?>
<?php endif; ?>
