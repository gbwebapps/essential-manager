<?php declare(strict_types = 1);

namespace App\Libraries\Backend;


/**
 * Prepara risorse e collegamenti della sezione amministrativa Users.
 */
class UsersClass
{
	/**
	 * Fornisce gli script del calendario e della relativa localizzazione per la lista utenti.
	 *
	 * @return array
	 */
	public function getJsShowAll(): array
	{
		$lang = service('request')->getLocale();
		$locale = ($lang === 'en' ? 'default' : $lang);
		
	    return [
	        ['id' => 'flatpickr-js', 'path' => 'assets/vendor/flatpickr/js/flatpickr.min.js', 'position' => 'before', 'target' => 'users-js'], 
	        ['id' => $locale . '-js', 'path' => 'assets/vendor/flatpickr/js/' . $locale . '.js', 'position' => 'after', 'target' => 'flatpickr-js']
	    ];
	}

	/**
	 * Fornisce gli stili del calendario e delle richieste utenti scadute nella lista.
	 *
	 * @return array
	 */
	public function getCssShowAll(): array
	{
	    return [
	        ['id' => 'flatpickr-css', 'path' => 'assets/vendor/flatpickr/css/flatpickr.min.css', 'position' => 'before', 'target' => 'backend-css'],
	        ['id' => 'users-css', 'path' => 'assets/css/backend/users.css', 'position' => 'after', 'target' => 'backend-css']
	    ];
	}

    /** Loads the PDF exporter for authorized users. */
    public function getJsShow(): array
    {
        if ( ! AuthorizationClass::allows(service('authorization')->currentAdmin(), 'users_export')):
            return [];
        endif;
        return [
            ['id' => 'html2pdf-js', 'path' => 'assets/vendor/html2pdf/js/html2pdf.bundle.min.js', 'position' => 'before', 'target' => 'users-js'],
        ];
    }

    /**
     * Prepara il collegamento di ritorno dal dettaglio alla lista utenti.
     *
     * @param string|null $uuid UUID del cliente o del proprietario delle immagini.
     * @return array
     */
    public function getLinksBarShow(?string $uuid = null): array
    {
        if ( ! AuthorizationClass::allows(service('authorization')->currentAdmin(), 'users_show_all')):
            return [];
        endif;

        return [['icon' => '<i class="fa-solid fa-users"></i>', 'label' => lang('backend/users.linksBar.showAll'), 'route' => 'backend/users/showAll']];
    }
}
