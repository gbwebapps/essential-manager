<?php
    $totalSteps = max(0, (int) $totalSteps);
    $completedSteps = min($totalSteps, max(0, (int) $completedSteps));
    $progressPercent = $totalSteps > 0 ? (int) round($completedSteps * 100 / $totalSteps) : 0;
?>
<?php if ($totalSteps === 0): ?>
<span class="small app-text-muted" data-no-steps><?= ui_text('backend/tasks.labels.noSteps', [], false); ?></span>
<?php else: ?>
<span class="task-steps-progress" data-steps-complete="<?= $totalSteps > 0 && $completedSteps === $totalSteps ? 'true' : 'false'; ?>">
    <span class="progress" role="progressbar" aria-label="<?= esc(lang('backend/tasks.labels.steps'), 'attr'); ?>" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= $progressPercent; ?>" aria-valuetext="<?= esc(sprintf(lang('backend/tasks.labels.stepsCount'), $completedSteps, $totalSteps), 'attr'); ?>">
        <span class="progress-bar" style="width: <?= $progressPercent; ?>%;"></span>
    </span>
    <span class="task-steps-count" aria-hidden="true"><?= ui_text('backend/tasks.labels.stepsCount', [$completedSteps, $totalSteps], false); ?></span>
</span>

<?php endif; ?>
