<?php if (isset($menuCounts[$menuCountEntity])): ?>
    <span class="d-inline-flex flex-shrink-0 gap-1 ms-auto align-middle" data-menu-counts="<?= esc($menuCountEntity, 'attr'); ?>">
        <?php $colors = $menuCountEntity === 'messages'
            ? ['read' => 'app-bg-success', 'unread' => 'app-bg-warning', 'archived' => 'messages-status-archived']
            : ['active' => 'app-bg-success', 'suspended' => 'app-bg-warning', 'trashed' => 'app-bg-danger']; ?>
        <?php foreach ($colors as $countType => $countColor): ?>
            <?php $countLabelKey = match (true) {
                $menuCountEntity === 'messages' => 'backend/messages.statuses.' . $countType,
                $menuCountEntity === 'users' && $countType === 'trashed' => 'backend/users.labels.removed',
                default => 'backend/global.menuCounts.' . $countType,
            }; ?>
            <?php $countLabel = lang($countLabelKey); ?>
            <span <?= ui_binding($countLabelKey, 'data-label'); ?> <?= ui_binding($countLabelKey, 'title', [], false, ': ' . $menuCounts[$menuCountEntity][$countType]); ?> <?= ui_binding($countLabelKey, 'aria-label', [], false, ': ' . $menuCounts[$menuCountEntity][$countType]); ?> class="badge <?= $countColor; ?> rounded-1" data-menu-count="<?= $countType; ?>" data-label="<?= esc($countLabel, 'attr'); ?>" title="<?= esc($countLabel . ': ' . $menuCounts[$menuCountEntity][$countType], 'attr'); ?>" aria-label="<?= esc($countLabel . ': ' . $menuCounts[$menuCountEntity][$countType], 'attr'); ?>"><?= esc($menuCounts[$menuCountEntity][$countType]); ?></span>
        <?php endforeach; ?>
    </span>
<?php endif; ?>
