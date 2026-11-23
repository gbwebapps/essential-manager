<aside class="manager-desk-studio" aria-labelledby="manager-dashboard-studio-title">
    <div class="manager-studio-header">
        <span class="manager-studio-emblem" aria-hidden="true"><i class="fa-regular fa-building"></i></span>
        <h2 id="manager-dashboard-studio-title"><?= ui_text('backend/studio.title', [], false); ?></h2>
        <?php if ($dashboardAccess['studioTasks']): ?><span class="badge manager-studio-count"><?= (int) $dashboardStudioTaskCount; ?></span><?php endif; ?>
    </div>
    <?php if ($dashboardAccess['studioTasks']): ?>
        <div class="manager-studio-toolbar"><span><?= ui_text('backend/managerDashboard.newest', [], false); ?></span><?php if ($dashboardAccess['addStudioTask']): ?><a href="<?= esc(base_url('backend/studio/tasks/add'), 'attr'); ?>"><i class="fa-solid fa-plus me-1" aria-hidden="true"></i><?= ui_text('backend/managerDashboard.task', [], false); ?></a><?php endif; ?></div>
        <div class="manager-studio-agenda" data-dashboard-studio-tasks>
            <?php foreach ($dashboardStudioTasks as $task): ?>
                <article class="manager-studio-task <?= (bool) $task->is_current ? 'manager-studio-current' : ''; ?>" data-dashboard-studio-task="<?= esc($task->uuid, 'attr'); ?>" data-task-current="<?= (bool) $task->is_current ? 'true' : 'false'; ?>">
                    <?php if ((bool) $task->is_current): ?><div class="manager-studio-current-label"><i class="fa-solid fa-thumbtack" aria-hidden="true"></i><?= ui_text('backend/tasks.labels.current', [], false); ?></div><?php endif; ?>
                    <time datetime="<?= esc(str_replace(' ', 'T', $task->created_at), 'attr'); ?>"><i class="fa-regular fa-clock me-1" aria-hidden="true"></i><?= ui_date($task->created_at, 'd MMM · HH:mm'); ?></time>
                    <h3 title="<?= esc($task->title, 'attr'); ?>"><?= esc($dashboardPresenter->preview($task->title)); ?></h3>
                    <?= view(\Modules\Backend\Tasks\Config\Views::STATUS_PARTIAL, ['task' => $task], ['saveData' => false]); ?>
                    <?php if ($task->status === 'completed' || trim((string) $task->next_step_title) !== ''): ?>
                        <div class="manager-studio-next">
                            <i class="fa-solid <?= $task->status === 'completed' ? 'fa-circle-check' : 'fa-arrow-right'; ?>" aria-hidden="true"></i>
                            <div><span class="manager-studio-caption"><?= ui_text($task->status === 'completed' ? 'backend/managerDashboard.finished' : 'backend/tasks.labels.nextStep', [], false); ?></span>
                                <p><?php if ($task->status === 'completed'): ?><?= ui_text('backend/managerDashboard.allStepsDone', [], false); ?><?php else: ?><?= esc($dashboardPresenter->preview($task->next_step_title, 100)); ?><?php endif; ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="manager-studio-task-footer">
                        <div class="manager-studio-progress"><?= view(\Modules\Backend\Tasks\Config\Views::PROGRESS_PARTIAL, ['completedSteps' => $task->completed_count, 'totalSteps' => $task->step_count], ['saveData' => false]); ?></div>
                        <?php if ($dashboardAccess['show'] || $dashboardAccess['edit']): ?>
                            <div class="manager-studio-task-links">
                                <?php if ($dashboardAccess['show']): ?><a href="<?= esc($dashboardPresenter->taskUrl($task), 'attr'); ?>"><i class="fa-regular fa-eye me-1" aria-hidden="true"></i><?= ui_text('backend/tasks.actions.show', [], false); ?></a><?php endif; ?>
                                <?php if ($dashboardAccess['edit']): ?><a href="<?= esc($dashboardPresenter->taskUrl($task, true), 'attr'); ?>"><i class="fa-solid fa-pen me-1" aria-hidden="true"></i><?= ui_text('backend/tasks.actions.edit', [], false); ?></a><?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <?php if (empty($dashboardStudioTasks)): ?><p class="manager-desk-empty"><?= ui_text('backend/studio.noTasksFound', [], false); ?></p><?php endif; ?>
    <?php endif; ?>
    <?php if ($dashboardAccess['notes']): ?>
        <?php $studioNotesLabel = 'backend/managerDashboard.' . ($dashboardStudioNotes === 1 ? 'studioNote' : 'studioNotes'); ?>
        <section class="manager-studio-notebook" aria-labelledby="manager-dashboard-studio-notes-title">
            <div class="manager-studio-notebook-heading">
                <i class="fa-solid fa-note-sticky" aria-hidden="true"></i>
                <h3 id="manager-dashboard-studio-notes-title"><?= ui_text('backend/managerDashboard.studioNotebook', [], false); ?></h3>
                <span class="badge manager-studio-note-count" <?= ui_binding($studioNotesLabel, 'aria-label', [$dashboardStudioNotes], true); ?> aria-label="<?= esc(sprintf(lang($studioNotesLabel), $dashboardStudioNotes), 'attr'); ?>"><?= (int) $dashboardStudioNotes; ?></span>
            </div>
            <a href="<?= esc(base_url('backend/studio/notes'), 'attr'); ?>"><?= ui_text('backend/managerDashboard.openStudioNotes', [], false); ?><i class="fa-solid fa-arrow-right ms-1" aria-hidden="true"></i></a>
        </section>
    <?php endif; ?>
    <div class="manager-studio-footer"><a href="<?= esc(base_url('backend/studio'), 'attr'); ?>"><?= ui_text('backend/managerDashboard.' . ($dashboardAccess['studioTasks'] ? 'viewAllTasks' : 'goStudio'), [], false); ?><i class="fa-solid fa-arrow-right ms-1" aria-hidden="true"></i></a></div>
</aside>
