<?php if ($dashboardCards !== []): ?>
    <div class="dashboard-summaries" style="--dashboard-columns: <?= count($dashboardCards); ?>;">
        <?php foreach ($dashboardCards as $entity => $card): ?>
            <?= view('backend/dashboard/partials/index/summaryPartial', ['entity' => $entity, 'card' => $card, 'dashboardPresenter' => $dashboardPresenter, 'removalCounts' => $dashboardTotals['usersRemoval'] ?? null, 'refreshUrl' => base_url('backend/dashboard/summary/' . rawurlencode($entity))]); ?>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p class="app-text-muted" role="status"><?= ui_text('backend/dashboard.messages.noSections', [], false); ?></p>
<?php endif; ?>

<?php foreach (config(\Config\Backend\Dashboard::class)->analysisViews as $analysisView): ?>
    <?= view($analysisView, ['dashboardRankings' => $dashboardRankings, 'dashboardTotals' => $dashboardTotals, 'dashboardPresenter' => $dashboardPresenter]); ?>
<?php endforeach; ?>
