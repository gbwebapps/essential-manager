<article class="manager-postit" data-paper="<?= esc($dashboardPresenter->paper($task->uuid), 'attr'); ?>" data-dashboard-task="<?= esc($task->uuid, 'attr'); ?>" data-task-current="<?= (bool) $task->is_current ? 'true' : 'false'; ?>">
    <div class="manager-postit-date">
        <i class="fa-<?= (bool) $task->is_current ? 'solid fa-thumbtack' : 'regular fa-clock'; ?>" aria-hidden="true"></i>
        <time datetime="<?= esc(str_replace(' ', 'T', $task->created_at), 'attr'); ?>"><?= ui_date($task->created_at, 'd MMM · HH:mm'); ?></time>
    </div>
    <h3 title="<?= esc($task->title, 'attr'); ?>"><?= esc($dashboardPresenter->preview($task->title)); ?></h3>
    <div class="manager-desk-context"><i class="fa-solid fa-<?= $task->application_uuid !== null ? 'desktop' : 'folder'; ?> fa-fw" aria-hidden="true"></i><span><?= esc($dashboardPresenter->preview($task->application_name ?? $task->project_name)); ?></span></div>
    <?= view(\Modules\Backend\Tasks\Config\Views::STATUS_PARTIAL, ['task' => $task], ['saveData' => false]); ?>
    <?php if (trim((string) $task->description) !== ''): ?><p class="manager-postit-description"><?= esc($dashboardPresenter->preview($task->description, 140)); ?></p><?php endif; ?>
    <div class="manager-postit-next">
        <?php if ($task->status === 'completed'): ?>
            <span class="manager-desk-caption"><?= ui_text('backend/managerDashboard.finished', [], false); ?></span><p><?= ui_text((int) $task->step_count > 0 ? 'backend/managerDashboard.allStepsDone' : 'backend/tasks.statuses.completed', [], false); ?></p>
        <?php elseif (trim((string) $task->next_step_title) !== ''): ?>
            <span class="manager-desk-caption"><?= ui_text('backend/tasks.labels.nextStep', [], false); ?></span><p><?= esc($dashboardPresenter->preview($task->next_step_title, 100)); ?></p>
        <?php endif; ?>
    </div>
    <div class="manager-postit-progress"><?= view(\Modules\Backend\Tasks\Config\Views::PROGRESS_PARTIAL, ['completedSteps' => $task->completed_count, 'totalSteps' => $task->step_count], ['saveData' => false]); ?></div>
    <div class="manager-postit-footer">
        <?php if ($dashboardAccess['notes']): ?><span class="manager-postit-notes" aria-label="<?= esc(lang('backend/tasks.labels.notes') . ': ' . (int) $task->note_count, 'attr'); ?>"><i class="fa-regular fa-note-sticky" aria-hidden="true"></i><?= (int) $task->note_count; ?></span><?php endif; ?>
        <div class="manager-postit-actions">
            <?php if ($dashboardAccess['show']): ?><a href="<?= esc($dashboardPresenter->taskUrl($task), 'attr'); ?>"><i class="fa-regular fa-eye me-1" aria-hidden="true"></i><?= ui_text('backend/tasks.actions.show', [], false); ?></a><?php endif; ?>
            <?php if ($dashboardAccess['edit']): ?><a href="<?= esc($dashboardPresenter->taskUrl($task, true), 'attr'); ?>" aria-label="<?= esc(lang('backend/tasks.actions.edit') . ': ' . $task->title, 'attr'); ?>"><i class="fa-solid fa-pen me-1" aria-hidden="true"></i><?= ui_text('backend/tasks.actions.edit', [], false); ?></a><?php endif; ?>
        </div>
    </div>
</article>
