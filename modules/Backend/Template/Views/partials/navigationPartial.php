<?php
    $navigationControllers = ['dashboard', 'tasks', 'projects', 'applications', 'notes', 'checklists', 'operations'];
    $navigationItems = [];
    if (\App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, 'tasks_show_all') && ! isset($navigationCounts['tasks'])):
        $canReadProjects = \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin, 'projects_show_all') || \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin, 'projects_show') || \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin, 'projects_edit');
        $canReadApplications = \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin, 'applications_show_all') || \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin, 'applications_show') || \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin, 'applications_edit');
        $navigationCounts['tasks'] = model(\Modules\Backend\Tasks\Models\TasksModel::class)->activeCount($canReadProjects, $canReadApplications);
    endif;

    foreach (['projects'=>\Modules\Backend\Projects\Models\ProjectsModel::class, 'applications'=>\Modules\Backend\Applications\Models\ApplicationsModel::class] as $countController=>$countModel):
        if (\App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null,$countController.'_show_all') && ! isset($navigationCounts[$countController])):
            $navigationCounts[$countController]=model($countModel)->activeCount();
        endif;
    endforeach;


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
            grid-template-columns: repeat(auto-fit, minmax(8rem, 1fr));
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
            gap: .5rem;
            min-height: 2.75rem;
            margin: 0;
            padding: 0;
            border: 0;
            border-radius: 0;
            color: inherit;
            background: transparent;
            text-decoration: none;
        }

        #manager-top-navigation a.manager-navigation-link:hover,
        #manager-top-navigation a.manager-navigation-link:focus-visible {
            background: var(--app-surface-muted);
        }

        #manager-top-navigation .manager-navigation-link.active {
            cursor: default;
            color: var(--app-text-inverse) !important;
            background: var(--app-surface-inverse);
        }

        @media (max-width: 767.98px) {
            #manager-top-navigation {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 479.98px) {
            #manager-top-navigation {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
    </style>

    <nav id="manager-top-navigation" class="bar" aria-label="<?= esc(lang('backend/global.buttons.modules'), 'attr'); ?>">
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
                <span><?= ui_value($item['label'] ?? '', false); ?></span>
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
