<?php declare(strict_types=1);

namespace App\Libraries\Frontend;

/**
 * Servizio dedicato all'autenticazione a due fattori (2FA) tramite messaggi di posta elettronica.
 * 
 * Gestisce l'intero ciclo di vita di un codice OTP (One-Time Password): la generazione crittografica, 
 * l'archiviazione sul database con relativa scadenza, l'elaborazione del template e-mail 
 * e la successiva validazione durante il tentativo di accesso.
 */
class EmailOtpService
{
    /**
     * Istanza della connessione al database per la persistenza dei codici temporanei.
     * @var \CodeIgniter\Database\BaseConnection 
     */
    protected $db;

    /**
     * Inizializza il servizio stabilendo la connessione nativa al database,
     * necessaria per registrare e interrogare i codici generati.
     *
     * @return void
     */
    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Genera un nuovo codice OTP temporaneo e lo trasmette attraverso posta elettronica all'indirizzo e-mail dell'utente.
     * 
     * Il metodo esegue diverse operazioni in sequenza: calcola un codice numerico casuale basato 
     * sui parametri di configurazione, determina l'esatto momento di scadenza, salva il record nel database 
     * e utilizza il servizio e-mail nativo del framework per compilare e recapitare il messaggio HTML.
     *
     * @param string $userUuid L'identificativo univoco (UUID) dell'utente che sta effettuando il login
     * @return bool Esito del processo: true se il codice è stato salvato e il messaggio affidato al servizio di posta, false altrimenti
     */
    public function send(string $userUuid): bool
    {
        $config = setting('Frontend\Auth');
        
        /* Genera un codice numerico casuale della lunghezza configurata */
        $digits = (int) $config->twoFactorDigits;
        if ( ! in_array($digits, [6, 8], true)):
            return false;
        endif;
        $code = str_pad((string) random_int(0, (10 ** $digits) - 1), $digits, '0', STR_PAD_LEFT);
        
        /* Calcola la data di scadenza per il formato DATETIME */
        $expiresAt = date('Y-m-d H:i:s', time() + (int)$config->twoFactorEmailExpiry);

        try {
            $sql = "insert into users_2fa_codes (user_uuid, code, expires_at) values (?, ?, ?)";
            $this->db->query($sql, [$userUuid, $code, $expiresAt]);
        } 
        catch (\Throwable $e) {
            log_message('error', 'Errore salvataggio OTP email: ' . $e->getMessage());
            return false;
        }

        /* Recupera l'e-mail e i dati dell'utente per la spedizione */
        $sql = "select email, firstname, lastname from users where uuid = ? and status = 1 limit 1";
        $admin = $this->db->query($sql, [$userUuid])->getRow();

        if ( ! $admin):
            return false;
        endif;

        /* Configurazione e invio tramite il servizio Email nativo di CodeIgniter */
        $email = \Config\Services::email();
        $email->setFrom($config->twoFactorEmailFrom, $config->twoFactorIssuer);
        $email->setTo($admin->email);
        $email->setSubject(lang('frontend/email.auth.2fa.subjectVerifyCodeEmail'));

        /* Calcola i minuti di scadenza direttamente nel servizio */
        $expiryMinutes = (int) round($config->twoFactorEmailExpiry / 60);
        
        /* Carica la vista parziale passando i dati necessari */
        $htmlMessage = view('frontend/auth/partials/email/email2faPartial', [
            'firstname'     => $admin->firstname,
            'lastname'      => $admin->lastname,
            'code'          => $code,
            'expiryMinutes' => $expiryMinutes /* Passato come argomento */
        ]);
        
        $email->setMessage($htmlMessage);

        if ( ! $email->send()):
            log_message('error', sprintf('Invio OTP fallito per %s %s (%s)', $admin->firstname, $admin->lastname, $admin->email));
            return false;
        else:
            return true;
        endif;
    }

}