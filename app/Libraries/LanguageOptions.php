<?php declare(strict_types = 1);

namespace App\Libraries;

/**
 * Presenta l'unico elenco globale di lingue definito in Config\App::supportedLocales.
 */
class LanguageOptions
{
    /**
     * Costruisce le etichette senza mantenere copie dei codici lingua nelle singole sezioni.
     *
     * @param string|null $locale Lingua delle etichette; native usa il nome di ciascun idioma.
     * @return array<string, string>
     */
    public static function all(?string $locale = null): array
    {
        $languages = [];
        $locale ??= service('request')->getLocale();

        foreach (config('App')->supportedLocales as $code):
            $name = \Locale::getDisplayLanguage($code, $locale === 'native' ? $code : $locale);
            $languages[$code] = $name !== false ? mb_convert_case($name, MB_CASE_TITLE, 'UTF-8') : strtoupper($code);
        endforeach;

        return $languages;
    }
}
