<?php declare(strict_types = 1);

namespace App\Filters\Frontend;

use App\Libraries\Frontend\SiteSettings;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Seleziona la lingua pubblica da cookie, browser e impostazioni del sito, in questo ordine.
 */
class LanguageFilter implements FilterInterface
{
    public const COOKIE_NAME = 'frontendLanguage';

    /**
     * Applica soltanto codici presenti nell'elenco globale delle lingue supportate.
     *
     * @param RequestInterface $request Richiesta corrente.
     * @param array|null $arguments Argomenti del filtro.
     * @return null
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        if ( ! $request instanceof IncomingRequest):
            return null;
        endif;

        $supported = config('App')->supportedLocales;
        $default = (new SiteSettings())->language();
        $cookie = $request->getCookie(self::COOKIE_NAME);
        if (is_string($cookie) && in_array($cookie, $supported, true)):
            $language = $cookie;
        else:
            /* Il default sito e il primo candidato quando il browser non offre corrispondenze. */
            $ordered = array_values(array_unique(array_merge([$default], $supported)));
            $candidate = strtolower(str_replace('_', '-', $request->negotiate('language', $ordered)));
            $short = explode('-', $candidate)[0];
            $language = in_array($candidate, $supported, true) ? $candidate
                : (in_array($short, $supported, true) ? $short : $default);
        endif;

        $request->setLocale($language);
        service('request')->setLocale($language);
        service('language')->setLocale($language);
        config('App')->defaultLocale = $language;
        return null;
    }

    /**
     * Non altera la risposta gia prodotta dal controller.
     *
     * @param RequestInterface $request Richiesta corrente.
     * @param ResponseInterface $response Risposta corrente.
     * @param array|null $arguments Argomenti del filtro.
     * @return ResponseInterface|null
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): ?ResponseInterface
    {
        return null;
    }
}
