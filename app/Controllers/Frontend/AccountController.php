<?php declare(strict_types = 1);

namespace App\Controllers\Frontend;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\UserAgent;
use Psr\Log\LoggerInterface;
use App\Models\Frontend\AccountModel;
use App\Models\Frontend\Components\GalleryOneModel;

/**
 * Gestisce profilo, immagini, sessioni e sicurezza del cliente autenticato.
 */
class AccountController extends FrontendController
{
    protected AccountModel $accountModel;
    protected GalleryOneModel $galleryOneModel;

    /**
     * Inizializza il controller e prepara i dati della sezione.
     *
     * @param \CodeIgniter\HTTP\RequestInterface $request Richiesta HTTP corrente.
     * @param \CodeIgniter\HTTP\ResponseInterface $response Risposta HTTP corrente.
     * @param \Psr\Log\LoggerInterface $logger Servizio di registrazione degli errori.
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);

        $this->data['controller'] = 'account';
        $this->data['entity'] = 'users';

        $this->data['icon'] = '<i class="fa-solid fa-user-gear"></i>';
        $this->data['title'] = lang('frontend/account.titles.index');

        $this->accountModel = model(AccountModel::class);

        $this->galleryOneModel = model(GalleryOneModel::class);

        $this->data['sections'] = [
            'general' => [
                'title' => lang('frontend/account.leftMenu.general'),
                'icon' => '<i class="fa-solid fa-id-card"></i>',
                'route' => 'account/general',
            ],
            'edit' => [
                'title' => lang('frontend/account.leftMenu.edit'),
                'icon' => '<i class="fa-solid fa-user-edit"></i>',
                'route' => 'account/edit',
            ],
             'images' => [
                'title' => lang('frontend/account.leftMenu.images'),
                'icon' => '<i class="fa-solid fa-images"></i>',
                'route' => 'account/images',
            ], 
            'tokens' => [
                'title' => lang('frontend/account.leftMenu.tokens'),
                'icon' => '<i class="fa-solid fa-chain"></i>',
                'route' => 'account/tokens',
            ],
            'resetPassword' => [
                'title' => lang('frontend/account.leftMenu.resetPassword'),
                'icon' => '<i class="fa-solid fa-unlock"></i>',
                'route' => 'account/resetPassword',
            ],
            'security' => [
                'title' => lang('frontend/account.leftMenu.security'),
                'icon' => '<i class="fa-solid fa-user-shield"></i>',
                'route' => 'account/security',
            ],
            'deleteAccount' => [
                'title' => lang('frontend/account.leftMenu.deleteAccount'),
                'icon' => '<i class="fa-solid fa-user-xmark"></i>',
                'route' => 'account/deleteAccount',
            ],
        ];
    }

    /**
     * Mostra la pagina iniziale della sezione o reindirizza al relativo accesso.
     *
     * @return string
     */
    public function index(): string
    {
        $this->data['action'] = 'index';

        return $this->render('frontend/account/indexView', $this->data);
    }

    /**
     * Mostra i dati generali del cliente autenticato.
     *
     * @return string
     */
    public function general(): string
    {
        $this->data['action'] = 'general';

        return $this->render('frontend/account/generalView', $this->data);
    }

    /**
     * Mostra, aggiorna o ricarica il form del profilo personale.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface|string
     */
    public function edit(): string|ResponseInterface
    {
        if ($this->request->isAJAX() && $this->request->is('post')):

            $posts = $this->request->getPost();

            if (isset($posts['action']) && $posts['action'] === 'refresh'):
                return $this->jsonResponse(['result' => true, 'output' => view('frontend/account/partials/edit/editPartial', $this->data)]);
            endif;

            /* Passiamo l'UUID sicuro ricavato dall'oggetto dell'admin loggato */
            $rules = $this->accountModel->editValidationRules($this->currentUser->uuid);
			$emailChanged = isset($this->currentUser->email)
				&& strtolower(trim((string) ($posts['email'] ?? '')))
				!== strtolower((string) $this->currentUser->email);

			if ($emailChanged):
				$rules = array_merge($rules, $this->accountModel->currentPasswordValidationRules());
			endif;

            if ( ! $this->validateData($posts, $rules)):
                return $this->jsonResponse(['errors' => $this->validator->getErrors(), 'message' => lang('frontend/account.messages.validationErrors')]);
            endif;

			if ($emailChanged && ! $this->accountModel->verifyCurrentPassword($this->currentUser->uuid, (string) $posts['currentPassword'])):
				return $this->jsonResponse([
					'errors' => ['currentPassword' => lang('frontend/account.errors.currentPasswordInvalid')],
					'message' => lang('frontend/account.messages.validationErrors'),
				]);
			endif;

            $json = $this->accountModel->edit($posts, $this->currentUser);

            if ($json['result'] === false):
                return $this->jsonResponse(['result' => false, 'message' => $json['message']]);
            endif;

            if ($json['result'] === true):
                $this->data['currentUser'] = $json['currentUser'];
                $json['output'] = view('frontend/account/partials/edit/editPartial', $this->data);

                /* Inserisco anche la vista del menu in alto nel caso admin corrente abbia modificato il nome o il cognome */
                $json['navBarTop'] = view('frontend/template/headerPartial', $this->data);
            endif;

            return $this->jsonResponse($json);

        endif;

        $this->data['action'] = 'edit';

        return $this->render('frontend/account/editView', $this->data);
    }

    /**
     * Mostra caricamento e galleria delle immagini del cliente autenticato.
     *
     * @return string
     */
    public function images(): string
    {
        $this->data['action'] = 'images';

        $this->data['context'] = 'edit';
        $this->data['images'] = $this->galleryOneModel->getImages(['entity' => 'users', 'uuid' => $this->currentUser->uuid]);

        $this->data['saveImages'] = true;
        $this->data['uuid'] = $this->currentUser->uuid;

        return $this->render('frontend/account/imagesView', $this->data);
    }

    /**
     * Mostra o aggiorna l'elenco dei token del cliente autenticato.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface|string
     */
    public function tokens(): string|ResponseInterface
    {
        $this->data['userAgent'] = new UserAgent();
        $this->data['tokens'] = $this->accountModel->getTokens($this->currentUser->uuid);

        /* Delega totale al Model per recuperare l'ID della sessione in uso */
        $this->data['currentTokenId'] = $this->accountModel->getCurrentTokenId();

        if ($this->request->isAJAX() && $this->request->is('post')):
            return $this->jsonResponse([
                'result' => true,
                'output' => view('frontend/account/partials/tokens/tokensPartial', $this->data)
            ]);
        endif;

        $this->data['action'] = 'tokens';

        return $this->render('frontend/account/tokensView', $this->data);
    }

    /**
     * Valida e revoca un token dell'utente, aggiornandone l'elenco.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function deleteToken(): ResponseInterface
    {
        if ($this->request->isAJAX() && $this->request->is('post')):

            $posts = $this->request->getPost();
            $rules = $this->accountModel->deleteTokenValidationRules();

            if ( ! $this->validateData($posts, $rules)):
                $errorMessage = implode('<br>', $this->validator->getErrors());
                return $this->jsonResponse(['result' => false, 'message' => ui_message('frontend/account.messages.validateToastErrors', [$errorMessage], lang('frontend/account.messages.validateToastErrors'))]);
            endif;

            /* Delega totale al Model per l'ID corrente, poi lo passa per lo sbarramento */
            $currentTokenId = $this->accountModel->getCurrentTokenId();
            if (($passwordError = $this->requireCurrentPassword()) !== null):
                return $passwordError;
            endif;

            $result = $this->accountModel->deleteToken($posts, $this->currentUser, $currentTokenId);

            if($result['result'] === true):
                $this->data['userAgent'] = new UserAgent();
                $this->data['tokens'] = $this->accountModel->getTokens($this->currentUser->uuid);
                $this->data['currentTokenId'] = $currentTokenId;

                $json = ['result' => true, 'message' => $result['message']];
                $json['output'] = view('frontend/account/partials/tokens/tokensPartial', $this->data);
            else:
                $json = ['result' => false, 'message' => $result['message']];
            endif;

            return $this->jsonResponse($json);
        endif;

        return service('response')->setStatusCode(400);
    }

    /**
     * Avvia il recupero credenziali e restituisce il risultato dell'operazione.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface|string
     */
    public function resetPassword(): string|ResponseInterface
    {
        if ($this->request->isAJAX() && $this->request->is('post')):
			$config = setting('Frontend\Auth');
			$throttleKey = 'frontend-account-password-reset-' . hash('sha256', $this->currentUser->uuid . '|' . $this->request->getIPAddress());

			if ( ! service('throttler')->check($throttleKey, (int) $config->resetPasswordLimit, (int) $config->resetPasswordInterval, 1)):
				return $this->jsonResponse(['result' => false, 'message' => lang('frontend/account.messages.tooManyResetRequests')], 429);
			endif;

            $result = $this->accountModel->resetPassword($this->currentUser, $this->request);

            $this->data['expiringDate'] = $this->accountModel->getExpiringDate($this->currentUser);

            if($result['result'] === false):

                $json = ['result' => $result['result'], 'message' => $result['message']];

            else:

                $output = view('frontend/account/partials/resetPassword/resetPasswordPartial', $this->data);
                $json = ['result' => $result['result'], 'message' => $result['message'], 'output' => $output];

            endif;

            return $this->jsonResponse($json);

        endif;

        $this->data['action'] = 'resetPassword';

        $this->data['expiringDate'] = $this->accountModel->getExpiringDate($this->currentUser);

        return $this->render('frontend/account/resetPasswordView', $this->data);
    }

    /**
     * Mostra le impostazioni personali di autenticazione a due fattori.
     *
     * @return string
     */
    public function security(): string
    {
        $this->data['action'] = 'security';

        $this->data['activeMethod'] = $this->accountModel->getActiveMethod($this->currentUser->uuid);
        
        return $this->render('frontend/account/securityView', $this->data);
    }

    /**
     * Verifica la password del cliente limitando i tentativi non riusciti.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function verifyCurrentPassword(): ResponseInterface
    {
        if ( ! $this->request->isAJAX() || ! $this->request->is('post')):
            return $this->response->setStatusCode(400);
        endif;
        $posts = ['currentPassword' => $this->request->getPost('currentPassword')];
        if ( ! $this->validateData($posts, $this->accountModel->currentPasswordValidationRules())):
            return $this->jsonResponse(['result' => false, 'errors' => $this->validator->getErrors(), 'message' => lang('frontend/account.messages.validationErrors')]);
        endif;
        if ( ! $this->accountModel->verifyCurrentPassword($this->currentUser->uuid, (string) $posts['currentPassword'])):
            return $this->jsonResponse(['result' => false, 'errors' => ['currentPassword' => lang('frontend/account.errors.currentPasswordInvalid')], 'message' => lang('frontend/account.messages.validationErrors')]);
        endif;
        return $this->jsonResponse(['result' => true]);
    }

    /**
     * Conferma con password e salva il metodo 2FA email o disabilitato.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function saveBasicMethod(): ResponseInterface
    {
        if ($this->request->isAJAX() && $this->request->is('post')) :
			$posts = [
				'twoFactorMethod' => (string) $this->request->getPost('twoFactorMethod'),
				'currentPassword' => (string) $this->request->getPost('currentPassword'),
			];
			$method = (string) $posts['twoFactorMethod'];
			$allowedMethods = setting('Frontend\Auth')->twoFactorMethods;

			/* Il metodo viene verificato prima delle altre dipendenze operative. */
			if ( ! in_array($method, $allowedMethods, true) || $method === 'totp'):
				return $this->jsonResponse(['result' => false, 'message' => lang('frontend/account.messages.methodNotValid')]);
			endif;

			$rules = array_merge([
				'twoFactorMethod' => [
					'label' => lang('frontend/account.titles.twoFactor'),
					'rules' => ['required'],
				],
			], $this->accountModel->currentPasswordValidationRules());

			if ( ! $this->validateData($posts, $rules)):
				return $this->jsonResponse(['errors' => $this->validator->getErrors(), 'message' => lang('frontend/account.messages.validationErrors')]);
			endif;

			if ( ! $this->accountModel->verifyCurrentPassword($this->currentUser->uuid, (string) $posts['currentPassword'])):
				return $this->jsonResponse([
					'errors' => ['currentPassword' => lang('frontend/account.errors.currentPasswordInvalid')],
					'message' => lang('frontend/account.messages.validationErrors'),
				]);
			endif;

            /* Eseguo l'aggiornamento nel Model */
            $updated = $this->accountModel->setBasicMethod($this->currentUser, $method);

            if ( ! $updated) :
                return $this->jsonResponse(['result' => false, 'message' => lang('frontend/account.messages.updateSecuritySettingsError')]);
            endif;

            return $this->jsonResponse(['result' => true, 'message' => lang('frontend/account.messages.updateSecuritySettingsSuccess')]);

        endif;

        return service('response')->setStatusCode(400);
    }

    /**
     * Verifica la password e prepara segreto temporaneo e QR code TOTP.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function setupTotp(): ResponseInterface 
    {
        if ($this->request->isAJAX() && $this->request->is('post')):
			$posts = [
				'currentPassword' => (string) $this->request->getPost('currentPassword'),
			];

			if ( ! $this->validateData($posts, $this->accountModel->currentPasswordValidationRules())):
				return $this->jsonResponse(['errors' => $this->validator->getErrors(), 'message' => lang('frontend/account.messages.validationErrors')]);
			endif;

			if ( ! $this->accountModel->verifyCurrentPassword($this->currentUser->uuid, (string) $posts['currentPassword'])):
				return $this->jsonResponse([
					'errors' => ['currentPassword' => lang('frontend/account.errors.currentPasswordInvalid')],
					'message' => lang('frontend/account.messages.validationErrors'),
				]);
			endif;

            $userUuid = $this->currentUser->uuid;

            /* Istanzio il servizio per la gestione dei codici OTP */
            $otpService = new \App\Libraries\Frontend\AppOtpService();
            $secret = $otpService->generateSecret();

			/* Il secret resta in sessione e non modifica il metodo TOTP già attivo */
            $saved = $this->accountModel->saveTemporarySecret($userUuid, $secret);

            if ( ! $saved) :
                return $this->jsonResponse(['result' => false, 'message' => lang('frontend/account.messages.configurationInitializeError')]);
            endif;

            /* Genero l'URI stringa usando il servizio esistente */
            $uri = $otpService->getProvisioningUri($secret, $this->currentUser->email);
            
            /* Sintassi ufficiale Endroid v6: parametri passati direttamente nel costruttore */
            $builder = new \Endroid\QrCode\Builder\Builder(
                writer: new \Endroid\QrCode\Writer\PngWriter(),
                data: $uri,
                size: 250,
                margin: 0
            );

            $result = $builder->build();

            $data['qrCode'] = $result->getDataUri();
            $data['totpSecret'] = $secret;
			$data['otpCountdownServerTime'] = microtime(true);
			$config = setting('Frontend\Auth');
			$data['otpCountdownPeriod'] = (int) ($config->twoFactorOtpExpiry ?? config(\Config\Frontend\Auth::class)->twoFactorOtpExpiry);

            /* Renderizzo la vista parziale per l'attivazione del TOTP */
            $output = view('frontend/account/partials/security/totpSetupPartial', $data);

            return $this->jsonResponse(['result' => true, 'output' => $output]);

        endif;

        return service('response')->setStatusCode(400);
    }

    /**
     * Verifica il codice OTP e attiva il metodo TOTP del cliente.
     *
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function confirmTotp(): ResponseInterface
    {
        if ($this->request->isAJAX() && $this->request->is('post')) :

            /* Definisco i soli campi consentiti in questa richiesta */
            $allowedFields = ['otp'];
            
            /* Sanifico l'input filtrando l'array $_POST */
            $posts = array_intersect_key($this->request->getPost(), array_flip($allowedFields));

            if ( ! $this->validateData($posts, ['otp' => 'required|regex_match[/^[0-9]{' . (int) setting('Frontend\Auth')->twoFactorDigits . '}$/]'])) :
                return $this->jsonResponse(['result'  => false, 'message' => lang('frontend/account.messages.missingCode')]);
            endif;

            /* Da qui in poi lavoriamo solo sul parametro sanificato e validato */
            $otpCode = (string) $posts['otp'];
            $userUuid = $this->currentUser->uuid;
			$config = setting('Frontend\Auth');
			$throttleKey = 'frontend-account-totp-confirm-' . hash('sha256', $userUuid . '|' . $this->request->getIPAddress());

			if ( ! service('throttler')->check($throttleKey, (int) $config->twoFactorLimit, (int) $config->twoFactorTime, 1)):
				return $this->jsonResponse(['result' => false, 'message' => lang('frontend/account.messages.tooManyAttempts')], 429);
			endif;

            /* Recupero il secret temporaneo */
            $secret = $this->accountModel->getTemporarySecret($userUuid);

            if ( ! $secret) :
                return $this->jsonResponse(['result' => false, 'message' => lang('frontend/account.messages.noConfigurationSession')]);
            endif;

            /* Istanzio il servizio e valido il codice */
            $otpService = new \App\Libraries\Frontend\AppOtpService();
            if ( ! $otpService->verify($secret, $otpCode)) :
                return $this->jsonResponse(['result' => false, 'message' => lang('frontend/account.messages.wrongCode')]);
            endif;

            /* Attivo definitivamente il TOTP nel DB */
			$activated = $this->accountModel->activateTotpMethod($userUuid, $secret, $this->currentUser);

            if ( ! $activated) :
                return $this->jsonResponse(['result' => false, 'message' => lang('frontend/account.messages.totpActivationNotPossible')]);
            endif;

            return $this->jsonResponse(['result' => true, 'message' => lang('frontend/account.messages.totpConfigurationSuccess')]);

        endif;

        return service('response')->setStatusCode(400);
    }

	/**
	 * Annulla la configurazione TOTP temporanea del cliente autenticato.
	 *
	 * @return \CodeIgniter\HTTP\ResponseInterface
	 */
	public function cancelTotpSetup(): ResponseInterface
	{
		if ($this->request->isAJAX() && $this->request->is('post')):
			$this->accountModel->clearTemporarySecret();

			return $this->jsonResponse(['result' => true]);
		endif;

		return service('response')->setStatusCode(400);
	}

    /**
     * Mostra la richiesta di eliminazione o la conferma con la password personale del cliente.
     *
     * La sessione locale viene chiusa soltanto dopo la conferma della transazione nel model.
     * Questa operazione richiede sempre la password, anche con la riconferma generale disabilitata.
     *
     * @return string|\CodeIgniter\HTTP\ResponseInterface
     */
    public function deleteAccount(): string|ResponseInterface
    {
        if ($this->request->isAJAX() && $this->request->is('post')):
            $posts = ['currentPassword' => $this->request->getPost('currentPassword')];
            if ( ! $this->validateData($posts, $this->accountModel->currentPasswordValidationRules())):
                return $this->jsonResponse(['result' => false, 'errors' => $this->validator->getErrors(), 'message' => lang('frontend/account.messages.validationErrors')]);
            endif;

            $result = $this->accountModel->requestRemoval($this->currentUser, (string) $posts['currentPassword']);
            if ($result['result'] === true):
                session()->remove(['frontendSession', 'frontend_login_log_id', 'frontend_auth_2fa_pending', 'frontend_account_totp_setup', 'frontend_intended_url']);
                delete_cookie('frontendRememberMe');
                session()->regenerate(true);
                service('userAuthorization')->refresh();
                session()->setFlashdata(['message' => $result['message'], 'class' => 'app-alert-success fw-bold']);
                $result['redirect'] = base_url('auth/login');
            endif;
            return $this->jsonResponse($result);
        endif;

        $this->data['action'] = 'deleteAccount';
        $this->data['removalDays'] = \Config\Frontend\Auth::ACCOUNT_REMOVAL_DAYS;
        return $this->render('frontend/account/deleteAccountView', $this->data);
    }
}
