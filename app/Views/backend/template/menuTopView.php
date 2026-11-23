<?php
/**
 * Menu superiore comune, inserito esplicitamente in un hook del layout.
 * Configurare Config\Backend\Menu::$top e includere questa vista, per esempio in afterNavbarTopView.
 * Dati opzionali: menuTop, menuTopCounts, menuTopActive, menuTopId, menuTopAttached e menuTopLabel.
 * Le voci usano label, route, icon, controller, permission, method e, se serve, counter/count.
 * Le applicazioni forniscono voci e conteggi: il componente non interroga modelli applicativi.
 */
    $menuTopItems = [];
    $menuTopCounts = $menuTopCounts ?? $menuCounts ?? [];
    $menuTopActive = $menuTopActive ?? $navigationSection ?? $controller ?? '';
    $menuTopId = $menuTopId ?? 'menu-top-navigation';
    $menuTopAttached = $menuTopAttached ?? true;
    $menuTopFeatures = config(\Config\Backend\Features::class);

    foreach ($menuTop ?? config(\Config\Backend\Menu::class)->top as $item):
        if ( ! $menuTopFeatures->isSectionEnabled($item['controller'] ?? '')): continue; endif;
        if (isset($item['permission']) && ! \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, $item['permission'])): continue; endif;
        $menuTopItems[] = $item;
    endforeach;
?>

<?php if (($currentAdmin ?? null) && $menuTopItems !== []): ?>
    <link rel="stylesheet" href="<?= esc(base_url('assets/css/backend/components/menuTop.css'), 'attr'); ?>">
    <nav id="<?= esc($menuTopId, 'attr'); ?>" class="bar menu-top-navigation" data-menu-top data-menu-top-attached="<?= $menuTopAttached ? 'true' : 'false'; ?>" style="--menu-top-columns: <?= count($menuTopItems); ?>; --menu-top-tablet-columns: <?= min(3, count($menuTopItems)); ?>; --menu-top-mobile-columns: <?= min(2, count($menuTopItems)); ?>;" aria-label="<?= esc($menuTopLabel ?? lang('backend/global.buttons.modules'), 'attr'); ?>">
        <?php foreach ($menuTopItems as $item): ?>
            <?php
                $isActive = $menuTopActive !== '' && ($item['controller'] ?? '') === $menuTopActive;
                $counter = $item['counter'] ?? $item['controller'] ?? '';
                $count = $item['count'] ?? $menuTopCounts[$counter] ?? null;
                $isPost = strtolower($item['method'] ?? 'get') === 'post';
            ?>
            <div class="menu-top-entry">
                <?php if ($isActive): ?>
                    <span class="menu-top-link active" aria-current="page" aria-disabled="true">
                <?php elseif ($isPost): ?>
                    <form method="post" action="<?= esc(base_url($item['route']), 'attr'); ?>" class="menu-top-form">
                        <?= csrf_field(); ?>
                        <button type="submit" class="menu-top-link">
                <?php else: ?>
                    <a href="<?= esc(base_url($item['route']), 'attr'); ?>" class="menu-top-link">
                <?php endif; ?>
                    <span aria-hidden="true"><?= $item['icon'] ?? ''; ?></span>
                    <span class="menu-top-label"><?= ui_value($item['label'] ?? '', false); ?></span>
                    <?php if (is_int($count) && $count >= 0): ?>
                        <span class="badge app-bg-muted-subtle app-text-muted-emphasis border rounded-1" data-navigation-count="<?= esc($counter, 'attr'); ?>"><?= $count; ?></span>
                    <?php endif; ?>
                <?php if ($isActive): ?>
                    </span>
                <?php elseif ($isPost): ?>
                        </button>
                    </form>
                <?php else: ?>
                    </a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </nav>
    <script type="module" src="<?= esc(base_url('assets/js/backend/components/MenuTop.js'), 'attr'); ?>"></script>
<?php endif; ?>
