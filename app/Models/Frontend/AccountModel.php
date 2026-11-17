<?php declare(strict_types = 1);

namespace App\Models\Frontend;


/**
 * Valida e salva il profilo e le impostazioni di sicurezza del cliente autenticato.
 */
class AccountModel extends FrontendModel
{
    private object $config;
    protected ?string $module = 'account';
    protected array $editAllowedFields = ['firstname', 'lastname', 'email', 'phone', 'note'];
    protected array $deleteTokenAllowedFields = ['id'];
    protected array $toCompare = ['firstname', 'lastname', 'email', 'phone', 'note'];

    /**
     * Inizializza il model e le configurazioni utilizzate dalle sue operazioni.
     *
     * @return void
     */
    protected function initModel(): void 
    {
    	parent::initModel();

    	$this->config = setting('Frontend\Auth');
    }

	/**
	 * Definisce le regole del profilo e il controllo di unicita dell'email.
	 *
	 * @param string $userUuid UUID del cliente interessato.
	 * @return array
	 */
	public function editValidationRules(string $userUuid): array
	{
	    return [
	        'firstname' => [
	            'label' => lang('frontend/account.labels.firstname'),
	            'rules' => ['required', 'trim', 'min_length[2]', 'max_length[30]', 'regex_match[/^[\p{L}\p{M}]+(?:[ \'\x{2018}\x{2019}`\-\x{2010}\x{2011}\x{2012}\x{2013}\x{2014}][\p{L}\p{M}]+)*$/u]'],
	        ],
	        'lastname' => [
	            'label' => lang('frontend/account.labels.lastname'),
	            'rules' => ['required', 'trim', 'min_length[2]', 'max_length[30]', 'regex_match[/^[\p{L}\p{M}]+(?:[ \'\x{2018}\x{2019}`\-\x{2010}\x{2011}\x{2012}\x{2013}\x{2014}][\p{L}\p{M}]+)*$/u]'],
	        ],
	        'email' => [
	            'label' => lang('frontend/account.labels.email'),
	            'rules' => ['required', 'trim', 'valid_email', 'max_length[255]', "is_unique[users.email,uuid,{$userUuid}]"],
	        ],
	        'phone' => [
	            'label' => lang('frontend/account.labels.phone'),
	            'rules' => ['required', 'trim', 'regex_match[/^\+?[0-9]{9,15}$/]'],
	        ],
	        'note' => [
	            'label' => lang('frontend/account.labels.note'),
	            'rules' => ['permit_empty', 'trim', 'max_length[500]', 'safeText'],
	            'errors' => [
	                'safeText' => lang('frontend/account.errors.safeText')
	            ]
	        ],
	    ];
	}

	/**
	 * Definisce i vincoli per la password personale richiesta in conferma.
	 *
	 * @return array
	 */
	public function currentPasswordValidationRules(): array
	{
		return [
			'currentPassword' => [
				'label' => lang('frontend/account.labels.currentPassword'),
				'rules' => ['required', "regex_match[{$this->config->passwordRegex}]"],
				'errors' => [
					'required' => lang('frontend/account.errors.currentPasswordRequired'),
				],
			],
		];
	}

	/**
	 * Definisce i vincoli dell'identificativo del token da revocare.
	 *
	 * @return array
	 */
	public function deleteTokenValidationRules(): array
	{
	    return [
	        'id' => [
	            'label' => lang('frontend/account.labels.id'),
	            'rules' => ['required', 'is_natural_no_zero'],
	            'errors' => [
	                'required' => lang('frontend/account.errors.id'), 
	                'is_natural_no_zero' => lang('frontend/account.errors.id') 
	            ]
	        ],
	    ];
	}

	/**
	 * Recupera i token appartenenti al cliente indicato.
	 *
	 * @param string $uuid UUID del cliente o del proprietario delle immagini.
	 * @return array
	 */
	public function getTokens(string $uuid): array
	{
	    /* Estrazione log dei tokens di sessione o reset */
	    $sql = "select * from users_tokens where user_uuid = ?";
	    return $this->db->query($sql, [$uuid])->getResult();
	}

	/**
	 * Aggiorna i soli campi consentiti del profilo e ricarica l'identita corrente.
	 *
	 * @param array $posts Payload dell'operazione sottoposto alla whitelist.
	 * @param \stdClass $currentUser Identita del cliente autenticato, ottenuta sul server.
	 * @return array
	 */
	public function edit(array $posts, \stdClass $currentUser): array 
	{
		$transactionStarted = false;

	    try {

	        $posts = $this->checkAllowedFields($posts, $this->editAllowedFields);

	        /* Se non è stato effettuato alcun cambio sui dati gestiti, interrompiamo subito */
	        if ( ! $this->hasDataChanged($posts, $currentUser)):
	            return ['result' => false, 'message' => lang('frontend/account.messages.noDataChanged')];
	        endif;

	        $updated_at = date('Y-m-d H:i:s');
	        
	        /* Gestione pulita della stringa vuota in NULL per la textarea note */
	        $note = isset($posts['note']) ? trim($posts['note']) : '';
	        $noteValue = ($note === '') ? null : $note;

	        if ( ! $this->db->transBegin()):
	            throw new \RuntimeException('Impossibile avviare la transazione di aggiornamento account.');
	        endif;

	        $transactionStarted = true;

	        $sql = 'update users set firstname = ?, lastname = ?, email = ?, phone = ?, note = ?, updated_at = ? where uuid = ? and status = 1 and suspended_at is null and deleted_at is null';
	        $this->db->query($sql, [$posts['firstname'], $posts['lastname'], $posts['email'], $posts['phone'], $noteValue, $updated_at, $currentUser->uuid]);

	        if ($this->db->affectedRows() !== 1 || $this->db->transStatus() === false):
	            throw new \RuntimeException('Errore durante l\'aggiornamento dell\'account.');
	        endif;

	        if ( ! $this->db->transCommit()):
	            throw new \RuntimeException('Commit dell\'aggiornamento account non riuscito.');
	        endif;

	        $transactionStarted = false;

	        /* Ricarichiamo l'istanza dell'admin aggiornata per passarla al controller */
	        $currentUser = service('userAuthorization')->refresh()->currentUser();
	        log_user_activity('UPDATE_DATA', 'account', ui_message('frontend/account.audits.updateData', [esc($currentUser->firstname), esc($currentUser->lastname)], lang('frontend/account.audits.updateData')), $currentUser);

	        return [
	            'result' => true, 
	            'message' => ui_message('frontend/account.messages.editSuccess', [esc($currentUser->firstname), esc($currentUser->lastname)], lang('frontend/account.messages.editSuccess')), 
	            'currentUser' => $currentUser
	        ];

	    } catch(\Throwable $e) {
	        if ($transactionStarted):
	            $this->db->transRollback();
	        endif;

	        log_message('error', lang('frontend/account.messages.editError') . ' - ' . $e->getMessage() . ' | File: ' . $e->getFile() . ' | Riga: ' . $e->getLine());
	        return ['result' => false, 'message' => lang('frontend/account.messages.editError')];
	    }
	}

    /**
     * Verifica la password del cliente limitando i tentativi non riusciti.
     *
     * @param string $userUuid UUID del cliente interessato.
     * @param string $currentPassword Password personale da verificare.
     * @return bool
     */
    public function verifyCurrentPassword(string $userUuid, string $currentPassword): bool
    {
        $request = service('request');
        $key = 'frontend-current-password-' . hash('sha256', $userUuid . '|' . $request->getIPAddress());
        $throttler = service('throttler');
        if ( ! $throttler->check($key, (int) $this->config->attemptsLimit, (int) $this->config->attemptsInterval, 0)):
            return false;
        endif;
        $sql = "select password_hash from users where uuid = ? and status = 1 and suspended_at is null and deleted_at is null limit 1";
        $row = $this->db->query($sql, [$userUuid])->getRow();
        $valid = $row && is_string($row->password_hash) && password_verify($currentPassword, $row->password_hash);
        if ( ! $valid):
            $throttler->check($key, (int) $this->config->attemptsLimit, (int) $this->config->attemptsInterval, 1);
        endif;
        return (bool) $valid;
    }

    /**
     * Recupera l'identificativo del token che autentica la richiesta corrente.
     *
     * @return int|null
     */
    public function getCurrentTokenId(): ?int
    {
        return service('userAuthorization')->currentTokenId();
    }

	/**
	 * Revoca un token di proprieta del cliente, proteggendo l'accesso corrente.
	 *
	 * @param array $posts Payload dell'operazione sottoposto alla whitelist.
	 * @param \stdClass $currentUser Identita del cliente autenticato, ottenuta sul server.
	 * @param int|null $currentTokenId Token dell'accesso corrente da preservare.
	 * @return array
	 */
	public function deleteToken(array $posts, \stdClass $currentUser, ?int $currentTokenId = null): array
    {
        /* Match dei posts con i campi consentiti */
        $posts = $this->checkAllowedFields($posts, $this->deleteTokenAllowedFields);

        /* SBARRAMENTO DI SICUREZZA: Impedisce l'eliminazione esclusiva del token di sessione in uso */
        if ($currentTokenId !== null && (int) $posts['id'] === $currentTokenId):
            log_message('warning', 'Tentativo bloccato: l\'utente ha tentato di eliminare il token della sessione in uso.');
            return ['result' => false, 'message' => lang('frontend/account.messages.cannotDeleteCurrentToken')];
        endif;

        $transactionStarted = false;

        try {

			if ( ! $this->db->transBegin()):
				throw new \RuntimeException('Impossibile avviare la transazione di revoca token.');
			endif;

			$transactionStarted = true;

            /* 1. Recupero il token per leggere last_activity */
			$tokenSql = "select id, last_activity, token_type from users_tokens where user_uuid = ? and id = ? for update";
            $tokenRow = $this->db->query($tokenSql, [$currentUser->uuid, $posts['id']])->getRow();

			if ( ! $tokenRow):
				throw new \RuntimeException('Token non trovato o non appartenente all\'account corrente.');
            endif;

			if ($currentTokenId !== null && (int) $tokenRow->id === $currentTokenId):
				$this->db->transRollback();
				$transactionStarted = false;
				return ['result' => false, 'message' => lang('frontend/account.messages.cannotDeleteCurrentToken')];
			endif;

			/* 2. Aggiorno il log se è una sessione o cookie */
			if (in_array($tokenRow->token_type, ['cookie', 'session'], true)):
				$logoutTime = ! empty($tokenRow->last_activity) ? $tokenRow->last_activity : date('Y-m-d H:i:s');
				$logUpdateSql = "update users_logs set logout = ?, logout_reason = 'deleted' where token_id = ? and logout is null";
				$this->db->query($logUpdateSql, [$logoutTime, $tokenRow->id]);
			endif;

            /* 3. Elimino fisicamente il token */
            $sql = "delete from users_tokens where user_uuid = ? and id = ?";
            $this->db->query($sql, [$currentUser->uuid, $posts['id']]);

			if ($this->db->affectedRows() !== 1 || $this->db->transStatus() === false):
				throw new \RuntimeException('Revoca token non riuscita.');
            endif;

			if ( ! $this->db->transCommit()):
				throw new \RuntimeException('Commit della revoca token non riuscito.');
			endif;

			$transactionStarted = false;

			log_user_activity('DELETE_TOKEN', 'account', ui_message('frontend/account.audits.deleteToken', [esc($currentUser->firstname), esc($currentUser->lastname)], lang('frontend/account.audits.deleteToken')), $currentUser);
			return ['result' => true, 'message' => ui_message('frontend/account.messages.deleteTokenSuccess', [esc($currentUser->firstname), esc($currentUser->lastname)], lang('frontend/account.messages.deleteTokenSuccess'))];

        } catch(\Throwable $e) {
			if ($transactionStarted):
				$this->db->transRollback();
			endif;

            log_message('error', lang('frontend/account.messages.deleteTokenError') . ' - ' . $e);
            return ['result' => false, 'message' => lang('frontend/account.messages.deleteTokenError')];

        }
    }

	/**
	 * Registra il recupero credenziali del cliente e invia il collegamento personale.
	 *
	 * @param \stdClass $currentUser Identita del cliente autenticato, ottenuta sul server.
	 * @param \CodeIgniter\HTTP\IncomingRequest $request Richiesta HTTP corrente.
	 * @return array
	 */
	public function resetPassword(\stdClass $currentUser, \CodeIgniter\HTTP\IncomingRequest $request): array
	{
		$transactionStarted = false;

	    try 
	    {
	        $userAgent = $request->getUserAgent()->getAgentString();
	        $ip_address = $request->getIPAddress();

	        /* Generazione token di attivazione */
	        $token = new \App\Libraries\Token();
	        $tokenHash = $token->getHash(config(\Config\Frontend\Auth::class)->hashKey);

	        /* 2. Calcolo corretto della scadenza lavorando sui secondi (timestamp) */
	        $expireTime = date('Y-m-d H:i:s', time() + setting('Frontend\Auth')->activationTime);

	        if ( ! $this->db->transBegin()):
	            throw new \RuntimeException('Impossibile avviare la transazione di reset password.');
	        endif;

	        $transactionStarted = true;

	        /* Scrittura Data di Reset nella tabella users */
	        $sql = "update users set resetted_at = ? where uuid = ?";
	        $this->db->query($sql, [date('Y-m-d H:i:s'), $currentUser->uuid]);

	        /* Eliminiamo eventuali token di attivazione precedenti ancora attivi o scaduti per questo specifico admin */
	        $sql = "delete from users_tokens where user_uuid = ? and token_type = ?";
	        $this->db->query($sql, [$currentUser->uuid, 'activation']);

	        /* Scrittura del token di attivazione */
			$sql = "insert into users_tokens (user_uuid, token_hash, token_create, last_activity, token_expire, token_type, user_agent, ip_address, created_at) values (?, ?, ?, ?, ?, ?, ?, ?, ?)";
			$this->db->query($sql, [$currentUser->uuid, $tokenHash, date('Y-m-d H:i:s'), null, $expireTime, 'activation', $userAgent, $ip_address, date('Y-m-d H:i:s')]);

	        if ($this->db->transStatus() === false):

	            throw new \RuntimeException('Errore durante la registrazione del reset password.');
	        endif;

	        if ( ! $this->db->transCommit()):
	            throw new \RuntimeException('Commit del reset password non riuscito.');
	        endif;

	        $transactionStarted = false;

	        log_user_activity('RESET_PASSWORD', 'account', ui_message('frontend/account.audits.resetPassword', [esc($currentUser->firstname), esc($currentUser->lastname)], lang('frontend/account.audits.resetPassword')), $currentUser);

	    } catch (\Throwable $e) {

	        if ($transactionStarted):
	            $this->db->transRollback();
	        endif;

	        log_message('error', lang('frontend/account.messages.resetPasswordError') . ' - ' . $e);
	        return ['result' => false, 'message' => lang('frontend/account.messages.resetPasswordError')];

	    }

	    /* Istanzio il servizio email dedicato e tento l'invio */
	    $emailService = new \App\Libraries\Frontend\EmailService();

	    /* Configuro i parametri dinamici per questa specifica chiamata */
	    $module = $this->module;
	    $template = 'emailResetPasswordAdminPartial';
	    $subjectLangKey = 'frontend/email.account.resetPassword.subjectResetPasswordEmail';

	    /* Chiamata al metodo con i nuovi parametri separati */
	    if ( ! $emailService->sendActivationEmail($currentUser, $token->getValue(), $module, $template, $subjectLangKey)):

	        $message = ui_message('frontend/account.messages.resetPasswordSuccessNoEmail', [esc($currentUser->firstname), esc($currentUser->lastname)], lang('frontend/account.messages.resetPasswordSuccessNoEmail'));
	        return ['result' => 'db_committed_no_email', 'message' => $message];
	        
	    else:
	        
	        $message = ui_message('frontend/account.messages.resetPasswordSuccess', [esc($currentUser->firstname), esc($currentUser->lastname)], lang('frontend/account.messages.resetPasswordSuccess'));
	        return ['result' => true, 'message' => $message];
	        
	    endif;
	}

	/**
	 * Legge la scadenza del collegamento di reimpostazione della password.
	 *
	 * @param \stdClass $currentUser Identità del cliente autenticato, ottenuta sul server.
	 * @return string Data da localizzare nella vista, oppure stringa vuota.
	 */
	public function getExpiringDate(\stdClass $currentUser): string
	{
	    $sql = 'select token_expire from users_tokens where user_uuid = ? and token_type = ? order by token_expire desc limit 1';
	    $token = $this->db->query($sql, [$currentUser->uuid, 'activation'])->getRow();

	    return $token->token_expire ?? '';
	}

	/**
	 * Recupera il metodo 2FA attivo per l'utente.
	 *
	 * @param string $userUuid UUID del cliente interessato.
	 * @return string
	 */
	public function getActiveMethod(string $userUuid): string
	{
	    $sql = "select method from users_2fa where user_uuid = ? and enabled = 1 limit 1";
	    $row = $this->db->query($sql, [$userUuid])->getRow();

	    if ( ! $row):
	        return 'none';
	    endif;

	    return $row->method;
	}

	/**
	 * Salva il metodo 2FA email o disabilitato e disattiva gli altri metodi.
	 *
	 * @param \stdClass $currentUser Identita del cliente autenticato, ottenuta sul server.
	 * @param string $method Metodo di autenticazione selezionato.
	 * @return bool
	 */
	public function setBasicMethod(\stdClass $currentUser, string $method): bool
    {
		$transactionStarted = false;

        try {
			if ( ! $this->db->transBegin()):
				throw new \RuntimeException('Impossibile avviare la transazione 2FA.');
			endif;

			$transactionStarted = true;

            /* Disattivo tutti i metodi esistenti per l'utente */
            $sqlDisable = "update users_2fa set enabled = 0 where user_uuid = ?";
            $this->db->query($sqlDisable, [$currentUser->uuid]);

            /* Se il metodo è email, eseguo l'upsert per attivarlo */
            if ($method === 'email') :
                $sqlUpsert = "insert into users_2fa (user_uuid, method, enabled) values (?, 'email', 1) on duplicate key update enabled = 1";
                $this->db->query($sqlUpsert, [$currentUser->uuid]);
            endif;

			if ($this->db->transStatus() === false):
				throw new \RuntimeException('Aggiornamento del metodo 2FA non riuscito.');
			endif;

			if ( ! $this->db->transCommit()):
				throw new \RuntimeException('Commit del metodo 2FA non riuscito.');
			endif;

			$transactionStarted = false;
			$this->clearTemporarySecret();

            log_user_activity('ACTIVATE_' . strtoupper($method), 'account', ui_message('frontend/account.audits.activateMethod', [esc($currentUser->firstname), esc($currentUser->lastname)], lang('frontend/account.audits.activateMethod')), $currentUser);

            return true;

        } catch (\Throwable $e) {
			if ($transactionStarted):
				$this->db->transRollback();
			endif;

            log_message('error', 'Errore nel salvataggio del metodo 2FA base: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Conserva in sessione il segreto TOTP cifrato per la configurazione in corso.
     *
     * @param string $userUuid UUID del cliente interessato.
     * @param string $secret Segreto TOTP da proteggere o attivare.
     * @return bool
     */
    public function saveTemporarySecret(string $userUuid, string $secret): bool
    {
        try {

			$protectedSecret = (new \App\Libraries\Frontend\AppOtpService())->protectSecret($secret);

			session()->set('frontend_account_totp_setup', [
				'user_uuid' => $userUuid,
				'secret' => $protectedSecret,
				'created_at' => time(),
			]);

            return true;

        } catch (\Throwable $e) {
            log_message('error', 'Errore nel salvataggio del secret TOTP temporaneo: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Recupera il segreto TOTP temporaneo dopo aver verificato proprietario e scadenza.
     *
     * @param string $userUuid UUID del cliente interessato.
     * @return string|null
     */
    public function getTemporarySecret(string $userUuid): ?string
    {
		$pending = session()->get('frontend_account_totp_setup');
		$settings = setting('Frontend\Auth');
		$expiresIn = (int) ($settings->twoFactorTime ?? config(\Config\Frontend\Auth::class)->twoFactorTime);

		if ( ! is_array($pending)
			|| ! isset($pending['user_uuid'], $pending['secret'], $pending['created_at'])
			|| ! hash_equals($userUuid, (string) $pending['user_uuid'])
			|| (time() - (int) $pending['created_at']) > $expiresIn):
			$this->clearTemporarySecret();
			return null;
		endif;

		$secret = (new \App\Libraries\Frontend\AppOtpService())->revealSecret((string) $pending['secret']);

		if ($secret === null):
			$this->clearTemporarySecret();
		endif;

		return $secret;
    }

	/**
	 * Rimuove dalla sessione la configurazione TOTP non ancora confermata.
	 *
	 * @return void
	 */
	public function clearTemporarySecret(): void
	{
		session()->remove('frontend_account_totp_setup');
	}

	/**
	 * Attiva il segreto TOTP verificato e conclude la configurazione temporanea.
	 *
	 * @param string $userUuid UUID del cliente interessato.
	 * @param string $secret Segreto TOTP da proteggere o attivare.
	 * @param \stdClass $currentUser Identita del cliente autenticato, ottenuta sul server.
	 * @return bool
	 */
	public function activateTotpMethod(string $userUuid, string $secret, \stdClass $currentUser): bool
    {
		$transactionStarted = false;

        try {
			if ( ! $this->db->transBegin()):
				throw new \RuntimeException('Impossibile avviare la transazione TOTP.');
			endif;

			$transactionStarted = true;

			/* Disattivo tutti i metodi prima di attivare esclusivamente TOTP */
			$sqlDisable = "update users_2fa set enabled = 0 where user_uuid = ?";
			$this->db->query($sqlDisable, [$userUuid]);

			$protectedSecret = (new \App\Libraries\Frontend\AppOtpService())->protectSecret($secret);
			$sqlActivateTotp = "insert into users_2fa (user_uuid, method, secret, enabled) values (?, 'totp', ?, 1) on duplicate key update secret = values(secret), enabled = 1";
			$this->db->query($sqlActivateTotp, [$userUuid, $protectedSecret]);

			if ($this->db->transStatus() === false):
				throw new \RuntimeException('Attivazione TOTP non riuscita.');
			endif;

			if ( ! $this->db->transCommit()):
				throw new \RuntimeException('Commit dell\'attivazione TOTP non riuscito.');
			endif;

			$transactionStarted = false;
			$this->clearTemporarySecret();

            log_user_activity('ACTIVATE_TOTP', 'account', ui_message('frontend/account.audits.activateTotp', [esc($currentUser->firstname), esc($currentUser->lastname)], lang('frontend/account.audits.activateTotp')), $currentUser);

            return true;

        } catch (\Throwable $e) {
			if ($transactionStarted):
				$this->db->transRollback();
			endif;

            log_message('error', 'Errore durante l\'attivazione definitiva del TOTP: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Registra la richiesta personale di cancellazione e revoca tutti gli accessi del cliente.
     *
     * Mantiene email, stato e sospensione invariati. Le date e la revoca dei token sono
     * confermate nella stessa transazione; nessun dato viene eliminato definitivamente.
     *
     * @param \stdClass $currentUser Cliente autenticato, risolto sul server.
     * @param string $currentPassword Password personale obbligatoria per questa operazione.
     * @return array{result: bool, message: string}
     */
    public function requestRemoval(\stdClass $currentUser, string $currentPassword): array
    {
        $transactionStarted = false;

        try {
            if ( ! $this->verifyCurrentPassword($currentUser->uuid, $currentPassword)):
                return ['result' => false, 'message' => lang('frontend/account.errors.currentPasswordInvalid')];
            endif;

            if ( ! $this->db->transBegin()):
                throw new \RuntimeException('Impossibile avviare la richiesta di cancellazione utente.');
            endif;
            $transactionStarted = true;

            /* Il blocco della riga impedisce richieste duplicate e ricontrolla le credenziali. */
            $sql = 'select password_hash from users where uuid = ? and status = 1 and suspended_at is null and deleted_at is null for update';
            $row = $this->db->query($sql, [$currentUser->uuid])->getRow();
            if ($row === null || ! is_string($row->password_hash) || ! password_verify($currentPassword, $row->password_hash)):
                $this->db->transRollback();
                $transactionStarted = false;
                return ['result' => false, 'message' => lang('frontend/account.messages.removalUnavailable')];
            endif;

            $now = new \DateTimeImmutable();
            $deletedAt = $now->format('Y-m-d H:i:s');
            $removalAt = $now->add(new \DateInterval('P' . \Config\Frontend\Auth::ACCOUNT_REMOVAL_DAYS . 'D'))->format('Y-m-d H:i:s');
            $sql = 'update users set deleted_at = ?, to_be_removed_at = ?, updated_at = ? where uuid = ? and deleted_at is null';
            $this->db->query($sql, [$deletedAt, $removalAt, $deletedAt, $currentUser->uuid]);
            if ($this->db->affectedRows() !== 1):
                throw new \RuntimeException('Richiesta di cancellazione utente non registrata.');
            endif;

            /* Chiude lo storico prima di revocare anche i token di attivazione e Ricordami. */
            $sql = "update users_logs set logout = ?, logout_reason = 'deleted' where user_uuid = ? and logout is null";
            $this->db->query($sql, [$deletedAt, $currentUser->uuid]);
            $this->db->query('delete from users_tokens where user_uuid = ?', [$currentUser->uuid]);
            $this->db->query('delete from users_2fa_codes where user_uuid = ?', [$currentUser->uuid]);

            if ($this->db->transStatus() === false || ! $this->db->transCommit()):
                throw new \RuntimeException('Conferma della cancellazione utente non riuscita.');
            endif;
            $transactionStarted = false;

            log_user_activity('REQUEST_ACCOUNT_REMOVAL', 'account', lang('frontend/account.audits.removalRequested'), $currentUser);
            return ['result' => true, 'message' => lang('frontend/account.messages.removalRequested')];

        } catch (\Throwable $e) {
            if ($transactionStarted):
                $this->db->transRollback();
            endif;
            log_message('error', 'Richiesta cancellazione utente - ' . $e->getMessage());
            return ['result' => false, 'message' => lang('frontend/account.messages.removalError')];
        }
    }
}
