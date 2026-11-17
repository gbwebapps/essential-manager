<?php
$removedLabelKey = $entity === 'users' ? 'backend/users.labels.removed' : 'backend/global.menuCounts.trashed';
$states = $entity === 'messages'
    ? ['unread' => ['backend/messages.statuses.unread', 'suspended'], 'read' => ['backend/messages.statuses.read', 'active'], 'archived' => ['backend/messages.statuses.archived', 'trashed']]
    : ['active' => ['backend/global.menuCounts.active', 'active'], 'suspended' => ['backend/global.menuCounts.suspended', 'suspended'], 'trashed' => [$removedLabelKey, 'trashed']];
$stateBindings = [];
$stateLabels = [];
foreach ($states as $state => [$label, $color]):
    if ($stateBindings !== []):
        $stateBindings[] = ', ';
    endif;
    $stateBindings[] = ['key' => $label];
    $stateBindings[] = ': ' . $card['counts'][$state];
    $stateLabels[] = lang($label) . ': ' . $card['counts'][$state];
endforeach;
?>
<div class="dashboard-summary-body">
    <div class="dashboard-donut<?= $card['total'] === 0 ? ' dashboard-donut-empty' : ''; ?>"
         style="--dashboard-active-end: <?= $card['activeEnd']; ?>%; --dashboard-suspended-end: <?= $card['suspendedEnd']; ?>%;"
         role="img" <?= ui_parts($stateBindings, 'aria-label'); ?> aria-label="<?= esc(implode(', ', $stateLabels), 'attr'); ?>">
        <div class="dashboard-donut-core">
            <span class="dashboard-total"><?= esc($dashboardPresenter->formatCount($card['total'])); ?></span>
            <span class="dashboard-total-label"><?= ui_text('backend/dashboard.labels.total', [], false); ?></span>
        </div>
    </div>
    <dl class="dashboard-states">
        <?php foreach ($states as $state => [$label, $color]): ?>
            <div>
                <dt><span class="dashboard-dot dashboard-state-<?= $color; ?>" aria-hidden="true"></span><?php if ($entity === 'messages'): ?><a class="cmd-link" href="<?= esc(site_url($card['route']) . '?status=' . $state, 'attr'); ?>"><?= ui_text($label, [], false); ?></a><?php else: ?><?= ui_text($label, [], false); ?><?php endif; ?></dt>
                <dd><?= esc($dashboardPresenter->formatCount($card['counts'][$state])); ?></dd>
            </div>
        <?php endforeach; ?>
    </dl>
</div>
<?php if ($entity === 'users'): ?>
    <div class="px-3 pb-3">
        <?php if (is_array($removalCounts ?? null)): ?>
            <dl class="dashboard-states">
                <div>
                    <dt><?= ui_text('backend/users.dashboard.pendingRemoval', [], false); ?></dt>
                    <dd><?= esc($dashboardPresenter->formatCount($removalCounts['pending'])); ?></dd>
                </div>
                <div>
                    <dt><?= ui_text('backend/users.dashboard.dueRemoval', [], false); ?></dt>
                    <dd class="<?= $removalCounts['due'] > 0 ? 'app-text-danger' : ''; ?>"><?= esc($dashboardPresenter->formatCount($removalCounts['due'])); ?></dd>
                </div>
            </dl>
        <?php else: ?>
            <p class="small app-text-muted mb-0"><?= ui_text('backend/users.dashboard.removalUnavailable', [], false); ?></p>
        <?php endif; ?>
    </div>
<?php endif; ?>
