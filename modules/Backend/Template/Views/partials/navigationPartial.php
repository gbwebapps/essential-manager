<?php
    $navigationControllers = ['dashboard', 'tasks', 'projects', 'applications', 'checklists', 'operations'];
    $navigationItems = [];
    $navigationCounts = \Modules\Backend\Template\Libraries\NavigationClass::counts($currentAdmin ?? null);

    foreach ($menuBottomLeft ?? [] as $item):
        $itemController = $item['controller'] ?? '';
        if ( ! in_array($itemController, $navigationControllers, true)):
            continue;
        endif;
        if (isset($item['permission']) && ! \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, $item['permission'])):
            continue;
        endif;
        $navigationItems[$itemController] = $item;
    endforeach;
?>

<?php if (isset($currentAdmin) && $currentAdmin && $navigationItems !== []): ?>

    <style>
        body:has(#manager-top-navigation) {
            padding-top: var(--manager-navbar-top-height, 3.5rem);
        }

        #manager-top-navigation {
            position: sticky;
            top: var(--manager-navbar-top-height, 3.5rem);
            z-index: 1020;
            display: grid;
            grid-template-columns: repeat(var(--manager-navigation-columns), minmax(0, 1fr));
            gap: 0;
            width: 100%;
            padding: 0;
            margin: 0 0 1rem;
            border-width: 0 0 1px !important;
            border-radius: 0;
            box-shadow: none;
        }

        #manager-top-navigation .manager-navigation-link {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .375rem;
            min-width: 0;
            min-height: 2.75rem;
            margin: 0;
            padding: .375rem .5rem;
            border: 0;
            border-inline-start: 1px solid var(--app-border);
            border-radius: 0;
            color: inherit;
            background: transparent;
            text-decoration: none;
        }

        #manager-top-navigation a.manager-navigation-link:hover,
        #manager-top-navigation a.manager-navigation-link:focus-visible {
            background: color-mix(in srgb, var(--app-surface-inverse) 50%, transparent);
            color: var(--bs-emphasis-color);
        }

        #manager-top-navigation .manager-navigation-link.active {
            cursor: default;
            color: var(--app-text-inverse) !important;
            background: var(--app-surface-inverse);
        }

        /* Il contenitore trasparente degli alert non deve intercettare i link sottostanti. */
        body:has(#manager-top-navigation) #alert-container { pointer-events: none; }
        body:has(#manager-top-navigation) #alert-container > * { pointer-events: auto; }

        #manager-top-navigation .manager-navigation-link:first-child { border-inline-start: 0; }
        #manager-top-navigation .manager-navigation-label {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        #manager-top-navigation .badge,
        #manager-top-navigation [aria-hidden] { flex-shrink: 0; }

        @media (min-width: 576px) and (max-width: 991.98px) {
            #manager-top-navigation {
                grid-template-columns: repeat(var(--manager-navigation-tablet-columns), minmax(0, 1fr));
            }
            #manager-top-navigation .manager-navigation-link:nth-child(3n+1) { border-inline-start: 0; }
        }

        @media (max-width: 575.98px) {
            #manager-top-navigation {
                grid-template-columns: repeat(var(--manager-navigation-mobile-columns), minmax(0, 1fr));
            }
            #manager-top-navigation .manager-navigation-link { font-size: .875rem; gap: .25rem; }
            #manager-top-navigation .manager-navigation-link:nth-child(2n+1) { border-inline-start: 0; }
        }
    </style>

    <nav id="manager-top-navigation" class="bar" style="--manager-navigation-columns: <?= count($navigationItems); ?>; --manager-navigation-tablet-columns: <?= min(3, count($navigationItems)); ?>; --manager-navigation-mobile-columns: <?= min(2, count($navigationItems)); ?>;" aria-label="<?= esc(lang('backend/global.buttons.modules'), 'attr'); ?>">
        <?php foreach ($navigationControllers as $navigationController): ?>
            <?php
                if ( ! isset($navigationItems[$navigationController])):
                    continue;
                endif;
                $item = $navigationItems[$navigationController];
                $isActive = ($controller ?? '') === $navigationController;
                $count = $navigationCounts[$navigationController] ?? null;
            ?>
            <?php if ($isActive): ?>
                <span class="manager-navigation-link active" aria-current="page" aria-disabled="true">
            <?php else: ?>
                <a href="<?= esc(base_url($item['route']), 'attr'); ?>" class="manager-navigation-link">
            <?php endif; ?>
                <span aria-hidden="true"><?= $item['icon'] ?? ''; ?></span>
                <span class="manager-navigation-label"><?= ui_value($item['label'] ?? '', false); ?></span>
                <?php if (is_int($count) && $count >= 0): ?>
                    <span class="badge app-bg-muted-subtle app-text-muted-emphasis border rounded-1" data-navigation-count="<?= esc($navigationController, 'attr'); ?>"><?= $count; ?></span>
                <?php endif; ?>
            <?php if ($isActive): ?>
                </span>
            <?php else: ?>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
    </nav>

    <script>
        (() => {
            const navbar = document.querySelector('#navbar-top-view .navbar');
            if ( ! navbar) return;

            // Mantiene il menu aderente anche quando la navbar cambia altezza.
            const updateNavbarHeight = () => {
                document.documentElement.style.setProperty('--manager-navbar-top-height', `${navbar.getBoundingClientRect().height}px`);
            };

            updateNavbarHeight();
            new ResizeObserver(updateNavbarHeight).observe(navbar);
        })();
    </script>

<?php endif; ?>
