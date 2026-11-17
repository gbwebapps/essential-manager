<?php declare(strict_types = 1);

namespace App\Libraries\Frontend;

/**
 * Espone le impostazioni effettive del sito e applica il fallback inglese ai contenuti della home.
 */
class SiteSettings
{
    private object $settings;

    /**
     * Legge configurazione e sovrascritture tramite la cache comune dell'helper setting().
     *
     * @return void
     */
    public function __construct()
    {
        helper('settings');
        $this->settings = setting('Frontend\General');
    }

    /**
     * Restituisce il nome del sito senza applicare markup o escaping di presentazione.
     *
     * @return string
     */
    public function name(): string
    {
        $name = trim((string) ($this->settings->siteName ?? ''));
        return $name !== '' ? $name : config(\Config\Frontend\General::class)->siteName;
    }

    /**
     * Seleziona il titolo della home nella lingua corrente oppure in inglese.
     *
     * @param string|null $locale Codice lingua richiesto, oppure quello della richiesta corrente.
     * @return string
     */
    public function title(?string $locale = null): string
    {
        return $this->translated('title', $locale);
    }

    /**
     * Seleziona la descrizione della home nella lingua corrente oppure in inglese.
     *
     * @param string|null $locale Codice lingua richiesto, oppure quello della richiesta corrente.
     * @return string
     */
    public function description(?string $locale = null): string
    {
        return $this->translated('description', $locale);
    }

    /**
     * Restituisce una lingua principale ammessa dall'elenco globale.
     *
     * @return string
     */
    public function language(): string
    {
        $language = $this->settings->language ?? 'en';
        return is_string($language) && in_array($language, config('App')->supportedLocales, true) ? $language : 'en';
    }

    /**
     * Restituisce un fuso valido, senza cambiare il timezone PHP usato dai processi di sicurezza.
     *
     * @return string
     */
    public function timezone(): string
    {
        $timezone = $this->settings->timezone ?? '';
        return is_string($timezone) && in_array($timezone, timezone_identifiers_list(), true)
            ? $timezone : config(\Config\Frontend\General::class)->timezone;
    }

    /**
     * Applica il fallback al singolo testo, senza riempire le traduzioni vuote nel database.
     *
     * @param string $key Campo multilingua ammesso: title oppure description.
     * @param string|null $locale Lingua di destinazione.
     * @return string
     */
    private function translated(string $key, ?string $locale): string
    {
        $locale ??= service('request')->getLocale();
        $values = $this->settings->{$key} ?? [];
        $values = is_array($values) ? $values : [];
        $value = $values[$locale] ?? '';
        if (is_string($value) && trim($value) !== ''):
            return trim($value);
        endif;

        $english = $values['en'] ?? '';
        if (is_string($english) && trim($english) !== ''):
            return trim($english);
        endif;

        return (string) (config(\Config\Frontend\General::class)->{$key}['en'] ?? '');
    }
}
