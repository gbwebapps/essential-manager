<?php declare(strict_types = 1);

namespace App\Controllers\Frontend;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use App\Models\Frontend\AuthModel;

/**
 * Gestisce accesso, registrazione, attivazione e recupero credenziali dei clienti.
 */
class AuthController extends FrontendController
{
    protected AuthModel $authModel;

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
        $this->authModel = model(AuthModel::class);
        $this->data['controller'] = 'auth';
    }

    /**
     * Mostra la pagina iniziale della sezione o reindirizza al relativo accesso.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function index()
    {
        return redirect()->to(base_url('auth/login'));
    }

    /**
     * La registrazione delega al model validazione, persistenza e invio dell'attivazione.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface|string
     */
    public function register(): string|ResponseInterface
    {
        if ( ! config(\Config\Frontend\Auth::class)->registration):
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        endif;
        if ($this->request->isAJAX() && $this->request->is('post')):
            $posts = $this->request->getPost();
            if ( ! $this->validateData($posts, $this->authModel->registerValidationRules())):
                return $this->jsonResponse(['result' => false, 'errors' => $this->validator->getErrors(), 'message' => lang('frontend/auth.messages.validationErrors')]);
            endif;
            return $this->jsonResponse($this->authModel->register($posts, $this->request));
        endif;
        $this->data['action'] = 'register';
        $this->data['title'] = lang('frontend/auth.titles.register');
        $this->data['icon'] = '<i class="fa-solid fa-user-plus"></i>';
        return $this->render('frontend/auth/registerView', $this->data);
    }
    /**
     * Mostra il form di accesso o verifica le credenziali inviate.
     *
     * @return string|\CodeIgniter\HTTP\ResponseInterface
     */
    public function login()
    {
        if ($this->request->isAJAX() && $this->request->is('post')):

            $posts = $this->request->getPost();
            $rules = $this->authModel->validateLoginRules();

            if (! $this->validateData($posts, $rules)):
                return $this->jsonResponse(['errors' => $this->validator->getErrors(), 'message' => lang('frontend/auth.messages.validationErrors')]);
            endif;

            $json = $this->authModel->login($posts, $this->request);

            if (in_array($json['result'] ?? false, [true, '2fa_required', 'restore_required'], true)):
                session()->remove('frontend_intended_url');
            endif;

            return $this->jsonResponse($json);

        endif;

        $this->authModel->cancelRemovalConfirmation();
        $this->data['action'] = 'login';
        
        $this->data['title'] = lang('frontend/auth.titles.login');
        $this->data['icon'] = '<i class="fa-solid fa-right-to-bracket"></i>';

        return $this->render('frontend/auth/loginView', $this->data);
    }

    /**
     * Avvia il recupero credenziali e restituisce il risultato dell'operazione.
     *
     * @return string|\CodeIgniter\HTTP\ResponseInterface
     */
    public function resetPassword()
    {
        if($this->request->isAJAX() && $this->request->is('post')):

            $posts = $this->request->getPost();
            $rules = $this->authModel->validateResetPasswordRules();

            if( ! $this->validateData($posts, $rules)):
                return $this->jsonResponse(['errors' => $this->validator->getErrors(), 'message' => lang('frontend/auth.messages.validationErrors')]);
            endif;

            $json = $this->authModel->resetPassword($posts, $this->request);

            /* Restituisce l'ok al Javascript */
            return $this->jsonResponse($json);

        endif;

        $this->data['action'] = 'resetPassword';
        
        $this->data['title'] = lang('frontend/auth.titles.resetPassword');
        $this->data['icon'] = '<i class="fa-solid fa-unlock"></i>';

        return $this->render('frontend/auth/resetPasswordView', $this->data);
    }

    /**
     * Mostra il form associato al token o salva la nuova password validata.
     *
     * @param string|null $token Token personale di attivazione o recupero credenziali.
     * @return string|\CodeIgniter\HTTP\ResponseInterface
     */
    public function setPassword(?string $token = null)
    {
        if($this->request->isAJAX() && $this->request->is('post')):

            $posts = $this->request->getPost();
            $rules = $this->authModel->validateSetPasswordRules();

            if( ! $this->validateData($posts, $rules)):
                return $this->jsonResponse(['errors' => $this->validator->getErrors(), 'message' => lang('frontend/auth.messages.validationErrors')]);
            endif;

            $json = $this->authModel->setPassword($posts);

            /* Restituisce l'ok al Javascript */
            return $this->jsonResponse($json);

        endif;

        if($token && $this->authModel->checkAuthToken($token)):
        
            $this->data['action'] = 'setPassword';
            
            $this->data['title'] = lang('frontend/auth.titles.setPassword');
            $this->data['icon'] = '<i class="fa-solid fa-key"></i>';

            $this->data['token'] = $token;

            return $this->render('frontend/auth/setPasswordView', $this->data);

        endif;

        return redirect()->to('auth')->with('class', 'app-alert-danger fw-bold')->with('message', lang('frontend/auth.messages.checkAuthError'))->with('icon', '<i class="fa-solid fa-triangle-exclamation"></i>');
    }

    /**
     * Mostra il secondo passaggio di accesso o verifica il codice 2FA inviato.
     *
     * @return string|\CodeIgniter\HTTP\ResponseInterface
     */
    public function verify()
    {
        /* SBARRAMENTO GLOBALE: Se la feature 2FA è spenta, la rotta non esiste */
        if ( ! setting('Frontend\Auth')->twoFactor):
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        endif;

        if ($this->request->isAJAX() && $this->request->is('post')):

            $posts = $this->request->getPost();
            $rules = $this->authModel->validateVerifyRules();

            if ( ! $this->validateData($posts, $rules)):
                return $this->jsonResponse(['errors' => $this->validator->getErrors(), 'message' => lang('frontend/auth.messages.validationErrors')]);
            endif;

            /* Il controllo sul database, l'eventuale blocco brute-force e la pulizia della 
               sessione in caso di successo avverranno tutti atomicamente dentro il Model */
            $json = $this->authModel->verify($posts, $this->request);

            return $this->jsonResponse($json);

        endif;

        $pending = session()->get('frontend_auth_2fa_pending');

        if (empty($pending)):
            return redirect()->to(base_url('auth'));
        endif;

        $this->data['action'] = 'verify';

        $this->data['title'] = lang('frontend/auth.titles.verify');
        $this->data['icon'] = '<i class="fa-solid fa-mobile-screen"></i>';

		$method = (string) ($pending['method'] ?? 'totp');

		$config = setting('Frontend\Auth');

		$this->data['otpCountdownMode'] = $method === 'email' ? 'expiry' : 'period';
		$this->data['otpCountdownServerTime'] = microtime(true);
		$this->data['otpCountdownExpiresAt'] = $method === 'email' ? ($this->authModel->getEmailOtpExpiresAt((string) $pending['user_uuid']) ?? time()) : 0;
		$this->data['otpCountdownPeriod'] = $method === 'totp' ? (int) ($config->twoFactorOtpExpiry ?? config(\Config\Frontend\Auth::class)->twoFactorOtpExpiry) : 0;

        return $this->render('frontend/auth/verifyView', $this->data);
    }

    /** Shows or consumes a restoration confirmation after successful authentication. */
    public function restore(): string|ResponseInterface
    {
        if ($this->request->is('post')):
            if ( ! $this->request->isAJAX()):
                return $this->response->setStatusCode(400);
            endif;
            return $this->jsonResponse($this->authModel->confirmRemoval($this->request));
        endif;

        $confirmation = $this->authModel->removalConfirmationToken();
        if ($confirmation === null):
            return redirect()->to(base_url('auth/login'));
        endif;

        $this->data['action'] = 'restore';
        $this->data['removalConfirmation'] = $confirmation;
        $this->data['title'] = lang('frontend/auth.titles.restore');
        return $this->render('frontend/auth/restoreView', $this->data);
    }

    /**
     * Revoca l'accesso frontend corrente e reindirizza alla pagina di login.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function logout()
    {
        $cookie = $this->request->getCookie('frontendRememberMe');

        /* 1. Legge i dati utente PRIMA di scollegarlo */
        $firstname = $this->currentUser->firstname ?? '';
        $lastname = $this->currentUser->lastname ?? '';

        /* 2. Esegue il logout corrispondente tramite il Model */
        if ($cookie !== null):
            $this->authModel->logoutByCookie($cookie, 'manual');
        else:
            $this->authModel->logoutBySession('manual');
        endif;

        /* 3. Prepara il messaggio di saluto utilizzando i dati appena salvati */
        $message = ui_message('frontend/auth.messages.goodbye', [$firstname, $lastname], lang('frontend/auth.messages.goodbye'));

        /* 4. Imposta i flashdata nativi di CI4 */
        session()->setFlashdata('message', $message);
        session()->setFlashdata('class', 'app-alert-success fw-bold');
        session()->setFlashdata('icon', '<i class="fa-solid fa-handshake"></i>');

        /* 5. Esegue un redirect pulito in GET verso la pagina di login */
        return redirect()->to(base_url('auth'))->withCookies();
    }
}
