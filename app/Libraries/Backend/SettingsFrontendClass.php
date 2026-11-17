<?php declare(strict_types = 1);

namespace App\Libraries\Backend;

use App\Libraries\LanguageOptions;

/**
 * Prepara opzioni e asset della sola sezione Settings Frontend.
 */
class SettingsFrontendClass
{
    /**
     * Restituisce le lingue definite nell'unico elenco globale dell'applicazione.
     *
     * @return array<string, string>
     */
    public function getLanguages(): array
    {
        return LanguageOptions::all('native');
    }

    /**
     * Restituisce i fusi orari nativi ammessi dal server e dal validatore.
     *
     * @return array<string>
     */
    public function getTimezones(): array
    {
        return timezone_identifiers_list();
    }
}
