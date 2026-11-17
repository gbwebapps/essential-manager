<article class="dashboard-card" data-dashboard-summary="<?= esc($entity, 'attr'); ?>" aria-labelledby="dashboard-<?= esc($entity, 'attr'); ?>-title">
    <header class="dashboard-card-header">
        <h2 id="dashboard-<?= esc($entity, 'attr'); ?>-title"><i class="fa-solid <?= esc($card['icon'], 'attr'); ?>" aria-hidden="true"></i> <?= ui_text($card['label'], [], true); ?></h2>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <?php if ($entity === 'admins'): ?>
                <span class="dashboard-role"><?= ui_text('backend/dashboard.labels.superadminOnly', [], false); ?></span>
            <?php endif; ?>
            <?php if (isset($refreshUrl)): ?>
                <button type="button" class="btn btn-sm app-btn-secondary" data-dashboard-refresh="<?= esc($refreshUrl, 'attr'); ?>">
                    <?= view('components/panelReloadIcon', []); ?><?= ui_text('backend/admins.buttons.reload', [], false); ?>
                </button>
            <?php endif; ?>
        </div>
    </header>
    <div class="d-flex flex-column flex-grow-1" data-dashboard-summary-content>
        <?= view('backend/dashboard/partials/index/summaryContentPartial', ['entity' => $entity, 'card' => $card, 'dashboardPresenter' => $dashboardPresenter, 'removalCounts' => $removalCounts ?? null]); ?>
    </div>
    <?php if (isset($refreshUrl)): ?>
        <p class="d-none app-text-danger small px-3 pb-3 mb-0" data-dashboard-refresh-error role="alert"><?= ui_text('backend/dashboard.messages.unavailable', [], false); ?></p>
    <?php endif; ?>
    <a <?= ui_parts([['key' => 'backend/dashboard.links.showAll'], ': ', ['key' => $card['label']]], 'aria-label'); ?> class="dashboard-card-footer" href="<?= esc(base_url($card['route']), 'attr'); ?>" aria-label="<?= esc(lang('backend/dashboard.links.showAll') . ': ' . lang($card['label']), 'attr'); ?>">
        <span><?= ui_text('backend/dashboard.links.showAll', [], false); ?></span><i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
    </a>
</article>
