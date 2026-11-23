<?php if (\App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, 'notes_show')): ?>
    <section class="task-related-panel" data-task-related-panel="note" data-task-uuid="<?= esc($task->uuid, 'attr'); ?>" data-reload-error="<?= esc(lang('backend/tasks.related.reloadError'), 'attr'); ?>">
        <div class="card-header rounded-0 d-flex justify-content-between align-items-center">
            <h2 class="card-title text-start mb-0"><?= ui_text('backend/tasks.panels.taskNotes', [], false); ?></h2>
            <div class="d-flex flex-wrap gap-1">
                <?php if ($task->deleted_at === null && $task->owner_deleted_at === null && \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin, 'notes_add')): ?>
                    <button type="button" class="btn btn-sm app-btn-secondary" data-bs-toggle="modal" data-bs-target="#tasks-note-modal" data-task-uuid="<?= esc($task->uuid, 'attr'); ?>" data-task-title="<?= esc($task->title, 'attr'); ?>">
                        <i class="fa-solid fa-plus me-1" aria-hidden="true"></i><?= ui_text('backend/tasks.actions.addNote', [], false); ?>
                    </button>
                <?php endif; ?>
                <button type="button" class="btn btn-sm app-btn-secondary" data-related-reload aria-controls="task-note-items">
                    <i class="fa-solid fa-arrows-rotate me-1" aria-hidden="true"></i><?= ui_text('backend/tasks.buttons.reload', [], false); ?>
                </button>
            </div>
        </div>
        <div id="task-note-items" data-related-items>
            <?= view(\Modules\Backend\Tasks\Config\Views::NOTES_PARTIAL, ['task'=>$task, 'currentAdmin'=>$currentAdmin, 'taskNotes'=>$taskNotes, 'notesForStep'=>false], ['saveData'=>false]); ?>
        </div>
    </section>
<?php endif; ?>
