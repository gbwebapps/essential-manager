<?php declare(strict_types = 1);

namespace Config\Backend;

use CodeIgniter\Config\BaseConfig;

/** Configura le sezioni della dashboard e i provider delle analisi di dominio. */
class Dashboard extends BaseConfig
{
    public const RANKING_LIMIT = 5;

    public const SECTIONS = [
        'admins' => ['label' => 'backend/global.menu.topRight.admins', 'icon' => 'fa-users', 'route' => 'backend/admins/showAll'],
        'users' => ['label' => 'backend/users.titles.showAll', 'icon' => 'fa-user-group', 'route' => 'backend/users/showAll', 'permission' => 'users_show_all'],
        'messages' => ['label' => 'backend/messages.titles.showAll', 'icon' => 'fa-envelope', 'route' => 'backend/messages/showAll', 'permission' => 'messages_show_all'],
    ];

    public const RANKINGS = [
    ];

    public const TOTALS = [
        'usersRemoval' => [
            'requires' => ['users'],
            'model' => \App\Models\Backend\UsersModel::class,
            'method' => 'getRemovalCounts',
        ],
    ];

    public array $sections = self::SECTIONS;
    public array $rankings = self::RANKINGS;
    public array $totals = self::TOTALS;
    public array $analysisViews = [];
}
