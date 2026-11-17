<?php declare(strict_types = 1);

namespace App\Models\Backend;

use App\Models\Backend\SettingsModel;

/**
 * Modello principale per la gestione delle Impostazioni globali del sistema (Settings).
 * 
 * Centralizza il recupero, la validazione e il salvataggio massivo delle configurazioni. 
 * Implementa un livello di Cache in memoria per evitare query multiple al database 
 * durante lo stesso caricamento di pagina, e garantisce che i dati inviati dai moduli 
 * rispettino regole rigorose prima di sovrascrivere le impostazioni.
 */
class SettingsBackendModel extends SettingsModel
{
    /**
     * Whitelist dei campi consentiti per le impostazioni Generali (lingua, data, fuso orario, tema, carattere).
     * 
     * @var array 
     */
    private array $allowedGeneralFields = [
        'timezone',
        'language',
        'dateFormat',
        'theme',
        'font'
    ];

    /**
     * Whitelist dei campi consentiti per le impostazioni di Autenticazione (sicurezza e sessioni).
     * 
     * @var array 
     */
	private array $allowedAuthFields = [
        'sensitivePasswordConfirmation',
        'attempts',
        'attemptsLimit',
        'attemptsInterval',
        'twoFactor',
        'twoFactorAttempts',
        'twoFactorLimit',
        'twoFactorTime',
        'twoFactorAttemptsInterval',
        'twoFactorIssuer',
        'twoFactorDigits',
        'twoFactorWindow',
        'twoFactorEmailExpiry',
        'twoFactorEmailFrom',
        'resetAttempts',
        'resetAttemptsLimit',
        'resetAttemptsInterval',
        'sessionTime',
        'rememberMeTime',
        'activationTime',
    ];

    /**
     * Whitelist dei campi consentiti per le impostazioni di Upload (dimensionamento, policy immagini).
     * 
     * @var array 
     */
    private array $allowedUploadFields = [
        'renameImages',
        'overwriteImages',
        'resizeMediumX',
        'resizeMediumY',
        'resizeSmallX',
        'resizeSmallY',
        'maxFileSize',
        'maxImageX', 
        'maxImageY', 
        'allowedExtensions'
    ];

    /**
     * Whitelist dei campi consentiti per le impostazioni E-mail (configurazione SMTP).
     * 
     * @var array 
     */
    private array $allowedEmailFields = [
        'fromEmail',
        'fromName',
        'recipients',
        'protocol',
        'SMTPHost',
        'SMTPPort',
        'SMTPCrypto',
        'SMTPUser',
        'SMTPPass',
        'SMTPAuthMethod',
        'mailType',
        'charset',
        'priority',
    ];

    /**
     * Fornisce le regole di validazione per i parametri di Autenticazione.
     * 
     * Verifica che i tempi di sessione, limiti di tentativi e parametri 2FA inviati 
     * dall'amministratore siano numeri, mail valide o booleani sicuri.
     *
     * @param array $posts I dati inviati dal form (opzionali per permettere controlli condizionali in futuro)
     * @return array Regole native di CI4
     */
    public function authSettingsValidateRules(array $posts = []): array
    {
        return [
            'sensitivePasswordConfirmation' => [
                'label' => lang('backend/settingsBackend.labels.sensitivePasswordConfirmation'),
                'rules' => ['if_exist', 'required', 'in_list[0,1]'],
            ],
            'attempts' => [
                'label' => lang('backend/settingsBackend.labels.attempts'),
                'rules' => ['required', 'in_list[0,1]'],
            ],
            'attemptsLimit' => [
                'label' => lang('backend/settingsBackend.labels.attemptsLimit'),
                'rules' => ['required', 'is_natural_no_zero', 'less_than_equal_to[20]'],
            ],
            'attemptsInterval' => [
                'label' => lang('backend/settingsBackend.labels.attemptsInterval'),
                'rules' => ['required', 'is_natural_no_zero', 'greater_than_equal_to[10]'],
            ],
            'twoFactor' => [
                'label' => lang('backend/settingsBackend.labels.twoFactor'),
                'rules' => ['required', 'in_list[0,1]'],
            ],
            'twoFactorAttempts' => [
                'label' => lang('backend/settingsBackend.labels.twoFactorAttempts'),
                'rules' => ['required', 'in_list[0,1]'],
            ],
            'twoFactorLimit' => [
                'label' => lang('backend/settingsBackend.labels.twoFactorLimit'),
                'rules' => ['required', 'is_natural_no_zero', 'less_than_equal_to[10]'],
            ],
            'twoFactorTime' => [
                'label' => lang('backend/settingsBackend.labels.twoFactorTime'),
                'rules' => ['required', 'is_natural_no_zero', 'greater_than_equal_to[10]'],
            ],
            'twoFactorAttemptsInterval' => [
                'label' => lang('backend/settingsBackend.labels.twoFactorAttemptsInterval'),
                'rules' => ['required', 'is_natural_no_zero', 'greater_than_equal_to[10]', 'less_than_equal_to[86400]'],
            ],
            'twoFactorIssuer' => [
                'label' => lang('backend/settingsBackend.labels.twoFactorIssuer'),
                'rules' => ['required', 'trim', 'max_length[255]', 'regex_match[/^[a-zA-Z0-9\s\-_.]+$/]'],
            ],
            'twoFactorDigits' => [
                'label' => lang('backend/settingsBackend.labels.twoFactorDigits'),
                'rules' => ['required', 'in_list[6,8]'],
            ],
            'twoFactorWindow' => [
                'label' => lang('backend/settingsBackend.labels.twoFactorWindow'),
                'rules' => ['required', 'integer', 'greater_than_equal_to[0]', 'less_than_equal_to[2]'],
            ],
            'twoFactorEmailExpiry' => [
                'label' => lang('backend/settingsBackend.labels.twoFactorEmailExpiry'),
                'rules' => ['required', 'is_natural_no_zero', 'greater_than_equal_to[10]'],
            ],
            'twoFactorEmailFrom' => [
                'label' => lang('backend/settingsBackend.labels.twoFactorEmailFrom'),
                'rules' => ['required', 'valid_email', 'max_length[255]'],
            ],
            'resetAttempts' => [
                'label' => lang('backend/settingsBackend.labels.resetAttempts'),
                'rules' => ['required', 'in_list[0,1]'],
            ],
            'resetAttemptsLimit' => [
                'label' => lang('backend/settingsBackend.labels.resetAttemptsLimit'),
                'rules' => ['required', 'is_natural_no_zero', 'less_than_equal_to[20]'],
            ],
            'resetAttemptsInterval' => [
                'label' => lang('backend/settingsBackend.labels.resetAttemptsInterval'),
                'rules' => ['required', 'is_natural_no_zero', 'greater_than_equal_to[60]', 'less_than_equal_to[86400]'],
            ],
            'sessionTime' => [
                'label' => lang('backend/settingsBackend.labels.sessionTime'),
                'rules' => ['required', 'is_natural_no_zero', 'greater_than_equal_to[60]'],
            ],
            'rememberMeTime' => [
                'label' => lang('backend/settingsBackend.labels.rememberMeTime'),
                'rules' => ['required', 'is_natural_no_zero', 'greater_than_equal_to[3600]'],
            ],
            'activationTime' => [
                'label' => lang('backend/settingsBackend.labels.activationTime'),
                'rules' => ['required', 'is_natural_no_zero', 'greater_than_equal_to[3600]'],
            ],
        ];
    }

    /**
     * Fornisce le regole di validazione per i parametri di Upload.
     * 
     * Verifica, ad esempio, che i limiti di peso siano numeri validi, o che le estensioni 
     * consentite siano stringhe alfanumeriche (senza caratteri strani).
     *
     * @param array $posts I dati inviati dal form
     * @return array Regole native di CI4
     */
    public function uploadSettingsValidateRules(array $posts = []): array
    {
        return [
            'renameImages' => [
                'label' => lang('backend/settingsBackend.labels.renameImages'),
                'rules' => ['required', 'in_list[0,1]'],
            ],
            'overwriteImages' => [
                'label' => lang('backend/settingsBackend.labels.overwriteImages'),
                'rules' => ['required', 'in_list[0,1]'],
            ],
            'resizeMediumX' => [
                'label' => lang('backend/settingsBackend.labels.resizeMediumX'),
                'rules' => ['required', 'is_natural'],
            ],
            'resizeMediumY' => [
                'label' => lang('backend/settingsBackend.labels.resizeMediumY'),
                'rules' => ['required', 'is_natural'],
            ],
            'resizeSmallX' => [
                'label' => lang('backend/settingsBackend.labels.resizeSmallX'),
                'rules' => ['required', 'is_natural'],
            ],
            'resizeSmallY' => [
                'label' => lang('backend/settingsBackend.labels.resizeSmallY'),
                'rules' => ['required', 'is_natural'],
            ],
            'maxFileSize' => [
                'label' => lang('backend/settingsBackend.labels.maxFileSize'),
                'rules' => ['required', 'is_natural_no_zero'],
            ],
            'maxImageX' => [
                'label' => lang('backend/settingsBackend.labels.maxImageX'),
                'rules' => ['required', 'is_natural'],
            ],
            'maxImageY' => [
                'label' => lang('backend/settingsBackend.labels.maxImageY'),
                'rules' => ['required', 'is_natural'],
            ],
            'allowedExtensions' => [
                'label' => lang('backend/settingsBackend.labels.allowedExtensions'),
                'rules' => ['required'],
            ],
            'allowedExtensions.*' => [
                'label' => lang('backend/settingsBackend.labels.allowedExtensions'),
                'rules' => ['alpha', 'max_length[10]'],
            ],
        ];
    }

    /**
     * Fornisce le regole di validazione per i parametri E-mail (SMTP).
     * 
     * Contiene una logica dinamica: se l'operatore seleziona "smtp" come protocollo, 
     * i parametri di connessione (host, porta, tipo di auth) diventano improvvisamente obbligatori ('required').
     *
     * @param array $posts I dati inviati dal form (necessari per rilevare il tipo di protocollo scelto)
     * @return array Regole native di CI4
     */
    public function emailSettingsValidateRules(array $posts = []): array
    {
        /** Verifichiamo se il protocollo inviato dal form è smtp */
        $isSmtp = (isset($posts['protocol']) && $posts['protocol'] === 'smtp');

        return [
            'fromEmail' => [
                'label' => lang('backend/settingsBackend.labels.fromEmail'),
                'rules' => ['required', 'valid_email', 'max_length[255]'],
            ],
            'fromName' => [
                'label' => lang('backend/settingsBackend.labels.fromName'),
                'rules' => ['required', 'trim', 'max_length[255]', 'regex_match[/^[a-zA-Z0-9\s\-_.]+$/]'],
            ],
            'recipients' => [
                'label' => lang('backend/settingsBackend.labels.recipients'),
                'rules' => ['permit_empty', 'valid_emails', 'max_length[500]'],
            ],
            'protocol' => [
                'label' => lang('backend/settingsBackend.labels.protocol'),
                'rules' => ['required', 'in_list[smtp,mail,sendmail]'],
            ],
            'SMTPHost' => [
                'label' => lang('backend/settingsBackend.labels.SMTPHost'),
                /** Se è smtp forziamo required, altrimenti permit_empty */
                'rules' => [$isSmtp ? 'required' : 'permit_empty', 'regex_match[/^[a-zA-Z0-9.-]+$/]', 'max_length[255]'],
            ],
            'SMTPPort' => [
                'label' => lang('backend/settingsBackend.labels.SMTPPort'),
                'rules' => [$isSmtp ? 'required' : 'permit_empty', 'is_natural_no_zero', 'less_than_equal_to[65535]'],
            ],
            'SMTPCrypto' => [
                'label' => lang('backend/settingsBackend.labels.SMTPCrypto'),
                'rules' => [$isSmtp ? 'required' : 'permit_empty', 'in_list[none,tls,ssl]'],
            ],
            'SMTPUser' => [
                'label' => lang('backend/settingsBackend.labels.SMTPUser'),
                'rules' => ['permit_empty', 'max_length[255]', 'regex_match[/^[a-zA-Z0-9@.\-_]+$/]'],
            ],
            'SMTPPass' => [
                'label' => lang('backend/settingsBackend.labels.SMTPPass'),
                'rules' => ['permit_empty', 'max_length[255]', 'regex_match[/^[^<>]+$/]'],
            ],
            'SMTPAuthMethod' => [
                'label' => lang('backend/settingsBackend.labels.SMTPAuthMethod'),
                'rules' => [$isSmtp ? 'required' : 'permit_empty', 'in_list[LOGIN,PLAIN]'],
            ],
            'mailType' => [
                'label' => lang('backend/settingsBackend.labels.mailType'),
                'rules' => ['required', 'in_list[html,text]'],
            ],
            'charset' => [
                'label' => lang('backend/settingsBackend.labels.charset'),
                'rules' => ['required', 'max_length[30]', 'regex_match[/^[a-zA-Z0-9\-]+$/]'],
            ],
            'priority' => [
                'label' => lang('backend/settingsBackend.labels.priority'),
                'rules' => ['required', 'in_list[1,3,5]'],
            ],
        ];
    }

    /**
     * Fornisce le regole di validazione per le impostazioni Generali.
     * 
     * Controlla che le stringhe inviate (fuso orario, formato data, lingua) corrispondano 
     * esattamente a quelle previste dalle liste interne di CodeIgniter o del progetto.
     *
     * @param array $posts I dati inviati dal form
     * @return array Regole native di CI4
     */
    public function generalSettingsValidateRules(array $posts = []): array
    {
        return [
            'timezone' => [
                'label' => lang('backend/settingsBackend.labels.timezone'),
                'rules' => ['required', 'timezone'],
            ],
            'language' => [
                'label' => lang('backend/settingsBackend.labels.language'),
                'rules' => ['required', 'in_list[' . implode(',', config('App')->supportedLocales) . ']'],
            ],
            'dateFormat' => [
                'label' => lang('backend/settingsBackend.labels.dateFormat'),
                'rules' => ['required', 'in_list[d MMMM yyyy HH:mm:ss,dd/MM/yyyy HH:mm,MMMM d yyyy h:mm:ss a,MM/dd/yyyy h:mm a,yyyy-MM-dd HH:mm:ss]'],
            ],
            'theme' => [
                'label' => lang('backend/settingsBackend.labels.theme'),
                'rules' => ['required', 'in_list[white,black,grey,dark]'],
            ],
            'font' => [
                'label' => lang('backend/settingsBackend.labels.font'),
                /* Mantiene compatibili le richieste precedenti all'introduzione del selettore. */
                'rules' => ['if_exist', 'required', 'in_list[' . implode(',', array_keys(\App\Libraries\Backend\InterfacePreferences::FONTS)) . ']'],
            ],
        ];
    }

    /**
     * Restituisce i soli campi scrivibili dei namespace amministrativi autorizzati.
     *
     * @param string $namespace Classe di configurazione richiesta dal controller.
     * @return array<string>
     */
    protected function allowedSettingsFields(string $namespace): array
    {
        return match ($namespace) {
            'Backend\General' => $this->allowedGeneralFields,
            'Backend\Auth' => $this->allowedAuthFields,
            'Backend\Upload' => $this->allowedUploadFields,
            'Backend\Email' => $this->allowedEmailFields,
            default => [],
        };
    }
}
