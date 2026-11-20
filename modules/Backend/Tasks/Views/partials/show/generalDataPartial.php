<?php $workable = $task->deleted_at === null && $task->owner_deleted_at === null; $isCurrent = ($currentTask->uuid ?? null) === $task->uuid; ?>
<div class="card-body">
    <div class="row">
        <div class="col-12 col-lg-6 mb-3">
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/tasks.labels.title', [], false); ?></li>
                <li class="list-group-item fw-bold text-break" style="white-space: pre-wrap;"><?= esc($task->title); ?></li>
            </ul>
        </div>
        <div class="col-12 col-lg-6 mb-3">
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/tasks.labels.context', [], false); ?></li>
                <li class="list-group-item fw-bold text-break"><?= esc($task->project_name); ?><?= $task->application_name !== null ? ' / ' . esc($task->application_name) : ''; ?></li>
            </ul>
        </div>
        <?php if (trim((string) ($task->description ?? '')) !== ''): ?>
        <div class="col-12 mb-3">
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/tasks.labels.description', [], false); ?></li>
                <li class="list-group-item fw-bold text-break" style="white-space: pre-wrap;"><?= esc($task->description ?? ''); ?></li>
            </ul>
        </div>
        <?php endif; ?>
        <div class="col-12 col-lg-6 mb-3">
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/tasks.labels.status', [], false); ?></li>
                <li class="list-group-item fw-bold"><?= ui_text('backend/tasks.statuses.' . $task->status, [], false); ?><?php if ($isCurrent): ?> <span class="small app-text-muted">(<?= ui_text('backend/tasks.labels.' . ($currentTask->paused_at !== null ? 'paused' : 'current'), [], false); ?>)</span><?php endif; ?></li>
            </ul>
        </div>
        <div class="col-12 col-lg-6 mb-3">
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/tasks.labels.steps', [], false); ?></li>
                <li class="list-group-item fw-bold"><?= (int) $task->completed_count; ?>/<?= (int) $task->step_count; ?></li>
            </ul>
        </div>
        <?php if ( ! $workable): ?><div class="col-12"><p class="app-text-danger fw-bold"><?= ui_text('backend/tasks.messages.contextInactive', [], false); ?></p></div><?php endif; ?>
    </div>
    <?php if ($workable): ?>
        <div class="d-flex flex-row flex-wrap justify-content-end align-items-center gap-1">
            <?php if ($task->status !== 'completed' && \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin, 'tasks_set_current')): ?>
                <form novalidate class="task-action" data-endpoint="<?= $isCurrent ? ($currentTask->paused_at !== null ? 'resumeCurrent' : 'pauseCurrent') : 'setCurrent'; ?>">
                    <?php if ( ! $isCurrent): ?><input type="hidden" name="uuid" value="<?= esc($task->uuid, 'attr'); ?>"><?php endif; ?>
                    <button type="submit" class="btn btn-sm app-btn-secondary text-nowrap"><i class="fa-solid fa-<?= $isCurrent && $currentTask->paused_at === null ? 'pause' : 'play'; ?> fa-fw"></i> <?= ui_text('backend/tasks.actions.' . ($isCurrent ? ($currentTask->paused_at !== null ? 'resume' : 'pause') : 'work'), [], false); ?></button>
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
