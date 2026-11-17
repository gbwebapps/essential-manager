<?php declare(strict_types = 1);

namespace App\Libraries\Frontend;

use CodeIgniter\Database\ConnectionInterface;

/**
 * Risolve l'identita del cliente verificando i token di sessione e Ricordami.
 */
class AuthorizationClass
{
    private \CodeIgniter\Database\ConnectionInterface $db;
    private ?object $currentUserCache = null;
    private ?string $currentAccessFingerprint = null;
    private ?int $currentTokenId = null;

    /**
     * Inizializza le dipendenze necessarie al componente.
     *
     * @param \CodeIgniter\Database\ConnectionInterface $db Connessione al database dell'installazione.
     * @return void
     */
    public function __construct(ConnectionInterface $db)
    {
        $this->db = $db;
        $this->currentUserCache = null;
        helper('settings');
    }

    /**
     * Risolve l'utente autenticato dalla sessione o dal cookie Ricordami.
     *
     * @return object|null
     */
    public function currentUser(): ?object
    {
        /* 1. Restituisce la cache se presente */
        if ($this->currentUserCache !== null):
            return $this->currentUserCache;
        endif;

        /* 2. Verifica tramite sessione */
        $data = $this->getUserFromSession();
        if ($data !== null):
            $this->currentUserCache = $data;
            return $data;
        endif;

        /* 3. Verifica tramite cookie */
        $data = $this->getUserFromCookie();
        if ($data !== null):
            $this->currentUserCache = $data;
            return $data;
        endif;

        return null;
    }

    /**
     * Restituisce l'impronta dell'accesso autenticato corrente.
     *
     * @return string|null
     */
    public function currentAccessFingerprint(): ?string
    {
        return $this->currentUser() !== null ? $this->currentAccessFingerprint : null;
    }

    /**
     * Valida il token di sessione e ne aggiorna la scadenza per inattivita.
     *
     * @return object|null
     */
    private function getUserFromSession(): ?object
    {
        if ( ! session()->has('frontendSession') || session()->get('frontendSession') === null):
            return null;
        endif;

        /* Istanzia il token passando il valore salvato in sessione */
        $token = new \App\Libraries\Token(session()->get('frontendSession'));
        $tokenHash = $token->getHash(setting('Frontend\Auth')->hashKey);

        $sql = "select * from users_tokens where token_hash = ? and token_type = ? limit 1";
        $query = $this->db->query($sql, [$tokenHash, 'session'])->getRow();

        /*
         * 1. Calcolo del limite di inattività basato su last_activity.
         * Il token è valido se (last_activity + sessionTime) è nel futuro.
         */
        $now = \CodeIgniter\I18n\Time::now();
        $sessionTimeLimit = (int) setting('Frontend\Auth')->sessionTime;
        $maxActivityTime = isset($query->last_activity) ? \CodeIgniter\I18n\Time::parse($query->last_activity)->addSeconds($sessionTimeLimit) : null;

        if (isset($query->token_hash) && $maxActivityTime && $now->isBefore($maxActivityTime)):

            /* 
             * 2. Sliding Expiration & Audit Trail.
             * Registriamo l'ora esatta di questa interazione (last_activity) e posticipiamo
             * la scadenza assoluta del token.
             */
            $currentTimeStr = $now->format('Y-m-d H:i:s');
            $newExpireStr   = $now->addSeconds($sessionTimeLimit)->format('Y-m-d H:i:s');
            
            $sqlUpdate = "update users_tokens set last_activity = ?, token_expire = ? where token_hash = ? and token_type = ?";
            $this->db->query($sqlUpdate, [$currentTimeStr, $newExpireStr, $tokenHash, 'session']);

            /* 3. Risoluzione dell'identità */
            $data = $this->getUser($query->user_uuid);
            if ($data):
                $this->currentAccessFingerprint = hash('sha256', (string) session()->get('frontendSession'));
                $this->currentTokenId = (int) $query->id;
                return $data;
            endif;

        endif;

        return null;
    }

    /**
     * Valida il cookie Ricordami rispettando la sua scadenza assoluta.
     *
     * @return object|null
     */
    private function getUserFromCookie(): ?object
    {
        $cookie = service('request')->getCookie('frontendRememberMe');

        if ($cookie === null):
            return null;
        endif;

        /* Decifra il valore del cookie prima di passarlo alla classe Token */
        $crypto = new \App\Libraries\CryptoService(setting('Frontend\Auth')->sessionCryptoKey);
        $decryptedValue = $crypto->decrypt($cookie);

        if ( ! $decryptedValue):
            return null;
        endif;

        $token = new \App\Libraries\Token($decryptedValue);
        $tokenHash = $token->getHash(setting('Frontend\Auth')->hashKey);

        $sql = "select * from users_tokens where token_hash = ? and token_type = ? limit 1";
        $query = $this->db->query($sql, [$tokenHash, 'cookie'])->getRow();

        /* 1. Controllo di validità assoluta (il RememberMe ha una data di "morte" rigida) */
        $now = \CodeIgniter\I18n\Time::now();
        $expireTime = isset($query->token_expire) ? \CodeIgniter\I18n\Time::parse($query->token_expire) : null;

        if (isset($query->token_hash) && $expireTime && $now->isBefore($expireTime)):
            
            /* 
             * 2. Audit Trail (Tracking).
             * Aggiorniamo in tempo reale solo l'ultima attività nota dell'utente,
             * senza alterare la scadenza assoluta del token persistente.
             */
            $currentTimeStr = $now->format('Y-m-d H:i:s');
            $sqlUpdate = "update users_tokens set last_activity = ? where token_hash = ? and token_type = ?";
            $this->db->query($sqlUpdate, [$currentTimeStr, $tokenHash, 'cookie']);

            /* 3. Risoluzione dell'identità */
            $data = $this->getUser($query->user_uuid);
            if ($data):
                $this->currentAccessFingerprint = hash('sha256', 'cookie:' . $tokenHash);
                $this->currentTokenId = (int) $query->id;
                return $data;
            endif;
            
        endif;

        return null;
    }

    /**
     * Azzera l'identita e i riferimenti di accesso memorizzati per questa richiesta.
     *
     * @return self
     */
    public function refresh(): self
    {
        $this->currentUserCache = null;
        $this->currentAccessFingerprint = null;
        $this->currentTokenId = null;

        return $this;
    }

    /**
     * Utenti e amministratori non condividono ruoli o autorizzazioni.
     *
     * @param string $uuid UUID del cliente o del proprietario delle immagini.
     * @return object|null
     */
    private function getUser(string $uuid): ?object
    {
        $sql = "select uuid, firstname, lastname, email, phone, status, note, created_at, updated_at,
                       suspended_at, resetted_at, deleted_at
                from users where uuid = ? and status = 1 and password_hash is not null
                and suspended_at is null and deleted_at is null limit 1";
        return $this->db->query($sql, [$uuid])->getRow();
    }

    /**
     * Restituisce solo il token che ha autenticato questa richiesta.
     *
     * @return int|null
     */
    public function currentTokenId(): ?int
    {
        $this->currentUser();
        return $this->currentTokenId;
    }
}
