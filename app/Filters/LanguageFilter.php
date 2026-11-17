<?php declare(strict_types = 1);

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Instrada la localizzazione verso le due aree senza condividere le preferenze del visitatore con gli admin.
 */
class LanguageFilter implements FilterInterface
{
    /**
     * Delega la lingua al filtro dell'area corrispondente prima di eseguire i controller.
     *
     * @param RequestInterface $request Richiesta corrente.
     * @param array|null $arguments Argomenti del filtro.
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        if ( ! $request instanceof IncomingRequest):
            return null;
        endif;

        $filter = self::isBackendRequest($request)
            ? new \App\Filters\Backend\LanguageFilter()
            : new \App\Filters\Frontend\LanguageFilter();
        return $filter->before($request, $arguments);
    }

    /**
     * Riconosce il prefisso backend anche nelle installazioni ospitate sotto una sottocartella.
     *
     * @param IncomingRequest $request Richiesta HTTP da classificare.
     * @return bool
     */
    public static function isBackendRequest(IncomingRequest $request): bool
    {
        $path = trim($request->getUri()->getPath(), '/');
        $base = trim((string) parse_url(config('App')->baseURL, PHP_URL_PATH), '/');
        if ($base !== '' && ($path === $base || str_starts_with($path, $base . '/'))):
            $path = ltrim(substr($path, strlen($base)), '/');
        endif;

        return $path === 'backend' || str_starts_with($path, 'backend/');
    }

    /**
     * La localizzazione e completata prima del controller e non modifica la risposta.
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
