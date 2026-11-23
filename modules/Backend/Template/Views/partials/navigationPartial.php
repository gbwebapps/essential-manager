<?= view('backend/template/menuTopView', [
    'menuTop' => $menuTop ?? config(\Config\Backend\Menu::class)->top,
    'menuTopCounts' => \Modules\Backend\Template\Libraries\NavigationClass::counts($currentAdmin ?? null),
    'menuTopActive' => $navigationSection ?? $controller ?? '',
    'menuTopId' => 'manager-top-navigation',
    'currentAdmin' => $currentAdmin ?? null,
], ['saveData' => false]); ?>
