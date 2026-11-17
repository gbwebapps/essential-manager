<?php declare(strict_types = 1); 

namespace Config\Backend;

use CodeIgniter\Config\BaseConfig;

/**
 * Gestisce la configurazione e la mappatura dei permessi di accesso alle sezioni e funzionalità del backend.
 */
class Permissions extends BaseConfig
{
	/**
	 * Elenco dei permessi di sistema configurati
	 * 
	 * @var array 
	 */
	public array $permissions = [];

	/** Cataloghi dei permessi forniti dai moduli nella lingua della richiesta. */
	public array $permissionProviders = [];

	/** Permessi delle gallerie registrati dai moduli applicativi. */
	public array $galleryPermissions = [];

	/**
	 * Inizializza la configurazione dei permessi di accesso.
	 */
	public function __construct()
    {
        parent::__construct();
	}

	/**
	 * Restituisce la struttura completa dei permessi suddivisi per controller e relative azioni consentite.
	 *
	 * @return array Array multidimensionale contenente i titoli, i controller, le icone e l'elenco dei permessi localizzati
	 */
	public function getPermissions(): array
	{
		$permissions = array_merge($this->permissions, [
			[
				'title' => lang('backend/permissions.messages.title'),
				'controller' => 'messages',
				'icon' => '<i class="fa-solid fa-envelope"></i>',
				'perms' => [
					'messages_show_all' => lang('backend/permissions.messages.showAll'),
					'messages_show' => lang('backend/permissions.messages.show'),
					'messages_mark' => lang('backend/permissions.messages.mark'),
					'messages_export' => lang('backend/permissions.messages.export'),
					'messages_print' => lang('backend/permissions.messages.print'),
				],
			],
			[
				'title' => lang('backend/permissions.users.title'),
				'controller' => 'users',
				'icon' => '<i class="fa-solid fa-user-group"></i>',
				'perms' => [
					'users_show' => lang('backend/permissions.users.show'),
					'users_show_all' => lang('backend/permissions.users.showAll'),
					'users_restore' => lang('backend/permissions.users.restore'),
					'users_delete' => lang('backend/permissions.users.delete'),
					'users_change_status' => lang('backend/permissions.users.changeStatus'),
					'users_reset_password' => lang('backend/permissions.users.resetPassword'),
					'users_delete_token' => lang('backend/permissions.users.deleteToken'),
					'users_export' => lang('backend/permissions.users.export'),
					'users_print' => lang('backend/permissions.users.print'),
				],
			],
		]);

		foreach ($this->permissionProviders as $provider):
			$permissions = array_merge($permissions, $provider());
		endforeach;

		return $permissions;
	}
}
