<?php $workable = $task->deleted_at === null && $task->owner_deleted_at === null; $isCurrent = ($currentTask->uuid ?? null) === $task->uuid; ?>
<div class="card-body">
    <div class="row">
        <div class="col-12 col-lg-6 mb-3">
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><i class="fa-solid fa-tag fa-fw me-1" aria-hidden="true"></i><?= ui_text('backend/tasks.labels.title', [], false); ?></li>
                <li class="list-group-item fw-bold text-break" style="white-space: pre-wrap;"><?= esc($task->title); ?></li>
            </ul>
        </div>
        <div class="col-12 col-lg-6 mb-3">
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><i class="fa-solid fa-folder-tree fa-fw me-1" aria-hidden="true"></i><?= ui_text((int)($task->is_studio ?? 0) === 1 ? 'backend/studio.title' : 'backend/tasks.labels.context', [], false); ?></li>
                <li class="list-group-item fw-bold text-break"><?= esc(\Modules\Backend\Tasks\Libraries\TasksClass::contextLabel($task)); ?></li>
            </ul>
        </div>
        <?php if (trim((string) ($task->description ?? '')) !== ''): ?>
        <div class="col-12 mb-3">
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><i class="fa-solid fa-align-left fa-fw me-1" aria-hidden="true"></i><?= ui_text('backend/tasks.labels.description', [], false); ?></li>
                <li class="list-group-item text-break" style="white-space: pre-wrap;"><?= esc($task->description ?? ''); ?></li>
            </ul>
        </div>
        <?php endif; ?>
        <div class="col-12 col-lg-6 mb-3">
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><i class="fa-solid fa-circle-half-stroke fa-fw me-1" aria-hidden="true"></i><?= ui_text('backend/tasks.labels.status', [], false); ?></li>
                <li class="list-group-item fw-bold"><?= view(\Modules\Backend\Tasks\Config\Views::STATUS_PARTIAL, ['task' => $task, 'currentTask' => $currentTask ?? null], ['saveData' => false]); ?><?php if ($isCurrent): ?> <span class="ms-1"><span class="task-current-pin" role="img" aria-label="<?= esc(lang('backend/tasks.labels.current'), 'attr'); ?>" title="<?= esc(lang('backend/tasks.labels.current'), 'attr'); ?>"><i class="fa-solid fa-thumbtack" aria-hidden="true"></i></span></span><?php endif; ?></li>
            </ul>
        </div>
        <div class="col-12 col-lg-6 mb-3">
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><i class="fa-solid fa-list-check fa-fw me-1" aria-hidden="true"></i><?= ui_text('backend/tasks.labels.steps', [], false); ?></li>
                <li class="list-group-item fw-bold"><?= view(\Modules\Backend\Tasks\Config\Views::PROGRESS_PARTIAL, ['completedSteps' => $task->completed_count, 'totalSteps' => $task->step_count], ['saveData' => false]); ?></li>
            </ul>
        </div>
        <?php if ( ! $workable): ?><div class="col-12"><p class="app-text-danger fw-bold"><?= ui_text('backend/tasks.messages.contextInactive', [], false); ?></p></div><?php endif; ?>
    </div>
    <?php if ($workable): ?>
        <div class="d-flex flex-row flex-wrap justify-content-end align-items-center gap-1">
            <?php if ( ! $isCurrent && $task->status !== 'completed' && \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin, 'tasks_set_current')): ?>
                <form novalidate class="task-action" data-endpoint="setCurrent">
                    <input type="hidden" name="uuid" value="<?= esc($task->uuid, 'attr'); ?>">
                    <button type="submit" class="btn btn-sm app-btn-secondary text-nowrap"><i class="fa-solid fa-play fa-fw"></i> <?= ui_text('backend/tasks.actions.work', [], false); ?></button>
                </form>
            <?php endif; ?>
            <?php if (\App\Libraries\Backend\AuthorizationClass::allows($currentAdmin, 'tasks_complete')): ?>
                <form novalidate class="task-action" data-endpoint="<?= $task->status === 'completed' ? 'reopen' : 'complete'; ?>" data-confirm="<?= esc(lang('backend/tasks.messages.' . ($task->status === 'completed' ? 'confirmReopen' : 'confirmComplete')), 'attr'); ?>">
                    <input type="hidden" name="uuid" value="<?= esc($task->uuid, 'attr'); ?>">
                    <button type="submit" class="btn btn-sm app-btn-secondary text-nowrap"><i class="fa-solid fa-<?= $task->status === 'completed' ? 'rotate-left' : 'check'; ?> fa-fw"></i> <?= ui_text('backend/tasks.actions.' . ($task->status === 'completed' ? 'reopen' : 'complete'), [], false); ?></button>
                </form>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
<?= $this->include(\Modules\Backend\Tasks\Config\Views::STEPS_PARTIAL); ?>
