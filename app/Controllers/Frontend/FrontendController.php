<?php declare(strict_types = 1);

namespace App\Controllers\Frontend;

use App\Controllers\BaseController;
use App\Models\Frontend\Components\GalleryOneModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/** Base dell'area clienti: identita, viste e risposte separate dal pannello amministrativo. */
abstract class FrontendController extends BaseController
{
    protected $helpers = ['date', 'array', 'cookie', 'settings', 'interface', 'user_audits'];
    protected array $data = [];
    protected ?object $currentUser = null;
    protected \App\Libraries\RegExp $regexp;

    /**
     * Inizializza il controller e prepara i dati della sezione.
     *
     * @param \CodeIgniter\HTTP\RequestInterface $request Richiesta HTTP corrente.
     * @param \CodeIgniter\HTTP\ResponseInterface $response Risposta HTTP corrente.
     * @param \Psr\Log\LoggerInterface $logger Servizio di registrazione degli errori.
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        $this->regexp = service('regexp');
        if (config(\Config\Frontend\Auth::class)->enabled):
            $this->currentUser = service('userAuthorization')->currentUser();
        endif;
        $this->data['currentUser'] = $this->currentUser;

        $siteSettings = new \App\Libraries\Frontend\SiteSettings();
        $this->data['siteName'] = $siteSettings->name();
        $this->data['siteTitle'] = $siteSettings->title();
        $this->data['siteDescription'] = $siteSettings->description();
        $this->data['frontendLanguages'] = \App\Libraries\LanguageOptions::all('native');
    }

    /**
     * Compila una pagina frontend autonoma con i dati comuni e della sezione.
     *
     * @param string $view Percorso della vista frontend da compilare.
     * @param array $data Dati da inviare alla vista o nella risposta.
     * @return string
     */
    protected function render(string $view, array $data = []): string
    {
        $data = array_merge($this->data, $data);
        if ($this->currentUser !== null):
            $data['avatarUrl'] = model(GalleryOneModel::class)->getCoverUrl((string) $this->currentUser->uuid);
        endif;
        $this->response->setHeader('Cache-Control', 'no-store');
        return view($view, $data);
    }

    /**
     * Restituisce il risultato JSON con il token CSRF aggiornato e senza cache.
     *
     * @param array $data Dati da inviare alla vista o nella risposta.
     * @param int $statusCode Codice HTTP della risposta.
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    protected function jsonResponse(array $data, int $statusCode = 200): ResponseInterface
    {
        $data['csrfName'] = csrf_token();
        $data['csrfHash'] = csrf_hash();
        return $this->response->setHeader('Cache-Control', 'no-store')->setStatusCode($statusCode)->setJSON($data);
    }

    /**
     * Le operazioni del cliente verificano la sua password, mai quella di un amministratore.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface|null
     */
    protected function requireCurrentPassword(): ?ResponseInterface
    {
        if ($this->currentUser === null):
            return $this->jsonResponse(['result' => 'no_current_user_logged']);
        endif;
        if ( ! setting('Frontend\Auth')->sensitivePasswordConfirmation):
            return null;
        endif;
        $password = $this->request->getPost('currentPassword');
        if ( ! is_string($password) || ! model(\App\Models\Frontend\AccountModel::class)->verifyCurrentPassword($this->currentUser->uuid, $password)):
            $this->response->setHeader('X-Current-Password-Required', '1');
            return $this->jsonResponse(['result' => false, 'errors' => ['currentPassword' => lang('frontend/account.errors.currentPasswordInvalid')], 'message' => lang('frontend/account.messages.validationErrors')]);
        endif;
        return null;
    }
}
