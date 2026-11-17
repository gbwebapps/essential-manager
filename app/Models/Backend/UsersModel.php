<?php declare(strict_types = 1);

namespace App\Models\Backend;


/**
 * Gestisce lista, stato e token dei clienti senza inserimento o modifica anagrafica dal backend.
 */
class UsersModel extends BackendModel implements \App\Interfaces\ExportValidationInterface
{

    protected ?string $module = 'users';
    protected bool $hasSoftDelete = true;
    protected bool $hasStatusFilter = true;
    protected array $showAllAllowedFields = ['column', 'order', 'page', 'rows', 'searchFields', 'searchDates', 'trash_filter'];
    protected array $showAllSearchAllowedFields = ['firstname', 'lastname', 'email', 'phone'];
    protected array $showAllSearchAllowedDates = ['created_at', 'updated_at'];
    protected array $allowedOrderColumns = ['id', 'firstname', 'lastname', 'email', 'phone', 'status'];
    protected array $changeStatusAllowedFields = ['uuid'];
    protected array $deleteTokenAllowedFields = ['uuid', 'id'];
    protected array $resetPasswordAllowedFields = ['uuid'];
    protected ?string $getDataQuery = "select uuid, firstname, lastname, email, phone, status, created_at, updated_at, resetted_at, suspended_at, deleted_at, to_be_removed_at,
        (select filename from images where entity = 'users' and entity_uuid = users.uuid and is_cover = 1 limit 1) as cover,
        (select count(*) from images where entity = 'users' and entity_uuid = users.uuid) as images_num from users where 1 = 1";
    protected ?string $getUUIDQuery = "select uuid, firstname, lastname, email, phone, status, note, created_at, updated_at, resetted_at, suspended_at, deleted_at, to_be_removed_at from users where uuid = ? limit 1";
    protected ?string $getNumRowsQuery = 'select count(*) as count from users where 1 = 1';
    /**
     * Operazione getTrashCounts degli utenti registrati.
     *
     * @return array
     */
    public function getTrashCounts(): array
    {
        $query = $this->db->query('select count(*) as total,
                                        count(case when deleted_at is null and status = 1 then 1 end) as active,
                                        count(case when deleted_at is not null then 1 end) as trashed,
                                        count(case when deleted_at is null and status = 0 then 1 end) as suspended
                                 from users');

        if ($query === false || ($counts = $query->getRowArray()) === null):
            throw new \RuntimeException(lang('backend/global.messages.getDataError'));
        endif;

        $total = (int) $counts['total'];
        $trashed = (int) $counts['trashed'];
        $suspended = (int) $counts['suspended'];

        return ['active' => (int) $counts['active'], 'suspended' => $suspended, 'trashed' => $trashed, 'all' => $total];
    }

    /**
     * Operazione showAllValidationRules degli utenti registrati.
     *
     * @return array
     */
    public function showAllValidationRules(): array
    {
        return [
            'column' => [
                'rules' => ['required', 'alpha_dash'] 
            ],
            'order' => [
                'rules' => ['required', 'in_list[asc,desc]'] 
            ],
            'page' => [
                'rules' => ['required', 'is_natural_no_zero'] 
            ],
            'rows' => [
                'rules' => ['required', 'is_natural_no_zero'] 
            ],
            'trash_filter' => [
                'rules' => ['in_list[active,suspended,trashed,all]']
            ],
        ];
    }

    /**
     * Operazione showAllSearchValidationRules degli utenti registrati.
     *
     * @return array
     */
    public function showAllSearchValidationRules(): array
    {
        return [
            'searchFields.firstname' => [
                'label' => lang('backend/users.labels.firstname'), 
                'rules' => ['permit_empty', 'regex_match[/^[\p{L}\p{M}]+(?:[ \'\x{2018}\x{2019}`\-\x{2010}\x{2011}\x{2012}\x{2013}\x{2014}][\p{L}\p{M}]+)*$/u]'], 
            ],
            'searchFields.lastname' => [
                'label' => lang('backend/users.labels.lastname'), 
                'rules' => ['permit_empty', 'regex_match[/^[\p{L}\p{M}]+(?:[ \'\x{2018}\x{2019}`\-\x{2010}\x{2011}\x{2012}\x{2013}\x{2014}][\p{L}\p{M}]+)*$/u]'], 
            ],
            'searchFields.email' => [
                'label' => lang('backend/users.labels.email'), 
                'rules' => ['permit_empty', 'regex_match[/^[a-zA-Z0-9@._-]+$/]'], 
            ],
            'searchFields.phone' => [
                'label' => lang('backend/users.labels.phone'), 
                'rules' => ['permit_empty', 'regex_match[/^[0-9+\-\s()]+$/]'], 
            ],
            'searchDates.created_at-from' => [
                'label' => lang('backend/users.labels.dateFrom'),
                'rules' => ['permit_empty', 'valid_date[Y-m-d H:i:s]'],
            ],
			'searchDates.created_at-to' => [
				'label' => lang('backend/users.labels.dateTo'),
				'rules' => ['permit_empty', 'valid_date[Y-m-d H:i:s]'],
			],
			'searchDates.updated_at-from' => [
				'label' => lang('backend/users.labels.dateFrom'),
				'rules' => ['permit_empty', 'valid_date[Y-m-d H:i:s]'],
			],
			'searchDates.updated_at-to' => [
				'label' => lang('backend/users.labels.dateTo'),
				'rules' => ['permit_empty', 'valid_date[Y-m-d H:i:s]'],
			],
        ];
    }

    /**
     * Operazione delValidationRules degli utenti registrati.
     *
     * @return array
     */
    public function delValidationRules(): array
    {
        return [
            'uuid' => [
                'label' => lang('backend/users.labels.uuid'),
                'rules' => ['required', 'regex_match[/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i]'], 
                'errors' => [
                    'required' => lang('backend/users.errors.uuid'),
                    'regex_match' => lang('backend/users.errors.uuid')
                ]
            ],
        ];
    }

    /**
     * Operazione resetPasswordValidationRules degli utenti registrati.
     *
     * @return array
     */
    public function resetPasswordValidationRules(): array
    {
        return [
            'uuid' => [
                'label' => lang('backend/users.labels.uuid'),
                'rules' => ['required', 'regex_match[/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i]'],
                'errors' => [
                    'required' => lang('backend/users.errors.uuid'),
                    'regex_match' => lang('backend/users.errors.uuid')
                ]
            ],
        ];
    }

    /**
     * Operazione changeStatusValidationRules degli utenti registrati.
     *
     * @return array
     */
    public function changeStatusValidationRules(): array
    {
        return [
            'uuid' => [
                'label' => lang('backend/users.labels.uuid'),
                'rules' => ['required', 'regex_match[/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i]'], 
                'errors' => [
                    'required' => lang('backend/users.errors.uuid'),
                    'regex_match' => lang('backend/users.errors.uuid')
                ]
            ],
            'context' => [
                'label' => lang('backend/users.labels.context'),
                'rules' => ['permit_empty', 'in_list[show]'],
                'errors' => [
                    'in_list' => lang('backend/users.errors.context'),
                ]
            ],
        ];
    }

    /**
     * Operazione getTokensValidationRules degli utenti registrati.
     *
     * @return array
     */
    public function getTokensValidationRules(): array
    {
        return [
            'uuid' => [
                'label' => lang('backend/users.labels.uuid'),
                'rules' => ['required', 'regex_match[/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i]'],
                'errors' => [
                    'required' => lang('backend/users.errors.uuid'),
                    'regex_match' => lang('backend/users.errors.uuid')
                ]
            ],
        ]; 
    }

    /**
     * Operazione metaDataValidationRules degli utenti registrati.
     *
     * @return array
     */
    public function metaDataValidationRules(): array
    {
        return [
            'uuid' => [
                'label' => lang('backend/users.labels.uuid'),
                'rules' => ['required', 'regex_match[/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i]'],
                'errors' => [
                    'required' => lang('backend/users.errors.uuid'),
                    'regex_match' => lang('backend/users.errors.uuid')
                ]
            ],
        ];
    }

    /**
     * Operazione deleteTokenValidationRules degli utenti registrati.
     *
     * @return array
     */
    public function deleteTokenValidationRules(): array
    {
        return [
            'uuid' => [
                'label' => lang('backend/users.labels.uuid'),
                'rules' => ['required', 'regex_match[/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i]'],
                'errors' => [
                    'required' => lang('backend/users.errors.uuid'),
                    'regex_match' => lang('backend/users.errors.uuid')
                ]
            ],
            'id' => [
                'label' => lang('backend/users.labels.id'),
                'rules' => ['required', 'is_natural_no_zero'],
                'errors' => [
                    'required' => lang('backend/users.errors.id'),
                    'is_natural_no_zero' => lang('backend/users.errors.id')
                ]
            ],
        ];
    }

    /**
     * Operazione getTokens degli utenti registrati.
     *
     * @param string $uuid UUID del cliente o del proprietario delle immagini.
     * @return array
     */
    public function getTokens(string $uuid): array
    {
        /* Estrazione log dei tokens di sessione o reset */
        $sql = "select id, token_create, token_expire, token_type, user_agent, ip_address from users_tokens where user_uuid = ? order by token_create desc, id desc";
        return $this->db->query($sql, [$uuid])->getResult();
    }

    /**
     * Operazione changeStatus degli utenti registrati.
     *
     * @param array $posts Payload dell'operazione sottoposto alla whitelist.
     * @return array
     */
    public function changeStatus(array $posts): array
    {
        try 
        {
            /* Match dei posts con i campi consentiti */
            $posts = $this->checkAllowedFields($posts, $this->changeStatusAllowedFields);

            /* Recupero i dati dell'utente prima dell'eliminazione */
            $data = $this->getByUUID($posts['uuid']);

            if($data['result'] === false):
                return ['result' => false, 'message' => $data['message']];
            endif;

            /* Scudo Enterprise: blocco immediato se il record si trova nel cestino */
            if ($data['row']->deleted_at !== null):
                return ['result' => false, 'message' => lang('backend/users.messages.cannotModifyDeleted')]; /* Ricorda di creare la stringa lingua */
            endif;


            $currentStatus = (int) $data['row']->status;

            $newStatus = null;
            $suspendedAt = null;

            /* Converte il risultato in un intero (0 o 1) per MySQL */
            if($currentStatus === 0):

                $newStatus = 1;
                $suspendedAt = null;
                $data['row']->status = 1;
                $data['row']->suspended_at = null;

            elseif($currentStatus === 1):

                $newStatus = 0;
                $suspendedAt = date('Y-m-d H:i:s');
                $data['row']->status = 0;
                $data['row']->suspended_at = $suspendedAt;

            endif;

            $updatedAt = date('Y-m-d H:i:s');
            $data['row']->updated_at = $updatedAt;

            $this->db->transBegin();

            /* cambio status utente */
            $sql = "update users set status = ?, updated_at = ?, suspended_at = ? where uuid = ? and deleted_at is null";
            $this->db->query($sql, [$newStatus, $updatedAt, $suspendedAt, $posts['uuid']]);

            if ($this->db->affectedRows() !== 1 || $this->db->transStatus() === false):

                $this->db->transRollback();
                log_message('error', lang('backend/users.messages.changeStatusError'));

                return ['result' => false, 'message' => lang('backend/users.messages.changeStatusError')];
            endif;

            $this->db->transCommit();

            $currentAdmin = service('authorization')->currentAdmin();
            log_admin_activity('CHANGE_STATUS_USER', 'users', ui_message('backend/users.audits.changeStatusAdmin', [esc($data['row']->firstname), esc($data['row']->lastname)], lang('backend/users.audits.changeStatusAdmin')), $currentAdmin);

            return ['result' => true, 'message' => ui_message('backend/users.messages.changeStatusSuccess', [esc($data['row']->firstname), esc($data['row']->lastname)], lang('backend/users.messages.changeStatusSuccess')), 'admin' => $data['row']];

        } catch (\Throwable $e) {

            /* Rollback incondizionato: se c'è un'eccezione, si annulla sempre */
            $this->db->transRollback();

            log_message('error', lang('backend/users.messages.changeStatusError') . ' - ' . $e);
            return ['result' => false, 'message' => lang('backend/users.messages.changeStatusError')];

        }
    }

    /**
     * Operazione deleteToken degli utenti registrati.
     *
     * @param array $posts Payload dell'operazione sottoposto alla whitelist.
     * @return array
     */
    public function deleteToken(array $posts): array
    {
        /* Match dei posts con i campi consentiti */
        $posts = $this->checkAllowedFields($posts, $this->deleteTokenAllowedFields);

        try {

            /* Recupero i dati dell'utente prima dell'eliminazione */
            $data = $this->getByUUID($posts['uuid']);

            if($data['result'] === false):
                return ['result' => false, 'message' => $data['message']];
            endif;

            /* Scudo Enterprise: blocco immediato se il record si trova nel cestino */
            if ($data['row']->deleted_at !== null):
                return ['result' => false, 'message' => lang('backend/users.messages.cannotModifyDeleted')]; /* Ricorda di creare la stringa lingua */
            endif;


			$this->db->transBegin();

			/* 1. Recupero il token per leggere last_activity */
            $tokenSql = "select id, last_activity, token_type from users_tokens where user_uuid = ? and id = ?";
            $tokenRow = $this->db->query($tokenSql, [$posts['uuid'], $posts['id']])->getRow();

			if ($tokenRow):
                /* 2. Aggiorno il log registrando la forzatura (banned) */
				if (in_array($tokenRow->token_type, ['cookie', 'session'])):
					$logoutTime = ! empty($tokenRow->last_activity) ? $tokenRow->last_activity : date('Y-m-d H:i:s');
					$logUpdateSql = "update users_logs set logout = ?, logout_reason = 'banned' where token_id = ?";
					$this->db->query($logUpdateSql, [$logoutTime, $tokenRow->id]);
				endif;
			else:
				$this->db->transRollback();
				return ['result' => false, 'message' => lang('backend/users.messages.deleteTokenError')];
			endif;

            /* 3. Elimino fisicamente il token */
            $sql = "delete from users_tokens where user_uuid = ? and id = ?";
            $this->db->query($sql, [$posts['uuid'], $posts['id']]);

			if($this->db->affectedRows() > 0 && $this->db->transStatus() !== false):
				$this->db->transCommit();

                $currentAdmin = service('authorization')->currentAdmin();
                log_admin_activity('DELETE_TOKEN_USER', 'users', ui_message('backend/users.audits.deleteTokenAdmin', [esc($data['row']->firstname), esc($data['row']->lastname)], lang('backend/users.audits.deleteTokenAdmin')), $currentAdmin);

                return ['result' => true, 'message' => ui_message('backend/users.messages.deleteTokenSuccess', [esc($data['row']->firstname), esc($data['row']->lastname)], lang('backend/users.messages.deleteTokenSuccess')), 'admin' => $data['row']];
			endif;

			$this->db->transRollback();

            return ['result' => false, 'message' => lang('backend/users.messages.deleteTokenError')];

		} catch(\Throwable $e) {
			$this->db->transRollback();

            log_message('error', lang('backend/users.messages.deleteTokenError') . ' - ' . $e);
            return ['result' => false, 'message' => lang('backend/users.messages.deleteTokenError')];

        }
    }

    /**
     * Invia il recupero al cliente e registra l'operazione dello staff.
     *
     * @param array $posts Payload dell'operazione sottoposto alla whitelist.
     * @param \CodeIgniter\HTTP\IncomingRequest $request Richiesta HTTP corrente.
     * @return array
     */
    public function resetPassword(array $posts, \CodeIgniter\HTTP\IncomingRequest $request): array
    {
        try 
        {
            /* Match dei posts con i campi consentiti */
            $posts = $this->checkAllowedFields($posts, $this->resetPasswordAllowedFields);

            /* Recupero i dati dell'utente prima dell'eliminazione */
            $data = $this->getByUUID($posts['uuid']);

            if($data['result'] === false):
                return ['result' => false, 'message' => $data['message']];
            endif;

            /* Scudo Enterprise: blocco immediato se il record si trova nel cestino */
            if ($data['row']->deleted_at !== null):
                return ['result' => false, 'message' => lang('backend/users.messages.cannotModifyDeleted')]; /* Ricorda di creare la stringa lingua */
            endif;


            $userAgent = $request->getUserAgent()->getAgentString();
            $ip_address = $request->getIPAddress();

            /* Generazione token di attivazione */
            $token = new \App\Libraries\Token();
            $tokenHash = $token->getHash(setting('Frontend\Auth')->hashKey);

            /* 2. Calcolo corretto della scadenza lavorando sui secondi (timestamp) */
            $expireTime = date('Y-m-d H:i:s', time() + setting('Frontend\Auth')->activationTime);

            $this->db->transBegin();

            /* Scrittura Data di Reset nella tabella users */
            $sql = "update users set resetted_at = ? where uuid = ?";
            $this->db->query($sql, [date('Y-m-d H:i:s'), $posts['uuid']]);

            /* Eliminiamo eventuali token di attivazione precedenti ancora attivi o scaduti per questo specifico admin */
            $sql = "delete from users_tokens where user_uuid = ? and token_type = ?";
            $this->db->query($sql, [$posts['uuid'], 'activation']);

            /* Scrittura del token di attivazione */
			$sql = "insert into users_tokens (user_uuid, token_hash, token_create, last_activity, token_expire, token_type, user_agent, ip_address, created_at) values (?, ?, ?, ?, ?, ?, ?, ?, ?)";
			$this->db->query($sql, [$posts['uuid'], $tokenHash, date('Y-m-d H:i:s'), null, $expireTime, 'activation', $userAgent, $ip_address, date('Y-m-d H:i:s')]);

            if ($this->db->transStatus() === false):

                $this->db->transRollback();
                log_message('error', lang('backend/users.messages.resetPasswordError'));

                return ['result' => false, 'message' => lang('backend/users.messages.resetPasswordError')];
            endif;

            $this->db->transCommit();

            $currentAdmin = service('authorization')->currentAdmin();
            log_admin_activity('RESET_PASSWORD_USER', 'users', ui_message('backend/users.audits.resetPasswordAdmin', [esc($data['row']->firstname), esc($data['row']->lastname)], lang('backend/users.audits.resetPasswordAdmin')), $currentAdmin);

        } catch (\Throwable $e) {

            $this->db->transRollback();

            log_message('error', lang('backend/users.messages.resetPasswordError') . ' - ' . $e);
            return ['result' => false, 'message' => lang('backend/users.messages.resetPasswordError')];

        }

        /* Istanzio il servizio email dedicato e tento l'invio */
        $emailService = new \App\Libraries\Frontend\EmailService();

        /* Configuro i parametri dinamici per questa specifica chiamata */
        $template = 'emailResetPasswordPartial';
        $subjectLangKey = 'frontend/email.auth.resetPassword.subjectResetPasswordEmail';

        /* Chiamata al metodo con i nuovi parametri separati */
        if ( ! $emailService->sendActivationEmail($data['row'], $token->getValue(), 'auth', $template, $subjectLangKey)):

            $message = ui_message('backend/users.messages.resetPasswordSuccessNoEmail', [esc($data['row']->firstname), esc($data['row']->lastname)], lang('backend/users.messages.resetPasswordSuccessNoEmail'));
			return ['result' => true, 'emailSent' => false, 'message' => $message];
            
        else:
            
            $message = ui_message('backend/users.messages.resetPasswordSuccess', [esc($data['row']->firstname), esc($data['row']->lastname)], lang('backend/users.messages.resetPasswordSuccess'));
            return ['result' => true, 'message' => $message];
            
        endif;
    }

    /**
     * Esclude credenziali e dati tecnici dalla generazione dei CSV.
     *
     * @return array
     */
    public function exportAllowedFields(): array
    {
        return ['uuid', 'firstname', 'lastname', 'email', 'phone', 'status', 'note'];
    }

    /**
     * Conta le richieste ancora ripristinabili e quelle la cui scadenza e stata raggiunta.
     *
     * @return array{pending: int, due: int}
     */
    public function getRemovalCounts(): array
    {
        $now = date('Y-m-d H:i:s');
        $sql = 'select count(case when deleted_at is not null and to_be_removed_at > ? then 1 end) as pending,
                       count(case when deleted_at is not null and to_be_removed_at <= ? then 1 end) as due from users';
        $query = $this->db->query($sql, [$now, $now]);
        if ($query === false || ($row = $query->getRow()) === null):
            throw new \RuntimeException('Conteggi cancellazioni utenti non disponibili.');
        endif;
        return ['pending' => (int) $row->pending, 'due' => (int) $row->due];
    }

    /**
     * Ripristina una richiesta non scaduta senza cambiare email, stato o sospensione del cliente.
     *
     * Il blocco della riga e il controllo della scadenza vengono eseguiti nella stessa transazione.
     * Le sessioni revocate non vengono ricreate: il cliente dovra effettuare un nuovo accesso.
     *
     * @param array $posts Payload validato contenente soltanto l'UUID dell'utente da ripristinare.
     * @return array{result: bool, message: string}
     */
    public function restoreDelete(array $posts): array
    {
        $transactionStarted = false;

        try {
            $posts = $this->checkAllowedFields($posts, ['uuid']);
            if ( ! $this->db->transBegin()):
                throw new \RuntimeException('Impossibile avviare il ripristino utente.');
            endif;
            $transactionStarted = true;

            $sql = 'select uuid, firstname, lastname, deleted_at, to_be_removed_at from users where uuid = ? for update';
            $row = $this->db->query($sql, [$posts['uuid']])->getRow();
            $now = date('Y-m-d H:i:s');
            if ($row === null || $row->deleted_at === null || $row->to_be_removed_at === null || $row->to_be_removed_at <= $now):
                $this->db->transRollback();
                $transactionStarted = false;
                return ['result' => false, 'message' => lang('backend/users.messages.restoreUnavailable')];
            endif;

            $sql = 'update users set deleted_at = null, to_be_removed_at = null, updated_at = ? where uuid = ? and deleted_at is not null and to_be_removed_at > ?';
            $this->db->query($sql, [$now, $row->uuid, $now]);
            if ($this->db->affectedRows() !== 1 || $this->db->transStatus() === false || ! $this->db->transCommit()):
                throw new \RuntimeException('Ripristino utente non confermato.');
            endif;
            $transactionStarted = false;

            $currentAdmin = service('authorization')->currentAdmin();
            log_admin_activity('RESTORE_USER', 'users', ui_message('backend/users.audits.restoreRemoved', [esc($row->firstname), esc($row->lastname)], lang('backend/users.audits.restoreRemoved')), $currentAdmin);
            return ['result' => true, 'message' => lang('backend/users.messages.restoreRemovedSuccess')];

        } catch (\Throwable $e) {
            if ($transactionStarted):
                $this->db->transRollback();
            endif;
            log_message('error', 'Ripristino utente - ' . $e->getMessage());
            return ['result' => false, 'message' => lang('backend/users.messages.restoreRemovedError')];
        }
    }

    /**
     * Permanently deletes an expired account and removes its profile images.
     *
     * @param array $posts Validated user UUID.
     * @return array{result: bool, message: string}
     */
    public function hardDelete(array $posts): array
    {
        $transactionStarted = false;

        try {
            $posts = $this->checkAllowedFields($posts, ['uuid']);
            if ( ! $this->db->transBegin()):
                throw new \RuntimeException('Could not start permanent user deletion.');
            endif;
            $transactionStarted = true;

            $sql = 'select uuid, firstname, lastname, deleted_at, to_be_removed_at from users where uuid = ? for update';
            $row = $this->db->query($sql, [$posts['uuid']])->getRow();
            $now = date('Y-m-d H:i:s');
            if ($row === null || $row->deleted_at === null || $row->to_be_removed_at === null || $row->to_be_removed_at > $now):
                $this->db->transRollback();
                return ['result' => false, 'message' => lang('backend/users.messages.hardDeleteRequiresTrash')];
            endif;

            $this->db->query("delete from images where entity = 'users' and entity_uuid = ?", [$row->uuid]);
            $this->db->query('delete from users where uuid = ? and deleted_at is not null and to_be_removed_at <= ?', [$row->uuid, $now]);
            if ($this->db->affectedRows() !== 1 || $this->db->transStatus() === false || ! $this->db->transCommit()):
                throw new \RuntimeException('Permanent user deletion did not commit.');
            endif;
        } catch (\Throwable $e) {
            if ($transactionStarted):
                $this->db->transRollback();
            endif;
            log_message('error', 'Permanent user deletion failed: ' . $e->getMessage());
            return ['result' => false, 'message' => lang('backend/users.messages.hardDeleteError')];
        }

        $message = ui_message('backend/users.messages.hardDeleteSuccess', [esc($row->firstname), esc($row->lastname)], lang('backend/users.messages.hardDeleteSuccess'));
        log_admin_activity('HARD_DELETE_USER', 'users', $row->uuid, service('authorization')->currentAdmin());
        if ( ! \App\Libraries\ImageFileSystemService::removeAllImages('users', $row->uuid)):
            $warning = lang('backend/users.messages.hardDeleteImagesError');
            log_message('error', $warning . ' ' . $row->uuid);
            $message .= ' ' . $warning;
        endif;
        return ['result' => true, 'message' => $message];
    }
}
