<div class="card mb-0" data-tasks-overview>
    <div class="card-body py-2">
        <div class="d-flex flex-wrap align-items-center gap-2 gap-lg-4">
            <span><?= ui_text('backend/tasks.overview.open', [], false); ?> <strong data-overview-open><?= (int) $taskOverview['open']; ?></strong></span>
            <span><?= ui_text('backend/tasks.overview.inProgress', [], false); ?> <strong><?= (int) $taskOverview['in_progress']; ?></strong></span>
            <span><?= ui_text('backend/tasks.overview.remainingSteps', [], false); ?> <strong data-overview-steps><?= (int) $taskOverview['remaining_steps']; ?></strong></span>
        </div>
        <?php if ($currentTask): ?>
            <div class="small mt-1">
                <?= ui_text('backend/tasks.labels.' . ($currentTask->paused_at !== null ? 'paused' : 'current'), [], false); ?>:
                <a href="<?= esc(base_url('backend/tasks/show/' . $currentTask->uuid), 'attr'); ?>"><?= esc($currentTask->title); ?></a>
                <?php if ($currentTask->next_step): ?> &bull; <?= ui_text('backend/tasks.labels.nextStep', [], false); ?>: <?= esc($currentTask->next_step->title); ?><?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
