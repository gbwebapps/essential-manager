<?php declare(strict_types = 1);

namespace App\Filters\Frontend;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/** Disabilita l'area Users senza richiedere le sue tabelle o modificare il backend. */
class UsersEnabledFilter implements FilterInterface
{
    /**
     * Blocca le rotte Users quando la funzionalita non e abilitata.
     *
     * @param \CodeIgniter\HTTP\RequestInterface $request Richiesta HTTP corrente.
     * @param mixed $arguments Argomenti opzionali del filtro.
     * @return \CodeIgniter\HTTP\ResponseInterface|null
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        if ( ! config(\Config\Frontend\Auth::class)->enabled):
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        endif;
        return null;
    }

    /**
     * Conclude il filtro senza alterare la risposta HTTP.
     *
     * @param \CodeIgniter\HTTP\RequestInterface $request Richiesta HTTP corrente.
     * @param \CodeIgniter\HTTP\ResponseInterface $response Risposta HTTP corrente.
     * @param mixed $arguments Argomenti opzionali del filtro.
     * @return \CodeIgniter\HTTP\ResponseInterface|null
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): ?ResponseInterface
    {
        return null;
    }
}
