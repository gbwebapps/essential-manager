<?php if ($task->steps !== []): ?>
<?php $workable = $task->deleted_at === null && $task->owner_deleted_at === null && $task->archived_at === null; ?>
<div class="card-header rounded-0 d-flex justify-content-between align-items-center" style="border-top: var(--bs-card-border-width) solid var(--bs-card-border-color);">
    <h2 class="card-title text-start mb-0"><?= ui_text('backend/tasks.panels.steps', [], false); ?></h2>
</div>
<div class="card-body">
    <ul class="list-unstyled" data-task-work-steps>
        <?php foreach ($task->steps as $stepIndex => $step): $checkboxId = 'complete-step-' . $step->uuid; ?>
            <li data-work-step="<?= esc($step->uuid, 'attr'); ?>">
                <div class="task-step-work-row">
                    <span class="task-step-number" aria-hidden="true"><?= $stepIndex + 1; ?></span>
                    <form novalidate class="task-action m-0 p-0" data-endpoint="<?= $step->completed ? 'reopenStep' : 'completeStep'; ?>">
                        <input type="hidden" name="uuid" value="<?= esc($task->uuid, 'attr'); ?>">
                        <input type="hidden" name="step_uuid" value="<?= esc($step->uuid, 'attr'); ?>">
                        <input id="<?= esc($checkboxId, 'attr'); ?>" type="checkbox" name="completed" value="1" class="form-check-input mt-1" style="cursor: pointer;" data-step-completion <?= $step->completed ? 'checked' : ''; ?> <?= ! $workable || ! \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin, 'tasks_complete') ? 'disabled' : ''; ?> aria-label="<?= esc($step->title, 'attr'); ?>">
                    </form>
                    <label for="<?= esc($checkboxId, 'attr'); ?>" style="cursor: pointer;" class="flex-grow-1 text-break <?= $step->completed ? 'app-text-muted text-decoration-line-through' : ''; ?>"><?= esc($step->title); ?></label>
                    <?php if ($workable && \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin, 'notes_add')): ?>
                        <button type="button" class="btn btn-link task-step-action" aria-label="<?= esc(lang('backend/tasks.actions.addNote'), 'attr'); ?>" data-bs-toggle="modal" data-bs-target="#tasks-note-modal" data-task-uuid="<?= esc($task->uuid, 'attr'); ?>" data-task-step-title="<?= esc($step->title, 'attr'); ?>" data-task-note-step="<?= esc($step->uuid, 'attr'); ?>" data-task-title="<?= esc($task->title, 'attr'); ?>"><i class="fa-solid fa-note-sticky" aria-hidden="true"></i></button>
                    <?php endif; ?>
                </div>
                <?php if (\App\Libraries\Backend\AuthorizationClass::allows($currentAdmin, 'notes_show')): ?>
                    <div class="task-related-panel" data-step-note-items="<?= esc($step->uuid, 'attr'); ?>">
                        <?= view(\Modules\Backend\Tasks\Config\Views::NOTES_PARTIAL, ['task' => $task, 'currentAdmin' => $currentAdmin, 'taskNotes' => $stepNotes[$step->uuid] ?? [], 'notesForStep' => true], ['saveData' => false]); ?>
                    </div>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<?php endif; ?>
