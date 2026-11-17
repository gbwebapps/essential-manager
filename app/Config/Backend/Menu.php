<?php declare(strict_types = 1); 

namespace Config\Backend;

use CodeIgniter\Config\BaseConfig;

/**
 * Gestisce la configurazione e la struttura dei menu di navigazione del pannello di controllo.
 */
class Menu extends BaseConfig
{
    /**
     * Elenco delle voci di menu posizionate nell'area superiore destra
     * 
     * @var array 
     */
    public array $topRight = [];

    /**
     * Elenco delle voci di menu posizionate nell'area inferiore sinistra
     * 
     * @var array 
     */
    public array $bottomLeft = [];

    /**
     * Elenco delle voci di menu posizionate nell'area inferiore destra
     * 
     * @var array 
     */
    public array $bottomRight = [];

    /** Voci dei moduli da anteporre alle sezioni del menu. */
    public array $prepend = [];

    /** Tabelle abilitate ai badge e permessi di consultazione delle rispettive liste. */
    public array $counterPermissions = [
        'admins' => null,
        'users' => 'users_show_all',
        'messages' => 'messages_show_all',
    ];

    /**
     * Inizializza la configurazione dei menu popolando le sezioni di navigazione con le rispettive voci localizzate e rotte.
     * 
     */
    public function __construct()
    {
        $this->topRight = [
            ['label' => lang('backend/global.menu.topRight.admins'), 'route' => 'backend/admins/showAll', 'icon' => '<i class="fa-solid fa-users"></i>', 'controller' => 'admins'],
            ['label' => lang('backend/admintransfer.titles.index'), 'route' => 'backend/admintransfer', 'icon' => '<i class="fa-solid fa-users-gear"></i>', 'controller' => 'admintransfer'],
            ['label' => lang('backend/global.menu.topRight.groups'), 'route' => 'backend/groups', 'icon' => '<i class="fa-solid fa-user-shield"></i>', 'controller' => 'groups'],
            ['label' => lang('backend/global.menu.topRight.audits'), 'route' => 'backend/audits', 'icon' => '<i class="fa-solid fa-clock-rotate-left"></i>', 'controller' => 'audits'],
            ['label' => lang('backend/global.menu.topRight.tokens'), 'route' => 'backend/tokens', 'icon' => '<i class="fa-solid fa-chain"></i>', 'controller' => 'tokens'],
            ['label' => lang('backend/global.menu.topRight.logs'), 'route' => 'backend/logs', 'icon' => '<i class="fa-solid fa-user-clock"></i>', 'controller' => 'logs'],
            ['label' => lang('backend/global.menu.topRight.account'), 'route' => 'backend/account', 'icon' => '<i class="fa-solid fa-user-gear"></i>', 'controller' => 'account'],
            ['label' => lang('backend/global.menu.topRight.logout'), 'route' => 'backend/auth/logout', 'icon' => '<i class="fa-solid fa-right-to-bracket"></i>', 'controller' => 'auth', 'method' => 'post'],
        ];

        $this->bottomLeft = [
            ['label' => lang('backend/global.menu.bottomLeft.dashboard'), 'route' => 'backend/dashboard', 'icon' => '<i class="fa-solid fa-gauge"></i>', 'controller' => 'dashboard'],
            ['label' => lang('backend/users.titles.showAll'), 'route' => 'backend/users/showAll', 'icon' => '<i class="fa-solid fa-user-group"></i>', 'controller' => 'users', 'permission' => 'users_show_all'],
            ['label' => lang('backend/messages.titles.showAll'), 'route' => 'backend/messages/showAll', 'icon' => '<i class="fa-solid fa-envelope"></i>', 'controller' => 'messages', 'permission' => 'messages_show_all'],
        ];

        $this->bottomRight = [
            ['label' => lang('backend/settingsBackend.titles.index'), 'route' => 'backend/settingsBackend', 'icon' => '<i class="fa-solid fa-sliders"></i>', 'controller' => 'settingsBackend'],
            ['label' => lang('backend/settingsFrontend.titles.index'), 'route' => 'backend/settingsFrontend', 'icon' => '<i class="fa-solid fa-globe"></i>', 'controller' => 'settingsFrontend'],
            ['label' => lang('backend/global.menu.bottomRight.tools'), 'route' => 'backend/tools', 'icon' => '<i class="fa-solid fa-screwdriver-wrench"></i>', 'controller' => 'tools'],
        ];

        parent::__construct();

        foreach (['topRight', 'bottomLeft', 'bottomRight'] as $section):
            $this->{$section} = array_merge($this->prepend[$section] ?? [], $this->{$section});
        endforeach;
    }
}
