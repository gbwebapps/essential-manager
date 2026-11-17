<?php declare(strict_types = 1);

namespace App\Models\Frontend;

use App\Models\Frontend\FrontendModel;

/**
 * Modello principale dedicato all'Autenticazione e alla Sicurezza degli accessi (Auth).
 * 
 * Estende il FrontendModel e incapsula tutta la complessa business logic legata all'ingresso 
 * nel sistema. Gestisce il flusso di Login a più step (incluso il controllo 2FA), il sistema 
 * anti brute-force (throttling dei tentativi falliti), il recupero delle credenziali, 
 * la generazione di token crittografici (sessioni o cookie "Remember Me") e la tracciatura 
 * rigorosa di ogni accesso o disconnessione nell'Audit Log.
 */
class AuthModel extends FrontendModel
{
    /** Hash password valido usato per uniformare il costo di verifica degli account inesistenti. */
    private const DUMMY_PASSWORD_HASH = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

    /**
     * Contenitore per i parametri di configurazione globali del modulo Auth 
     * (es. limiti di tentativi, durata sessioni, espressioni regolari per le password).
     * 
     * @var object 
     */
    private object $config;

    /**
     * Nome identificativo del modulo corrente, utilizzato per instradare i log o risolvere i template email.
     * 
     * @var string|null 
     */
    public ?string $module = 'auth';

    /**
     * Whitelist dei campi HTTP POST consentiti in fase di Login.
     * Blocca l'immissione di parametri non previsti prima dell'elaborazione delle credenziali.
     * 
     * @var array 
     */
    protected array $loginAllowedFields = ['email', 'password', 'rememberMe']; 

    /**
     * Whitelist dei campi consentiti per la richiesta di reset della password (solo email).
     * 
     * @var array 
     */
    protected array $resetPasswordAllowedFields = ['email'];

    /**
     * Whitelist dei campi consentiti durante l'impostazione fisica di una nuova password 
     * (tramite link di ripristino o attivazione).
     * 
     * @var array 
     */
    protected array $setPasswordAllowedFields = ['password', 'token'];

    /**
     * Whitelist dei campi consentiti durante la verifica OTP per il Secondo Fattore di Autenticazione (2FA).
     * 
     * @var array 
     */
    protected array $verifyAllowedFields = ['code'];

    /**
     * Hook di inizializzazione nativo di CodeIgniter 4.
     *
     * Richiama il setup genitore e inietta immediatamente in memoria le configurazioni
     * del modulo Auth, rendendole disponibili a tutti i metodi della classe per evitare query ripetitive.
     *
     * @return void
     */
    protected function initModel(): void 
    {
        parent::initModel();

        $this->config = setting('Frontend\Auth');
    }

    /**
     * Regole di validazione per il form di Login.
     * 
     * Verifica che l'email sia in un formato valido e che la password rispetti la policy configurata.
     * La validità effettiva della credenziale viene poi determinata dal confronto con l'hash memorizzato.
     *
     * @return array Regole native di CodeIgniter
     */
    public function validateLoginRules(): array
    {
        return [
            'email' => [
                'label' => 'Indirizzo email',
                'rules' => ['required', 'valid_email', 'trim']
            ], 
            'password' => [
                'label' => 'Password',
                'rules' => ['required', "regex_match[{$this->config->passwordRegex}]"],
            ]
        ];
    }

    /**
     * Regole di validazione per la richiesta di Reset della Password (Form "Password Dimenticata").
     * 
     * Controlla esclusivamente la correttezza formale dell'indirizzo email prima di interrogare il database.
     *
     * @return array Regole native di CodeIgniter
     */
    public function validateResetPasswordRules()
    {
        return [
            'email' => [
                'label' => 'Indirizzo email',
                'rules' => ['required', 'valid_email', 'trim'], 
            ],
        ];
    }

    /**
     * Regole di validazione per l'impostazione di una nuova password.
     * 
     * Verifica che la nuova password rispetti la Regex di sicurezza, che il campo di conferma combaci 
     * perfettamente (`matches[password]`) e applica una regola personalizzata (`checkUserTokenRule`) 
     * per validare l'autenticità del token nascosto inviato dal form.
     *
     * @return array Regole strutturate
     */
    public function validateSetPasswordRules()
    {
        return [
            'password' => [
                'label' => 'Password',
                'rules' => ['required', "regex_match[{$this->config->passwordRegex}]"],
                'errors' => [
                    'regex_match' => lang('frontend/auth.errors.passwordFormatNotValid')
                ]
            ], 
            'confirmPassword' => [
                'label' => 'Conferma password',
                'rules' => ['required', 'matches[password]'], 
            ], 
            'token' => [
                'label' => 'Token di autenticazione',
                'rules' => 'required|checkUserTokenRule', 
                'errors' => [
                    'checkUserTokenRule' => lang('frontend/auth.messages.checkAuthError')
                ]
            ]
        ];    
    }

    /**
     * Regole di validazione per la verifica del codice OTP (2FA).
     * 
     * Assicura che il codice immesso sia composto dal numero di cifre configurato,
     * preservando anche gli eventuali zeri iniziali.
     *
     * @return array Regole per il campo code
     */
    public function validateVerifyRules(): array
    {
        return [
            'code' => [
                'label' => 'Codice',
                'rules' => ['required', 'regex_match[/^[0-9]{' . (int) setting('Frontend\Auth')->twoFactorDigits . '}$/]'],
            ],
        ];
    }

    /**
     * Orchestratore principale del flusso di Autenticazione (Login).
     * 
     * Metodo denso e protetto:
     * 1. Filtra i dati in ingresso e legge le configurazioni (es. limiti tentativi, 2FA).
     * 2. Applica il throttling uniforme per email anonimizzata e indirizzo IP, anche per account inesistenti.
     * 3. Estrae l'utente verificando subito gli scudi (deve essere attivo, non cestinato, non sospeso).
     * 4. Valida l'hash della password con costo uniforme. Se fallisce, registra il tentativo errato a DB in transazione.
     * 5. Se la password è corretta ma il 2FA è attivo, parcheggia i dati sicuri in una sessione temporanea server-side, 
     *    invia l'eventuale OTP via email e avvisa il controller di richiedere il secondo fattore (`result => '2fa_required'`).
     * 6. Se il 2FA non è richiesto, azzera i tentativi falliti e demanda la creazione della sessione a `innerLogin()`.
     *
     * @param array $posts Credenziali pulite sottomesse dal form
     * @param \CodeIgniter\HTTP\IncomingRequest $request Richiesta per estrarre Indirizzo IP
     * @return array Risposta strutturata con esito e messaggi per il router o il client
     */
    public function login(array $posts, \CodeIgniter\HTTP\IncomingRequest $request): array
    {
        $transactionStarted = false;

        try 
        {
            /* Inizializzazione variabili e parametri di configurazione */
            $this->cancelRemovalConfirmation();
            session()->remove('frontend_auth_2fa_pending');
            $posts = $this->checkAllowedFields($posts, $this->loginAllowedFields);
            $posts['email'] = strtolower(trim($posts['email']));

            $rememberMe = (isset($posts['rememberMe']) && $posts['rememberMe']) ? true : false;
            $ip_address = $request->getIPAddress();
            $emailHash = $this->getAttemptIdentifierHash($posts['email']);

            /* Lettura centralizzata delle configurazioni per evitare chiamate ridondanti */
            $allowAttempts = (bool) $this->config->attempts;
            $allowTwoFactor = (bool) $this->config->twoFactor;

            if ($allowAttempts):
                $attemptStatus = $this->getAttemptsStatus(
                    'login',
                    ['email_hash' => $emailHash],
                    $ip_address,
                    (int) $this->config->attemptsLimit,
                    (int) $this->config->attemptsInterval
                );

                if ($attemptStatus['limited']):
                    if ( ! $this->db->transBegin()):
                        throw new \RuntimeException('login: impossibile avviare la transazione tentativi.');
                    endif;

                    $transactionStarted = true;
                    $this->renewAttemptsWindow('login', ['email_hash' => $emailHash], $ip_address, $attemptStatus);

                    if ( ! $this->db->transCommit()):
                        throw new \RuntimeException('login: commit tentativi non riuscito.');
                    endif;

                    $transactionStarted = false;

                    log_user_activity('LOGIN_BLOCKED', 'auth', lang('frontend/auth.audits.loginBlocked'));

                    return ['result' => false, 'message' => lang('frontend/auth.messages.tooManyAttempts')];
                endif;
            endif;

            $sql = 'select uuid, firstname, lastname, email, password_hash, deleted_at, to_be_removed_at
                    from users where email = ? and status = 1 and password_hash is not null and suspended_at is null
                    and (deleted_at is null or to_be_removed_at > ?) limit 1';
            $admin = $this->db->query($sql, [$posts['email'], date('Y-m-d H:i:s')])->getRow();

            if ( ! $admin):
                password_verify($posts['password'], self::DUMMY_PASSWORD_HASH);

                if ($allowAttempts):
                    if ( ! $this->db->transBegin()):
                        throw new \RuntimeException('login: impossibile avviare la transazione tentativi.');
                    endif;

                    $transactionStarted = true;
                    $this->registerAttempt('login', ['user_uuid' => null, 'email_hash' => $emailHash], $ip_address);

                    if ( ! $this->db->transCommit()):
                        throw new \RuntimeException('login: commit tentativi non riuscito.');
                    endif;

                    $transactionStarted = false;
                endif;

                log_user_activity('LOGIN_REFUSED', 'auth', lang('frontend/auth.audits.loginRefused'));
                return ['result' => false, 'message' => lang('frontend/auth.messages.loginFailed')];
            endif;

            /* Verifica della password */
            if ( ! password_verify($posts['password'], $admin->password_hash)):

                if ($allowAttempts):
                    if ( ! $this->db->transBegin()):
                        throw new \RuntimeException('login: impossibile avviare la transazione tentativi.');
                    endif;

                    $transactionStarted = true;
                    $this->registerAttempt('login', ['user_uuid' => $admin->uuid, 'email_hash' => $emailHash], $ip_address);

                    if ( ! $this->db->transCommit()):
                        throw new \RuntimeException('login: commit tentativi non riuscito.');
                    endif;

                    $transactionStarted = false;
                endif;

                log_user_activity('LOGIN_FAILED', 'auth', ui_message('frontend/auth.audits.loginFailed', [esc($admin->firstname), esc($admin->lastname)], lang('frontend/auth.audits.loginFailed')), $admin);

                return ['result' => false, 'message' => lang('frontend/auth.messages.loginFailed')];
                
            endif;

            /* Gestione del Secondo Fattore di Autenticazione (2FA) */
            if ($allowTwoFactor):

                $sql = "select method from users_2fa where user_uuid = ? and enabled = 1 limit 1";
                $twofa = $this->db->query($sql, [$admin->uuid])->getRow();

                if ($allowAttempts):
                    $this->clearAttempts('login', ['user_uuid' => $admin->uuid, 'email_hash' => $emailHash]);
                endif;

                if ($twofa):
                    /* IMPLEMENTAZIONE SICURA: Scrittura dei dati sensibili in sessione server protetta */
                    session()->set('frontend_auth_2fa_pending', [
                        'user_uuid' => $admin->uuid,
                        'identity' => $this->loginIdentity($admin),
                        'rememberMe' => $rememberMe,
                        'method' => $twofa->method,
                        'created_at' => time(),
                        'redirect' => session()->get('frontend_intended_url') ?? base_url('account'),
                    ]);

                    /* Eliminazione preventiva di eventuali codici presenti in users_2fa_codes per l'utente corrente */
                    $sql = "delete from users_2fa_codes where user_uuid = ?";
                    $this->db->query($sql, [$admin->uuid]);

                    if ($twofa->method === 'email'):

                        $sent = (new \App\Libraries\Frontend\EmailOtpService())->send($admin->uuid);

                        if ( ! $sent):

                            /* Elimina l'eventuale OTP registrato prima del fallimento dell'invio */
                            $sql = "delete from users_2fa_codes where user_uuid = ?";
                            $this->db->query($sql, [$admin->uuid]);

                            /* Annulla lo stato di autenticazione parziale */
                            session()->remove('frontend_auth_2fa_pending');

                            return ['result' => false, 'message' => lang('frontend/auth.messages.twoFactorEmailSendError')];

                        endif;

                    endif;

                    log_user_activity('2FA_REQUIRED', 'auth', ui_message('frontend/auth.audits.2faRequired', [esc($twofa->method), esc($admin->firstname), esc($admin->lastname)], lang('frontend/auth.audits.2faRequired')), $admin);

                    /* Il client riceve solo la notifica del successo parziale senza dati sensibili esposti */
                    return ['result' => '2fa_required', 'method' => $twofa->method];
                endif;

            endif;

            /* Fase finale del Login (Password e 2FA non richiesto) */
            if ($allowAttempts):
                if ( ! $this->db->transBegin()):
                    throw new \RuntimeException('login: impossibile avviare la transazione di pulizia tentativi.');
                endif;

                $transactionStarted = true;
                $this->clearAttempts('login', ['user_uuid' => $admin->uuid, 'email_hash' => $emailHash]);

                if ( ! $this->db->transCommit()):
                    throw new \RuntimeException('login: commit pulizia tentativi non riuscito.');
                endif;

                $transactionStarted = false;
            endif;

            return $this->completeAuthentication($admin, $rememberMe, $request, session()->get('frontend_intended_url') ?? base_url('account'));

        } catch (\Throwable $e) {

            /* Annulla un'eventuale transazione ancora aperta */
            if ($transactionStarted):
                $this->db->transRollback();
            endif;

            log_message('error', lang('frontend/auth.messages.loginFailed') . ' - ' . $e);

            return ['result' => false, 'message' => lang('frontend/auth.messages.loginFailed')];
        }
    }

    /**
     * Binds authentication to the verified password and the current removal request.
     *
     * @return array{password: string, deleted_at: ?string, to_be_removed_at: ?string}
     */
    private function loginIdentity(object $user): array
    {
        return [
            'password' => hash('sha256', $user->password_hash),
            'deleted_at' => $user->deleted_at,
            'to_be_removed_at' => $user->to_be_removed_at,
        ];
    }

    /** Starts a session or requests explicit confirmation after all required factors are verified. */
    private function completeAuthentication(object $user, bool $rememberMe, \CodeIgniter\HTTP\IncomingRequest $request, string $redirect): array
    {
        $identity = $this->loginIdentity($user);
        if ($user->deleted_at === null):
            $result = $this->innerLogin($user->uuid, $identity, $rememberMe, $request);
            return $result + ['redirect' => $redirect];
        endif;

        if ($user->to_be_removed_at === null || $user->to_be_removed_at <= date('Y-m-d H:i:s')):
            return ['result' => false, 'message' => lang('frontend/auth.messages.loginFailed')];
        endif;

        session()->regenerate(true);
        session()->set('frontend_auth_removal_pending', [
            'user_uuid' => $user->uuid,
            'identity' => $identity,
            'rememberMe' => $rememberMe,
            'redirect' => $redirect,
            'expires_at' => time() + (int) $this->config->twoFactorTime,
            'confirmation' => bin2hex(random_bytes(32)),
        ]);

        return ['result' => 'restore_required', 'redirect' => base_url('auth/restore')];
    }

    /** Discards the pending restoration when the visitor returns to login. */
    public function cancelRemovalConfirmation(): void
    {
        session()->remove('frontend_auth_removal_pending');
    }

    /** Returns the one-time token for the confirmation page. */
    public function removalConfirmationToken(): ?string
    {
        return $this->getRemovalConfirmation()['confirmation'] ?? null;
    }

    /** Consumes the confirmation and restores the account only if the deadline still allows it. */
    public function confirmRemoval(\CodeIgniter\HTTP\IncomingRequest $request): array
    {
        $pending = $this->getRemovalConfirmation();
        $confirmation = $request->getPost('confirmation');
        if ($pending === null || ! is_string($confirmation) || ! hash_equals($pending['confirmation'], $confirmation)):
            return ['result' => false, 'message' => lang('frontend/auth.messages.sessionExpired')];
        endif;
        $this->cancelRemovalConfirmation();

        try {
            $result = $this->innerLogin($pending['user_uuid'], $pending['identity'], $pending['rememberMe'], $request, true);
            return $result + ['redirect' => $pending['redirect']];
        } catch (\Throwable $e) {
            log_message('error', 'Account restoration failed: ' . $e->getMessage());
            return ['result' => false, 'message' => lang('frontend/auth.messages.loginFailed')];
        }
    }

    /** Returns the server-side proof only while the session and recovery deadline are valid. */
    private function getRemovalConfirmation(): ?array
    {
        $pending = session()->get('frontend_auth_removal_pending');
        if ( ! is_array($pending) || ! isset($pending['user_uuid'], $pending['identity']['to_be_removed_at'], $pending['rememberMe'], $pending['redirect'], $pending['expires_at'], $pending['confirmation'])
            || time() >= $pending['expires_at'] || $pending['identity']['to_be_removed_at'] <= date('Y-m-d H:i:s')):
            return null;
        endif;
        return $pending;
    }

    /**
     * Conclude positivamente l'autenticazione generando i token e i cookie necessari.
     * 
     * Valuta se l'utente ha richiesto la funzione "Ricordami" per decidere la scadenza (cookie lungo o sessione breve).
     * Genera un nuovo token crittografico univoco, lo salva nella tabella `users_tokens`, 
     * e inserisce un record di ingresso (Login) nella tabella `users_logs` per l'auditing.
     * Successivamente rigenera l'ID di sessione PHP (protezione da Session Fixation) e imposta 
     * il cookie cifrato o la sessione standard, restituendo al frontend un messaggio flash di benvenuto.
     *
     * @param string $uuid Authenticated account UUID.
     * @param array $identity Snapshot captured during authentication.
     * @param bool $rememberMe Whether the account requested a persistent login.
     * @param \CodeIgniter\HTTP\IncomingRequest $request Request supplying IP and user agent.
     * @param bool $restore Whether the user explicitly confirmed restoration.
     * @return array Authentication result.
     */
    private function innerLogin(string $uuid, array $identity, bool $rememberMe, \CodeIgniter\HTTP\IncomingRequest $request, bool $restore = false): array
    {
        if ($rememberMe):
            $time = (int) $this->config->rememberMeTime;
            $tokenType = 'cookie';
        else:
            $time = (int) $this->config->sessionTime;
            $tokenType = 'session';
        endif;

        $token = new \App\Libraries\Token();
        $tokenHash = $token->getHash($this->config->hashKey);

        /* Generazione delle stringhe DATETIME per users_tokens */
        $tokenCreate = date('Y-m-d H:i:s');
        $lastActivity = date('Y-m-d H:i:s');
        $tokenExpire = date('Y-m-d H:i:s', time() + $time);

        $userAgent = $request->getUserAgent()->getAgentString();
        $ip_address = $request->getIPAddress();

        /* Transazione autonoma per la creazione del login */
        if ( ! $this->db->transBegin()):
            throw new \RuntimeException('innerLogin: impossibile avviare la transazione.');
        endif;

        try {
            $sql = 'select uuid, firstname, lastname, email, password_hash, deleted_at, to_be_removed_at
                    from users where uuid = ? and status = 1 and password_hash is not null and suspended_at is null for update';
            $admin = $this->db->query($sql, [$uuid])->getRow();
            $now = date('Y-m-d H:i:s');
            if ($admin === null || $this->loginIdentity($admin) !== $identity
                || ($restore ? ($admin->deleted_at === null || $admin->to_be_removed_at === null || $admin->to_be_removed_at <= $now) : $admin->deleted_at !== null)):
                $this->db->transRollback();
                return ['result' => false, 'message' => lang($restore ? 'frontend/auth.messages.sessionExpired' : 'frontend/auth.messages.loginFailed')];
            endif;

            if ($restore):
                $this->db->query('update users set deleted_at = null, to_be_removed_at = null, updated_at = ? where uuid = ?', [$now, $uuid]);
                if ($this->db->affectedRows() !== 1):
                    throw new \RuntimeException('Account restoration did not update the user.');
                endif;
            endif;

            /* Pulizia dei vecchi token di sessione, se applicabile */
            if ($tokenType === 'session'):
                $sql = "update users_logs as l join users_tokens as t on t.id = l.token_id set l.logout = coalesce(t.last_activity, ?), l.logout_reason = 'timeout' where t.user_uuid = ? and t.token_type = ? and l.logout is null";
                $this->db->query($sql, [date('Y-m-d H:i:s'), $admin->uuid, 'session']);

                $sql = "delete from users_tokens where user_uuid = ? and token_type = ?";
                $this->db->query($sql, [$admin->uuid, 'session']);
            endif;

            /* Registrazione del nuovo token */
            $sql = "insert into users_tokens (user_uuid, token_hash, token_create, last_activity, token_expire, token_type, user_agent, ip_address, created_at) values (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $this->db->query($sql, [
                $admin->uuid,
                $tokenHash,
                $tokenCreate,
                $lastActivity,
                $tokenExpire,
                $tokenType,
                $userAgent,
                $ip_address,
                date('Y-m-d H:i:s')
            ]);

            $token_id = $this->db->insertID();

            /* Registrazione del log di accesso */
            $sql = "insert into users_logs (token_id, user_uuid, username, login, token_type, user_agent, ip_address, created_at) values (?, ?, ?, ?, ?, ?, ?, ?)";
            $this->db->query($sql, [
                $token_id,
                $admin->uuid,
                $admin->email,
                date('Y-m-d H:i:s'),
                $tokenType,
                $userAgent,
                $ip_address,
                date('Y-m-d H:i:s')
            ]);

            /* ID del log utilizzato successivamente durante il logout */
            $loginLogId = $this->db->insertID();

            /* Verifica dell'esito delle operazioni SQL */
            if ($this->db->transStatus() === false):
                $dbError = $this->db->error();
                throw new \RuntimeException('innerLogin: errore durante la registrazione del login. ' .($dbError['message'] ?? ''));
            endif;

            /* Conferma definitiva delle operazioni */
            if ( ! $this->db->transCommit()):
                throw new \RuntimeException('innerLogin: commit della transazione non riuscito.');
            endif;

        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }

        if ($restore):
            log_user_activity('CANCEL_ACCOUNT_REMOVAL', 'auth', lang('frontend/auth.audits.removalCancelled'), $admin);
        endif;

        /* Audit del login riuscito */
        log_user_activity('LOGIN_SUCCESS', 'auth', ui_message('frontend/auth.audits.loginSuccess', [esc($admin->firstname), esc($admin->lastname)], lang('frontend/auth.audits.loginSuccess')), $admin);

        /* Rigenerazione dell'ID di sessione contro Session Fixation */
        session()->regenerate(true);
        

        /* ID del log per il futuro logout */
        session()->set('frontend_login_log_id', $loginLogId);

        /* Assegnazione del token al client */
        if ($rememberMe):

            $encryptedToken = (new \App\Libraries\CryptoService($this->config->sessionCryptoKey))->encrypt($token->getValue());

            set_cookie([
                'name'     => 'frontendRememberMe',
                'value'    => $encryptedToken,
                'expire'   => $time,
                'httponly' => true,
                'secure'   => true,
                'samesite' => 'Lax'
            ]);

        else:

            session()->set('frontendSession', $token->getValue());

        endif;

        /* Messaggio di benvenuto */
        $welcomeMessage = ui_message('frontend/auth.messages.welcome', [esc($admin->firstname), esc($admin->lastname)], lang('frontend/auth.messages.welcome'));

        $welcomeBinding = \App\Libraries\Backend\InterfaceTranslations::binding($welcomeMessage);
        session()->setFlashdata(['message' => $welcomeMessage, 'message_translation' => $welcomeBinding !== null ? \App\Libraries\Backend\InterfaceTranslations::variants($welcomeBinding) : null, 'class' => 'app-alert-success fw-bold', 'icon' => '<i class="fa-solid fa-handshake"></i>']);

        return ['result' => true];
    }

    /**
     * Gestisce la richiesta di ripristino per "Password dimenticata".
     * 
     * Cerca l'utente tramite email, assicurandosi che non sia cestinato. Se trovato, 
     * inizia una transazione per generare un token di attivazione univoco, ne calcola la scadenza, 
     * lo salva a DB eliminando eventuali vecchi token pendenti e invia l'email transazionale 
     * contenente il link di recupero.
     *
     * @param array $posts Dati POST contenenti l'email
     * @param \CodeIgniter\HTTP\IncomingRequest $request Richiesta HTTP per logging dati connessione
     * @return array Esito dell'operazione e relativo messaggio utente
     */
    public function resetPassword(array $posts, \CodeIgniter\HTTP\IncomingRequest $request): array
    {
        $posts = $this->checkAllowedFields($posts, $this->resetPasswordAllowedFields);
        $posts['email'] = strtolower(trim($posts['email']));

        $successMessage = lang('frontend/auth.messages.resetPasswordSuccess');

        if ((bool) $this->config->resetAttempts):
            $attemptTransactionStarted = false;

            try {
                $emailHash = $this->getAttemptIdentifierHash($posts['email']);
                $ipAddress = $request->getIPAddress();
                $attemptStatus = $this->getAttemptsStatus(
                    'reset',
                    ['email_hash' => $emailHash],
                    $ipAddress,
                    (int) $this->config->resetAttemptsLimit,
                    (int) $this->config->resetAttemptsInterval
                );

                if ($attemptStatus['limited']):
                    if ( ! $this->db->transBegin()):
                        throw new \RuntimeException('resetPassword: impossibile avviare la transazione tentativi.');
                    endif;

                    $attemptTransactionStarted = true;
                    $this->renewAttemptsWindow('reset', ['email_hash' => $emailHash], $ipAddress, $attemptStatus);

                    if ( ! $this->db->transCommit()):
                        throw new \RuntimeException('resetPassword: commit tentativi non riuscito.');
                    endif;

                    $attemptTransactionStarted = false;

                    log_message('warning', 'Richiesta reset password limitata per IP/email hash.');
                    return ['result' => false, 'message' => lang('frontend/auth.messages.tooManyAttempts')];
                endif;

                if ( ! $this->db->transBegin()):
                    throw new \RuntimeException('resetPassword: impossibile avviare la transazione tentativi.');
                endif;

                $attemptTransactionStarted = true;
                $this->registerAttempt('reset', ['email_hash' => $emailHash], $ipAddress);

                if ( ! $this->db->transCommit()):
                    throw new \RuntimeException('resetPassword: commit tentativi non riuscito.');
                endif;

                $attemptTransactionStarted = false;
            } catch (\Throwable $e) {
                if ($attemptTransactionStarted):
                    $this->db->transRollback();
                endif;

                log_message('error', 'Errore controllo tentativi reset password - ' . $e);
                return ['result' => false, 'message' => lang('frontend/auth.messages.resetPasswordFailed')];
            }
        endif;

        $sql = "select uuid, firstname, lastname, email from users where email = ? and status = 1 and suspended_at is null and deleted_at is null limit 1";
        $admin = $this->db->query($sql, [$posts['email']])->getRow();

        if ($admin):

            /* 1. Transazione avviata solo se l'utente esiste (Ottimizzazione DB) */
            try {
                $token = new \App\Libraries\Token();
                $tokenHash = $token->getHash($this->config->hashKey);

                $time = (int) $this->config->activationTime;

                $tokenCreate = date('Y-m-d H:i:s');
                $tokenExpire = date('Y-m-d H:i:s', time() + $time);

                $this->db->transBegin();

                $sql = "update users set resetted_at = ? where uuid = ?";
                $this->db->query($sql, [date('Y-m-d H:i:s'), $admin->uuid]);

                /* Eliminiamo eventuali token di attivazione precedenti ancora attivi o scaduti per questo specifico admin */
                $sql = "delete from users_tokens where user_uuid = ? and token_type = ?";
                $this->db->query($sql, [$admin->uuid, 'activation']);

				$sql = "insert into users_tokens (user_uuid, token_hash, token_create, last_activity, token_expire, token_type, user_agent, ip_address, created_at) values(?,?,?,?,?,?,?,?,?)";
				$this->db->query($sql, [$admin->uuid, $tokenHash, $tokenCreate, null, $tokenExpire, 'activation', $request->getUserAgent()->getAgentString(), $request->getIPAddress(), date('Y-m-d H:i:s')]);

                if ($this->db->transStatus() === false):
                    $this->db->transRollback();
                    log_message('error', lang('frontend/auth.messages.resetPasswordFailed'));
                    return ['result' => false, 'message' => lang('frontend/auth.messages.resetPasswordFailed')];
                endif;

                $this->db->transCommit();

                log_user_activity('RESET_PASSWORD_AUTH', 'auth', ui_message('frontend/auth.audits.resetPasswordAuth', [esc($admin->firstname), esc($admin->lastname)], lang('frontend/auth.audits.resetPasswordAuth')), $admin);

            } catch (\Throwable $e) {
                $this->db->transRollback();
                log_message('error', lang('frontend/auth.messages.resetPasswordFailed') . ' - ' . $e);
                return ['result' => false, 'message' => lang('frontend/auth.messages.resetPasswordFailed')];
            }

            /* 2. Istanzio il servizio email dedicato e tento l'invio */
            $emailService = new \App\Libraries\Frontend\EmailService();

            /* Configuro i parametri dinamici per questa specifica chiamata */
            $module = $this->module;
            $template = 'emailResetPasswordPartial';
            $subjectLangKey = 'frontend/email.auth.resetPassword.subjectResetPasswordEmail';

            /* Chiamata al metodo con i nuovi parametri separati e gestione dei ritorni */
            if ( ! $emailService->sendActivationEmail($admin, $token->getValue(), $module, $template, $subjectLangKey)):

                try {
                    $this->db->transBegin();
                    $this->db->query("delete from users_tokens where user_uuid = ? and token_type = ?", [$admin->uuid, 'activation']);
                    $this->db->query("update users set resetted_at = ? where uuid = ?", [null, $admin->uuid]);
                    $this->db->transCommit();
                } catch (\Throwable $e) {
                    $this->db->transRollback();
                    log_message('error', 'Errore pulizia token reset password non inviato - ' . $e);
                }

                return ['result' => true, 'message' => $successMessage];
                
            else:
                
                return ['result' => true, 'message' => $successMessage];
                
            endif;

        endif;

        /* Risposta indistinguibile per impedire l'enumerazione degli account */
        return ['result' => true, 'message' => $successMessage];
    }

    /**
     * Calcola l'HMAC stabile dell'identificatore senza memorizzare l'email in chiaro.
     *
     * @param string $identifier Identificatore già normalizzato.
     * @return string Hash esadecimale SHA-256.
     */
    private function getAttemptIdentifierHash(string $identifier): string
    {
        return hash_hmac('sha256', $identifier, $this->config->hashKey);
    }

    /**
     * Restituisce lo stato della finestra tentativi per identificatore e indirizzo IP.
     *
     * Prima del conteggio elimina in modo uniforme i record scaduti. Il limite IP è
     * intenzionalmente più alto e deriva dal moltiplicatore interno non esposto nei Settings.
     *
     * @param string $scope Ambito supportato: login, verify oppure reset.
     * @param array<string, string|null> $identifier Identificatore specifico dell'ambito.
     * @param string $ipAddress Indirizzo IP della richiesta.
     * @param int $limit Limite configurato per l'identificatore.
     * @param int $interval Finestra temporale configurata in secondi.
     * @return array{limited:bool,identifier_limited:bool,ip_limited:bool,identifier_attempts:int,ip_attempts:int,cutoff:string}
     */
    private function getAttemptsStatus(string $scope, array $identifier, string $ipAddress, int $limit, int $interval): array
    {
        $limit = max(1, $limit);
        $interval = max(1, $interval);
        $ipLimit = $limit * max(1, (int) $this->config->attemptsIpMultiplier);
        $cutoff = date('Y-m-d H:i:s', time() - $interval);
        $table = $this->getAttemptsTable($scope);

        $this->db->query("delete from {$table} where timestamp <= ?", [$cutoff]);

        switch ($scope):
            case 'login':
            case 'reset':
                $sql = "select
                            count(case when email_hash = ? then 1 end) as identifier_attempts,
                            count(case when ip_address = ? then 1 end) as ip_attempts
                        from {$table}
                        where timestamp > ? and (email_hash = ? or ip_address = ?)";
                $params = [$identifier['email_hash'], $ipAddress, $cutoff, $identifier['email_hash'], $ipAddress];
                break;

            case 'verify':
                $sql = "select
                            count(case when user_uuid = ? and method = ? then 1 end) as identifier_attempts,
                            count(case when ip_address = ? then 1 end) as ip_attempts
                        from {$table}
                        where timestamp > ? and ((user_uuid = ? and method = ?) or ip_address = ?)";
                $params = [
                    $identifier['user_uuid'],
                    $identifier['method'],
                    $ipAddress,
                    $cutoff,
                    $identifier['user_uuid'],
                    $identifier['method'],
                    $ipAddress,
                ];
                break;

            default:
                throw new \InvalidArgumentException('Ambito tentativi non supportato.');
        endswitch;

        $row = $this->db->query($sql, $params)->getRow();
        $identifierAttempts = (int) ($row->identifier_attempts ?? 0);
        $ipAttempts = (int) ($row->ip_attempts ?? 0);

        $identifierLimited = $identifierAttempts >= $limit;
        $ipLimited = $ipAttempts >= $ipLimit;

        return [
            'limited' => $identifierLimited || $ipLimited,
            'identifier_limited' => $identifierLimited,
            'ip_limited' => $ipLimited,
            'identifier_attempts' => $identifierAttempts,
            'ip_attempts' => $ipAttempts,
            'cutoff' => $cutoff,
        ];
    }

    /**
     * Registra un tentativo nella tabella associata all'ambito richiesto.
     *
     * @param string $scope Ambito supportato: login, verify oppure reset.
     * @param array<string, string|null> $identifier Dati identificativi specifici dell'ambito.
     * @param string $ipAddress Indirizzo IP della richiesta.
     * @return void
     */
    private function registerAttempt(string $scope, array $identifier, string $ipAddress): void
    {
        $timestamp = date('Y-m-d H:i:s');

        switch ($scope):
            case 'login':
                $sql = "insert into users_attempts (user_uuid, email_hash, ip_address, timestamp) values (?, ?, ?, ?)";
                $params = [$identifier['user_uuid'], $identifier['email_hash'], $ipAddress, $timestamp];
                break;

            case 'verify':
                $sql = "insert into users_2fa_attempts (user_uuid, method, ip_address, timestamp) values (?, ?, ?, ?)";
                $params = [$identifier['user_uuid'], $identifier['method'], $ipAddress, $timestamp];
                break;

            case 'reset':
                $sql = "insert into users_reset_attempts (email_hash, ip_address, timestamp) values (?, ?, ?)";
                $params = [$identifier['email_hash'], $ipAddress, $timestamp];
                break;

            default:
                throw new \InvalidArgumentException('Ambito tentativi non supportato.');
        endswitch;

        $this->db->query($sql, $params);
    }

    /**
     * Rinnova l'intera finestra del gruppo che ha causato il blocco.
     *
     * @param string $scope Ambito supportato: login, verify oppure reset.
     * @param array<string, string|null> $identifier Identificatore specifico dell'ambito.
     * @param string $ipAddress Indirizzo IP della richiesta.
     * @param array{identifier_limited:bool,ip_limited:bool,cutoff:string} $status Stato del limite corrente.
     * @return void
     */
    private function renewAttemptsWindow(string $scope, array $identifier, string $ipAddress, array $status): void
    {
        $table = $this->getAttemptsTable($scope);
        $timestamp = date('Y-m-d H:i:s');

        switch ($scope):
            case 'login':
            case 'reset':
                if ($status['identifier_limited'] && $status['ip_limited']):
                    $sql = "update {$table} set timestamp = ? where timestamp > ? and (email_hash = ? or ip_address = ?)";
                    $params = [$timestamp, $status['cutoff'], $identifier['email_hash'], $ipAddress];
                elseif ($status['identifier_limited']):
                    $sql = "update {$table} set timestamp = ? where timestamp > ? and email_hash = ?";
                    $params = [$timestamp, $status['cutoff'], $identifier['email_hash']];
                else:
                    $sql = "update {$table} set timestamp = ? where timestamp > ? and ip_address = ?";
                    $params = [$timestamp, $status['cutoff'], $ipAddress];
                endif;
                break;

            case 'verify':
                if ($status['identifier_limited'] && $status['ip_limited']):
                    $sql = "update {$table} set timestamp = ? where timestamp > ? and ((user_uuid = ? and method = ?) or ip_address = ?)";
                    $params = [$timestamp, $status['cutoff'], $identifier['user_uuid'], $identifier['method'], $ipAddress];
                elseif ($status['identifier_limited']):
                    $sql = "update {$table} set timestamp = ? where timestamp > ? and user_uuid = ? and method = ?";
                    $params = [$timestamp, $status['cutoff'], $identifier['user_uuid'], $identifier['method']];
                else:
                    $sql = "update {$table} set timestamp = ? where timestamp > ? and ip_address = ?";
                    $params = [$timestamp, $status['cutoff'], $ipAddress];
                endif;
                break;

            default:
                throw new \InvalidArgumentException('Ambito tentativi non supportato.');
        endswitch;

        $this->db->query($sql, $params);
    }

    /**
     * Elimina i tentativi dopo il completamento positivo di Login o Verify.
     *
     * @param string $scope Ambito supportato: login oppure verify.
     * @param array<string, string|null> $identifier Identificatore specifico dell'ambito.
     * @return void
     */
    private function clearAttempts(string $scope, array $identifier): void
    {
        switch ($scope):
            case 'login':
                $this->db->query(
                    "delete from users_attempts where email_hash = ? or user_uuid = ?",
                    [$identifier['email_hash'], $identifier['user_uuid']]
                );
                break;

            case 'verify':
                $this->db->query(
                    "delete from users_2fa_attempts where user_uuid = ? and method = ?",
                    [$identifier['user_uuid'], $identifier['method']]
                );
                break;

            default:
                throw new \InvalidArgumentException('Pulizia tentativi non supportata per questo ambito.');
        endswitch;
    }

    /**
     * Risolve esclusivamente le tabelle autorizzate per evitare identificatori SQL dinamici arbitrari.
     *
     * @param string $scope Ambito supportato.
     * @return string Nome della tabella tentativi.
     */
    private function getAttemptsTable(string $scope): string
    {
        switch ($scope):
            case 'login':
                return 'users_attempts';

            case 'verify':
                return 'users_2fa_attempts';

            case 'reset':
                return 'users_reset_attempts';

            default:
                throw new \InvalidArgumentException('Ambito tentativi non supportato.');
        endswitch;
    }

    /**
     * Salva la nuova password dopo la convalida del token di reset/attivazione.
     * 
     * Recupera l'utente a partire dall'hash del token fornito. Esegue in transazione 
     * la rigenerazione dell'hash della password (`password_hash`), azzera la data di reset,
     * chiude i log aperti e revoca tutti i token attivi dell'utente.
     *
     * @param array $posts Dati POST contenenti la nuova password e il token
     * @return array Esito del salvataggio
     */
    public function setPassword(array $posts): array
    {
        $transactionStarted = false;

        try
        {
            $posts = $this->checkAllowedFields($posts, $this->setPasswordAllowedFields);

            /* 1. Recupero il token passato dal form (il nome deve combaciare con l'input hidden) */
            $token = new \App\Libraries\Token($posts['token']);
            $tokenHash = $token->getHash($this->config->hashKey);

            if ( ! $this->db->transBegin()):
                throw new \RuntimeException('setPassword: impossibile avviare la transazione.');
            endif;

            $transactionStarted = true;

            /* 2. Blocca il token valido per impedirne l'uso concorrente */
            $sql = "select u.uuid, u.firstname, u.lastname, u.email from users as u join users_tokens as t on u.uuid = t.user_uuid where t.token_hash = ? and t.token_type = ? and t.token_expire > ? and u.status = 1 and u.suspended_at is null and u.deleted_at is null limit 1 for update";
            $admin = $this->db->query($sql, [$tokenHash, 'activation', date('Y-m-d H:i:s')])->getRow();

            if($admin):

                $sql = "update users set password_hash = ?, resetted_at = ? where uuid = ?";
                $this->db->query($sql, [password_hash($posts['password'], PASSWORD_DEFAULT), null, $admin->uuid]);

                /* Chiude formalmente e revoca tutte le sessioni esistenti dell'account */
                $sql = "update users_logs as l join users_tokens as t on t.id = l.token_id set l.logout = ?, l.logout_reason = ? where t.user_uuid = ? and t.token_type in ('session', 'cookie') and l.logout_reason is null";
                $this->db->query($sql, [date('Y-m-d H:i:s'), 'manual', $admin->uuid]);

                $sql = "delete from users_tokens where user_uuid = ?";
                $this->db->query($sql, [$admin->uuid]);

                /* 4. Verifica stato transazione prima del commit */
                if ($this->db->transStatus() === false):
                    $this->db->transRollback();
                    $transactionStarted = false;
                    return ['result' => false, 'message' => lang('frontend/auth.messages.setPasswordError')];
                endif;

                if ( ! $this->db->transCommit()):
                    throw new \RuntimeException('setPassword: commit della transazione non riuscito.');
                endif;

                $transactionStarted = false;

                log_user_activity('SET_PASSWORD', 'auth', ui_message('frontend/auth.audits.setPassword', [esc($admin->firstname), esc($admin->lastname)], lang('frontend/auth.audits.setPassword')), $admin);

                $message = ui_message('frontend/auth.messages.setPasswordSuccess', [esc($admin->firstname), esc($admin->lastname)], lang('frontend/auth.messages.setPasswordSuccess'));

                return ['result' => true, 'message' => $message];

            endif;

            $this->db->transRollback();
            $transactionStarted = false;

            return ['result' => false, 'message' => lang('frontend/auth.messages.setPasswordFailed')];

        } catch (\Throwable $e) {

            /* 5. Rollback di sicurezza solo se la transazione era effettivamente in corso */
            if ($transactionStarted):
                $this->db->transRollback();
            endif;

            log_message('error', lang('frontend/auth.messages.setPasswordError') . ' - ' . $e);

            /* Modificato false in 'setPasswordFailed' per coerenza con le aspettative del Controller */
            return ['result' => false, 'message' => lang('frontend/auth.messages.setPasswordError')];
        }
    }

    /**
     * Verifica l'autenticità e la validità temporale di un token di reset/attivazione.
     * 
     * Metodo di utilità usato frequentemente per proteggere le rotte (es. form di reset password). 
     * Controlla che il token esista nel database e che il timestamp attuale non abbia 
     * superato la data di scadenza prestabilita (`token_expire`).
     *
     * @param string $token La stringa raw del token da verificare
     * @return bool True se il token è valido e non scaduto, false altrimenti
     */
    public function checkAuthToken(string $token): bool
    {
        try 
        {
            $tokenObj = new \App\Libraries\Token($token);
            $tokenHash = $tokenObj->getHash($this->config->hashKey);

            $sql = "select t.token_expire, t.user_uuid, u.password_hash, u.email  
                from users as u 
                join users_tokens as t 
                on t.user_uuid = u.uuid 
                where t.token_hash = ? 
                and t.token_type = ?
                and u.status = 1
                and u.suspended_at is null
                and u.deleted_at is null
                limit 1";

            $query = $this->db->query($sql, [$tokenHash, 'activation'])->getRow();

            if (($query) && (date('Y-m-d H:i:s') < $query->token_expire)):
                return true;
            endif;

            return false;

        } catch (\Throwable $e) {
            log_message('error', lang('frontend/auth.messages.checkAuthError') . ' - ' . $e);
            return false;
        }
    }

    /**
     * Recupera la scadenza Unix dell'ultimo codice OTP email generato per l'utente.
     *
     * @param string $userUuid Identificatore univoco dell'utente
     * @return int|null Timestamp Unix della scadenza oppure null se il codice non è disponibile
     */
    public function getEmailOtpExpiresAt(string $userUuid): ?int
    {
        $sql = "select expires_at from users_2fa_codes where user_uuid = ? order by id desc limit 1";
        $row = $this->db->query($sql, [$userUuid])->getRow();

        if ( ! $row || empty($row->expires_at)):
            return null;
        endif;

        $expiresAt = strtotime((string) $row->expires_at);

        return $expiresAt !== false ? $expiresAt : null;
    }

    /**
     * Convalida l'inserimento del codice OTP (Autenticazione a Due Fattori).
     * 
     * Estrae in sicurezza i dati pre-autorizzati dalla sessione temporanea `frontend_auth_2fa_pending`. 
     * Applica il Throttling specifico per il 2FA: se si sbaglia il codice troppe volte, rinnova il blocco e respinge.
     * Verifica il codice immesso valutando sia la corrispondenza (Tramite App Authenticator o DB per OTP Email) 
     * sia la scadenza temporale. In caso di errore, incrementa i fallimenti in transazione. 
     * In caso di successo, svuota le tabelle temporanee, distrugge la sessione di parcheggio e 
     * finalizza l'ingresso invocando `innerLogin()`.
     *
     * @param array $posts Il payload contenente il codice (OTP)
     * @param \CodeIgniter\HTTP\IncomingRequest $request Richiesta HTTP per l'estrazione dell'IP
     * @return array Esito dell'operazione (successo o messaggio d'errore specifico se scaduto o errato)
     */
    public function verify(array $posts, \CodeIgniter\HTTP\IncomingRequest $request): array
    {
        $transactionStarted = false;

        try 
        {
            $posts = $this->checkAllowedFields($posts, $this->verifyAllowedFields);

            $config = $this->config;
            $ip_address = $request->getIPAddress();
            $allowTwoFactorAttempts = (bool) $config->twoFactorAttempts;

            /* Recupero e validazione immediata della sessione protetta temporanea */
            $sessionData = session()->get('frontend_auth_2fa_pending');
            if ( ! is_array($sessionData) || ! isset($sessionData['user_uuid'], $sessionData['method'], $sessionData['identity'], $sessionData['created_at'])):
                session()->remove('frontend_auth_2fa_pending');
                return ['result' => false, 'message' => lang('frontend/auth.messages.sessionExpired')];
            endif;

            if ((time() - (int) $sessionData['created_at']) > (int) $config->twoFactorTime):
                $this->db->query("delete from users_2fa_codes where user_uuid = ?", [(string) $sessionData['user_uuid']]);
                session()->remove('frontend_auth_2fa_pending');
                return ['result' => false, 'message' => lang('frontend/auth.messages.sessionExpired')];
            endif;

            $userUuid  = (string) $sessionData['user_uuid'];
            $method     = (string) $sessionData['method'];
            $rememberMe = (bool) $sessionData['rememberMe'];

            $sql = 'select uuid, firstname, lastname, email, password_hash, deleted_at, to_be_removed_at
                    from users where uuid = ? and status = 1 and password_hash is not null and suspended_at is null
                    and (deleted_at is null or to_be_removed_at > ?) limit 1';
            $admin = $this->db->query($sql, [$userUuid, date('Y-m-d H:i:s')])->getRow();
            if ($admin === null || $this->loginIdentity($admin) !== $sessionData['identity']):
                session()->remove('frontend_auth_2fa_pending');
                log_user_activity('VERIFY_FAILED', 'auth', lang('frontend/auth.audits.verifyFailed'));
                return ['result' => false, 'message' => lang('frontend/auth.messages.loginFailed')];
            endif;

            if ($allowTwoFactorAttempts):
                $attemptIdentifier = ['user_uuid' => $userUuid, 'method' => $method];
                $attemptStatus = $this->getAttemptsStatus(
                    'verify',
                    $attemptIdentifier,
                    $ip_address,
                    (int) $config->twoFactorLimit,
                    (int) $config->twoFactorAttemptsInterval
                );

                if ($attemptStatus['limited']):
                    if ( ! $this->db->transBegin()):
                        throw new \RuntimeException('verify: impossibile avviare la transazione tentativi.');
                    endif;

                    $transactionStarted = true;
                    $this->renewAttemptsWindow('verify', $attemptIdentifier, $ip_address, $attemptStatus);

                    if ( ! $this->db->transCommit()):
                        throw new \RuntimeException('verify: commit tentativi non riuscito.');
                    endif;

                    $transactionStarted = false;

                    log_user_activity('2FA_BLOCKED', 'auth', ui_message('frontend/auth.audits.2faBlocked', [esc($admin->firstname), esc($admin->lastname)], lang('frontend/auth.audits.2faBlocked')), $admin);

                    return ['result' => false, 'message' => lang('frontend/auth.messages.tooManyAttempts')];
                endif;
            endif;

            /* Validazione del codice OTP (TOTP o Email) */
            $isValidCode = false;
            $isExpired = false; 

            if ($method === 'totp'):
                $sql = "select secret from users_2fa where user_uuid = ? and method = 'totp' and enabled = 1 limit 1";
                $row = $this->db->query($sql, [$userUuid])->getRow();

				if ($row && ! empty($row->secret)):
					$otpService = new \App\Libraries\Frontend\AppOtpService();
					$secret = $otpService->revealSecret((string) $row->secret);

					if ($secret !== null):
						$isValidCode = $otpService->verify($secret, $posts['code']);
					endif;
                endif;

            elseif ($method === 'email'):
                /* Prima query: controlliamo solo se il codice inserito esiste per questo utente */
                $sql = "select expires_at from users_2fa_codes where user_uuid = ? and code = ? limit 1";
                $row = $this->db->query($sql, [$userUuid, $posts['code']])->getRow();

                if ( ! $row):
                    /* Il codice non esiste nel DB: è sbagliato */
                    $isValidCode = false;
                else:
                    /* Il codice esiste! Adesso controlliamo se è scaduto rispetto a questo momento */
                    if (date('Y-m-d H:i:s') > $row->expires_at):
                        $isValidCode = false;
                        $isExpired = true; /* Segnaliamo che il problema è il tempo */
                    else:
                        $isValidCode = true;
                    endif;
                endif;
            endif;

            if ( ! $isValidCode):
                /* Qualsiasi fallimento conta come tentativo errato per la sicurezza */
                if ($allowTwoFactorAttempts):
                    if ( ! $this->db->transBegin()):
                        throw new \RuntimeException('verify: impossibile avviare la transazione tentativi.');
                    endif;

                    $transactionStarted = true;
                    $this->registerAttempt('verify', ['user_uuid' => $userUuid, 'method' => $method], $ip_address);

                    if ( ! $this->db->transCommit()):
                        throw new \RuntimeException('verify: commit tentativo non riuscito.');
                    endif;

                    $transactionStarted = false;
                endif;

                log_user_activity('2FA_FAILED', 'auth', ui_message('frontend/auth.audits.2faFailed', [esc($admin->firstname), esc($admin->lastname)], lang('frontend/auth.audits.2faFailed')), $admin);

                /* Scegliamo il messaggio specifico in base allo stato */
                $errorMessage = $isExpired ? lang('frontend/auth.messages.expiredCode') : lang('frontend/auth.messages.wrongCode');

                return ['result' => false, 'message' => $errorMessage];
            endif;

            /* Codice Corretto: Pulizia tabelle temporanee dell'utente */
            if ( ! $this->db->transBegin()):
                throw new \RuntimeException('verify: impossibile avviare la transazione di verifica.');
            endif;

            $transactionStarted = true;

            if ($allowTwoFactorAttempts):
                $this->clearAttempts('verify', ['user_uuid' => $userUuid, 'method' => $method]);
            endif;

            $sql = "delete from users_2fa_codes where user_uuid = ?";
            $this->db->query($sql, [$userUuid]);

            if ( ! $this->db->transCommit()):
                throw new \RuntimeException('verify: commit verifica non riuscito.');
            endif;

            $transactionStarted = false;

            /* Rimozione tassativa della sessione temporanea e Finalizzazione Login */
            session()->remove('frontend_auth_2fa_pending');

            return $this->completeAuthentication($admin, $rememberMe, $request, $sessionData['redirect'] ?? base_url('account'));

        } catch (\Throwable $e) {

            /* Annulla un'eventuale transazione ancora aperta */
            if ($transactionStarted):
                $this->db->transRollback();
            endif;

            log_message('error', 'Errore nel metodo verify 2FA: ' . $e);

            return ['result' => false, 'message' => lang('frontend/auth.messages.verifyError')];
        }
    }

    /**
     * Esegue il Logout (Disconnessione) per gli utenti autenticati via Sessione standard.
     *
     * Recupera il token dalla sessione locale, identifica il log aperto nella tabella `users_logs`
     * (tramite `login_log_id` salvato durante il login) e lo chiude formalmente inserendo
     * l'ora di uscita (`last_activity`) e la causale (es. 'manual' o 'timeout').
     * Infine distrugge il record del token dal database e resetta completamente la sessione PHP locale.
     *
     * @param string $reason Motivo della disconnessione (default: 'manual')
     * @return void
     */
    public function logoutBySession(string $reason = 'manual'): void
    {
        
        try {
            if (session()->has('frontendSession')):
                
                $sessionValue = session()->get('frontendSession');
                $token = new \App\Libraries\Token($sessionValue);
                $tokenHash = $token->getHash($this->config->hashKey);

                $this->closeLoginLog($tokenHash, 'session', $reason);

                /* Elimino fisicamente il token (se non è già stato eliminato da altri) */
                $sqlDelete = "delete from users_tokens where token_hash = ? and token_type = ?";
                $this->db->query($sqlDelete, [$tokenHash, 'session']);

                /* Distruzione sessione locale */
                session()->remove('frontendSession');
                session()->regenerate(true);

            endif;
        } catch (\Throwable $e) {
            log_message('error', lang('frontend/auth.messages.logoutError') . ' - ' . $e);
        }
    }

    /**
     * Esegue il Logout (Disconnessione) per gli utenti autenticati via Cookie "Ricordami".
     *
     * Funzionamento analogo a `logoutBySession`, ma estrae l'hash partendo dal valore crittografato
     * del cookie inviato dal browser. Chiude il log aperto registrando il timestamp di logout
     * ed elimina definitivamente il token di tipo 'cookie' dal database, invalidando gli accessi futuri.
     *
     * @param string $cookieValue Il valore crittografato del cookie 'frontendRememberMe'
     * @param string $reason Motivo della disconnessione (default: 'manual')
     * @return void
     */
    public function logoutByCookie(string $cookieValue, string $reason = 'manual'): void
    {
        
        try {
            $decryptedValue = (new \App\Libraries\CryptoService($this->config->sessionCryptoKey))->decrypt($cookieValue);

            if ( ! $decryptedValue):
                return;
            endif;

            $token = new \App\Libraries\Token($decryptedValue);
            $tokenHash = $token->getHash($this->config->hashKey);

            $this->closeLoginLog($tokenHash, 'cookie', $reason);

            /* Elimino fisicamente il token (se non è già stato eliminato da altri) */
            $sqlDelete = "delete from users_tokens where token_hash = ? and token_type = ?";
            $this->db->query($sqlDelete, [$tokenHash, 'cookie']);

        } catch (\Throwable $e) {
            log_message('error', lang('frontend/auth.messages.logoutError') . ' - ' . $e);
        } finally {
            session()->remove('frontend_login_log_id');
            delete_cookie('frontendRememberMe');
        }
    }

    /**
     * Chiude il log di accesso associato a un token senza sovrascrivere log già conclusi.
     *
     * @param string $tokenHash Hash del token di autenticazione.
     * @param string $tokenType Tipo del token: session oppure cookie.
     * @param string $reason Motivo della disconnessione.
     * @return void
     */
    private function closeLoginLog(string $tokenHash, string $tokenType, string $reason): void
    {
        $sqlSelect = "select id, last_activity from users_tokens where token_hash = ? and token_type = ?";
        $tokenRow = $this->db->query($sqlSelect, [$tokenHash, $tokenType])->getRow();
        $logoutTime = ($tokenRow && $reason === 'timeout' && ! empty($tokenRow->last_activity))
            ? $tokenRow->last_activity
            : date('Y-m-d H:i:s');

        if (session()->has('frontend_login_log_id')):
            $logId = session()->get('frontend_login_log_id');
            $sqlLogUpdate = "update users_logs set logout = ?, logout_reason = ? where id = ? and logout is null";
            $this->db->query($sqlLogUpdate, [$logoutTime, $reason, $logId]);
            session()->remove('frontend_login_log_id');
        elseif ($tokenRow):
            $sqlLogUpdate = "update users_logs set logout = ?, logout_reason = ? where token_id = ? and logout is null";
            $this->db->query($sqlLogUpdate, [$logoutTime, $reason, $tokenRow->id]);
        endif;
    }

    /**
     * Valida l'anagrafica senza accettare stato, ruoli o identificativi dal browser.
     *
     * @return array
     */
    public function registerValidationRules(): array
    {
        $rules = model(AccountModel::class)->editValidationRules('');
        unset($rules['note']);
        $rules['email']['rules'] = ['required', 'trim', 'valid_email', 'max_length[255]'];
        return $rules;
    }

    /**
     * Crea un account in attesa di password e invia il collegamento di attivazione.
     *
     * @param array $posts Payload dell'operazione sottoposto alla whitelist.
     * @param \CodeIgniter\HTTP\IncomingRequest $request Richiesta HTTP corrente.
     * @return array
     */
    public function register(array $posts, \CodeIgniter\HTTP\IncomingRequest $request): array
    {
        $posts = $this->checkAllowedFields($posts, ['firstname', 'lastname', 'email', 'phone']);
        $posts['email'] = strtolower(trim((string) $posts['email']));
        $success = ['result' => true, 'message' => lang('frontend/auth.messages.registerSuccess')];
        $now = date('Y-m-d H:i:s');
        try {
            /* Limita gli invii anche per indirizzi inesistenti, senza salvare email in chiaro. */
            $emailHash = $this->getAttemptIdentifierHash($posts['email']);
            $from = date('Y-m-d H:i:s', time() - (int) $this->config->registrationInterval);
            $sql = "select count(*) as total from users_registration_attempts where timestamp >= ? and (email_hash = ? or ip_address = ?)";
            $attempts = $this->db->query($sql, [$from, $emailHash, $request->getIPAddress()])->getRow();
            if ((int) $attempts->total >= (int) $this->config->registrationLimit):
                return ['result' => false, 'message' => lang('frontend/auth.messages.tooManyAttempts')];
            endif;
            $this->db->query("insert into users_registration_attempts (email_hash, ip_address, timestamp) values (?, ?, ?)", [$emailHash, $request->getIPAddress(), $now]);
            $this->db->transBegin();
            $row = $this->db->query("select uuid, firstname, lastname, email, password_hash, status, suspended_at, deleted_at from users where email = ? limit 1 for update", [$posts['email']])->getRow();
            if ($row && ($row->password_hash !== null || (int) $row->status !== 1 || $row->suspended_at !== null || $row->deleted_at !== null)):
                $this->db->transRollback();
                return $success;
            endif;
            if ( ! $row):
                $uuid = $this->generateUUID();
                $sql = "insert into users (uuid, firstname, lastname, email, phone, status, created_at) values (?, ?, ?, ?, ?, 1, ?)";
                $this->db->query($sql, [$uuid, trim($posts['firstname']), trim($posts['lastname']), $posts['email'], trim($posts['phone']), $now]);
                $row = (object) ['uuid' => $uuid, 'firstname' => trim($posts['firstname']), 'lastname' => trim($posts['lastname']), 'email' => $posts['email']];
            endif;
            $token = new \App\Libraries\Token();
            $hash = $token->getHash($this->config->hashKey);
            $expires = date('Y-m-d H:i:s', time() + (int) $this->config->activationTime);
            $sql = "insert into users_tokens (user_uuid, token_hash, token_create, token_expire, token_type, user_agent, ip_address, created_at) values (?, ?, ?, ?, 'activation', ?, ?, ?)";
            $this->db->query($sql, [$row->uuid, $hash, $now, $expires, $request->getUserAgent()->getAgentString(), $request->getIPAddress(), $now]);
            if ($this->db->transStatus() === false || ! $this->db->transCommit()):
                throw new \RuntimeException('Registrazione utente non confermata.');
            endif;
            $email = new \App\Libraries\Frontend\EmailService();
            if ( ! $email->sendActivationEmail($row, $token->getValue(), 'auth', 'emailRegisterPartial', 'frontend/auth.email.registerSubject')):
                /* Una nuova registrazione puo ritentare; non invalida token di altri invii. */
                $this->db->query("delete from users_tokens where user_uuid = ? and token_hash = ? and token_type = 'activation'", [$row->uuid, $hash]);
            endif;
            return $success;
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', 'Errore registrazione utenti - ' . $e->getMessage());
            return ['result' => false, 'message' => lang('frontend/auth.messages.registerError')];
        }
    }

}
