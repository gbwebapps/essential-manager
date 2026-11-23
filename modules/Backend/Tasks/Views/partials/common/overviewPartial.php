<?php
    $total = (int) $taskOverview['total'];
    $open = (int) $taskOverview['open'];
    $inProgress = (int) $taskOverview['in_progress'];
    $counts = [
        'todo' => ['value' => max(0, $open - $inProgress), 'icon' => 'fa-regular fa-circle'],
        'inProgress' => ['value' => $inProgress, 'icon' => 'fa-solid fa-spinner'],
        'completed' => ['value' => max(0, $total - $open), 'icon' => 'fa-solid fa-circle-check'],
        'total' => ['value' => $total, 'icon' => 'fa-solid fa-layer-group'],
    ];
    $canOpenTask = \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, 'tasks_show');
?>
<div class="card mb-0 task-overview" data-tasks-overview data-current-task="<?= esc($currentTask->uuid ?? '', 'attr'); ?>">
    <div class="task-overview-grid">
        <section class="task-overview-focus" aria-label="<?= esc(lang('backend/tasks.labels.current'), 'attr'); ?>">
            <div class="task-overview-heading">
                <i class="fa-solid fa-thumbtack" aria-hidden="true"></i>
                <span><?= ui_text('backend/tasks.labels.current', [], false); ?></span>
                <?php if ($currentTask): ?>
                    <span class="task-overview-state"><?= ui_text('backend/tasks.statuses.' . $currentTask->status, [], false); ?></span>
                <?php endif; ?>
            </div>
            <?php if ($currentTask): ?>
                <h2 class="task-overview-title" title="<?= esc($currentTask->title, 'attr'); ?>">
                    <?php if ($canOpenTask): ?><a href="<?= esc(base_url(($taskRouteBase ?? 'backend/tasks/') . 'show/' . $currentTask->uuid), 'attr'); ?>"><?php endif; ?>
                    <?= esc(mb_strlen($currentTask->title) > 100 ? mb_substr($currentTask->title, 0, 99) . '…' : $currentTask->title); ?>
                    <?php if ($canOpenTask): ?></a><?php endif; ?>
                </h2>
                <?php if ((int) ($currentTask->is_studio ?? 0) === 1 || trim((string) ($currentTask->project_name ?? '')) !== '' || trim((string) ($currentTask->application_name ?? '')) !== ''): ?>
                    <div class="task-overview-context">
                        <?php if ((int) ($currentTask->is_studio ?? 0) === 1 || trim((string) ($currentTask->project_name ?? '')) !== ''): ?><span><i class="fa-solid fa-<?= (int) ($currentTask->is_studio ?? 0) === 1 ? 'building' : 'folder'; ?> fa-fw" aria-hidden="true"></i> <?= esc((int) ($currentTask->is_studio ?? 0) === 1 ? lang('backend/studio.title') : $currentTask->project_name); ?></span><?php endif; ?>
                        <?php if (trim((string) ($currentTask->application_name ?? '')) !== ''): ?><span><i class="fa-solid fa-desktop fa-fw" aria-hidden="true"></i> <?= esc($currentTask->application_name); ?></span><?php endif; ?>
                    </div>
                <?php endif; ?>
                <?php if ($currentTask->next_step): ?>
                    <div class="task-overview-next">
                        <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                        <div><div class="task-overview-caption"><?= ui_text('backend/tasks.labels.nextStep', [], false); ?></div><div class="task-overview-next-title"><?= esc($currentTask->next_step->title); ?></div></div>
                    </div>
                <?php endif; ?>
                <?php if ((int) $currentTask->step_count > 0 || $canOpenTask): ?>
                    <div class="task-overview-footer">
                        <?php if ((int) $currentTask->step_count > 0): ?>
                            <div class="task-overview-progress"><span class="task-overview-caption"><?= ui_text('backend/tasks.labels.steps', [], false); ?></span><?= view(\Modules\Backend\Tasks\Config\Views::PROGRESS_PARTIAL, ['completedSteps' => $currentTask->completed_count, 'totalSteps' => $currentTask->step_count], ['saveData' => false]); ?></div>
                        <?php endif; ?>
                        <?php if ($canOpenTask): ?><a class="btn btn-sm app-btn-secondary text-nowrap" href="<?= esc(base_url(($taskRouteBase ?? 'backend/tasks/') . 'show/' . $currentTask->uuid), 'attr'); ?>"><i class="fa-solid fa-arrow-up-right-from-square me-1" aria-hidden="true"></i><?= ui_text('backend/tasks.overview.openTask', [], false); ?></a><?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <h2 class="task-overview-title"><?= ui_text('backend/tasks.overview.noCurrent', [], false); ?></h2>
                <?php if ($canOpenTask && \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, 'tasks_set_current')): ?>
                    <p class="task-overview-empty mb-0"><?= ui_text('backend/tasks.overview.noCurrentHint', [lang('backend/tasks.actions.work')], true); ?></p>
                <?php endif; ?>
            <?php endif; ?>
        </section>
        <section class="task-overview-summary" aria-label="<?= esc(lang('backend/tasks.overview.summary'), 'attr'); ?>">
            <h2 class="task-overview-summary-title"><i class="fa-solid fa-chart-simple me-2" aria-hidden="true"></i><?= ui_text('backend/tasks.overview.summary', [], false); ?></h2>
            <div class="task-overview-counts">
                <?php foreach ($counts as $key => $count): ?>
                    <div class="task-overview-count" data-overview-count="<?= esc($key, 'attr'); ?>">
                        <i class="<?= $count['icon']; ?>" aria-hidden="true"></i>
                        <strong><?= $count['value']; ?></strong>
                        <span><?= ui_text('backend/tasks.overview.' . $key, [], false); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="task-overview-remaining"><i class="fa-solid fa-list-check me-1" aria-hidden="true"></i><strong data-overview-steps><?= (int) $taskOverview['remaining_steps']; ?></strong> <?= ui_text('backend/tasks.overview.remainingSteps', [], false); ?></div>
        </section>
    </div>
</div>
