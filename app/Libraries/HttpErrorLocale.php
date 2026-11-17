<?php declare(strict_types = 1);

namespace App\Libraries;

use App\Filters\Frontend\LanguageFilter;
use App\Libraries\Backend\InterfacePreferences;
use CodeIgniter\HTTP\IncomingRequest;
use Config\App;
use Config\Backend\General as BackendGeneral;
use Config\Frontend\General as FrontendGeneral;
use Config\Services;
use Config\Session;

/** Risolve la lingua delle sole pagine di errore, anche quando i filtri non sono stati eseguiti. */
class HttpErrorLocale
{
    /**
     * Conserva la lingua già risolta o cerca la preferenza dell'area.
     * Per una 500 usa soltanto i dati già disponibili e i valori dei file di configurazione.
     *
     * @param IncomingRequest $request Richiesta che ha generato l'errore.
     * @param bool $isBackend Indica se il percorso appartiene al backend.
     * @param int $status Codice HTTP della pagina di errore.
     * @return string Codice di una lingua supportata.
     */
    public static function resolve(IncomingRequest $request, bool $isBackend, int $status): string
    {
        $supported = config(App::class)->supportedLocales;
        $locale = $request->getLocale();
        if ($locale !== $request->getDefaultLocale() && in_array($locale, $supported, true)):
            return $locale;
        endif;

        $preference = $isBackend
            ? self::backendPreference($request, $status === 404)
            : $request->getCookie(LanguageFilter::COOKIE_NAME);
        if (is_string($preference) && in_array($preference, $supported, true)):
            return $preference;
        endif;

        $default = $isBackend ? config(BackendGeneral::class)->language : config(FrontendGeneral::class)->language;
        if ($status === 404):
            try {
                helper('settings');
                $stored = setting($isBackend ? 'Backend\General' : 'Frontend\General', 'language');
                if (is_string($stored) && in_array($stored, $supported, true)):
                    $default = $stored;
                endif;
            } catch (\Throwable) {
                /* I valori dei file restano disponibili anche se la persistenza non risponde. */
            }
        endif;

        if ( ! in_array($default, $supported, true)):
            $default = in_array($locale, $supported, true) ? $locale : ($supported[0] ?? 'en');
        endif;
        if ($isBackend):
            return $default;
        endif;

        $ordered = array_values(array_unique([$default, ...$supported]));
        $candidate = strtolower(str_replace('_', '-', $request->negotiate('language', $ordered)));
        $short = explode('-', $candidate)[0];

        return in_array($candidate, $supported, true) ? $candidate
            : (in_array($short, $supported, true) ? $short : $default);
    }

    /**
     * Legge la lingua amministrativa senza scrivere nella sessione o rigenerarne l'identificativo.
     *
     * @param IncomingRequest $request Richiesta che ha generato l'errore.
     * @param bool $canOpenSession Consente di leggere una sessione esistente per le sole 404.
     * @return string|null Preferenza presente nella sessione.
     */
    private static function backendPreference(IncomingRequest $request, bool $canOpenSession): ?string
    {
        $preferences = $_SESSION[InterfacePreferences::SESSION_KEY] ?? null;
        if ($preferences === null && $canOpenSession && session_status() === PHP_SESSION_NONE && ! headers_sent()):
            $config = clone config(Session::class);
            $cookie = $request->getCookie($config->cookieName);
            if (is_string($cookie) && $cookie !== ''):
                $config->timeToUpdate = 0;

                try {
                    $preferences = self::readBackendSession($config);
                } catch (\Throwable) {
                    /* La 404 può essere mostrata anche senza la sessione. */
                }
            endif;
        endif;

        $language = is_array($preferences) ? ($preferences['language'] ?? null) : null;

        return is_string($language) ? $language : null;
    }

    /**
     * Legge una sessione esistente e rilascia il lock senza salvare modifiche.
     *
     * @param Session $config Configurazione usata soltanto per questa lettura.
     * @return mixed Preferenze temporanee dell'interfaccia amministrativa.
     */
    private static function readBackendSession(Session $config): mixed
    {
        try {
            return Services::session($config, false)->get(InterfacePreferences::SESSION_KEY);
        } finally {
            if (session_status() === PHP_SESSION_ACTIVE):
                session_abort();
            endif;
        }
    }
}
