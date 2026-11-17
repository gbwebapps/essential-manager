<?php declare(strict_types = 1); 

namespace App\Controllers\Backend;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

use App\Models\Backend\SettingsBackendModel;
use App\Libraries\Backend\SettingsBackendClass;
use App\Controllers\Backend\BackendController; 

/**
 * Controller per la configurazione globale delle variabili e dei parametri del sistema.
 * 
 * Gestisce l'interfaccia preposta all'impostazione di parametri applicativi (generali, autenticazione, upload, email), 
 * applicando un pattern in cui i dati persistenti a livello di DB sovrascrivono i default cablati nei file di configurazione.
 */
class SettingsBackendController extends BackendController 
{
    /**
     * Istanza del modello che esegue query SQL di base (senza l'impiego dell'ORM) per manipolare la tabella delle configurazioni.
     * @var SettingsBackendModel 
     */
    protected SettingsBackendModel $settingsBackendModel;

    /**
     * Istanza della classe di supporto che genera o estrae dizionari fissi, quali elenchi dei fusi orari, delle lingue o formati ammessi.
     * @var SettingsBackendClass 
     */
    protected SettingsBackendClass $settingsBackendClass;

    /**
     * Elenco che definisce i soli ambiti (namespace) consentiti dalla logica del controller, atto a respingere richieste arbitrarie di configurazione.
     * @var array 
     */
    protected array $allowedEnvs = ['general', 'auth', 'upload', 'email'];

    /**
     * Esegue le procedure di inizializzazione standard estendendo BackendController, instanziando il modello base 
     * e la classe helper per le configurazioni.
     *
     * @param RequestInterface $request L'oggetto contenente la richiesta in arrivo.
     * @param ResponseInterface $response L'oggetto per la risposta.
     * @param LoggerInterface $logger Il gestore dei file di logging locale.
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->data['controller'] = 'settingsBackend';

        /* Utilizzo del modello nativo privo di ORM, basato su RAW SQL */
        $this->settingsBackendModel = model(SettingsBackendModel::class);
        $this->settingsBackendClass = new SettingsBackendClass($this->settingsBackendModel);
    }

    /**
     * Elabora la struttura HTML iniziale della pagina impostazioni, fornendo i collegamenti per il caricamento asincrono 
     * dei singoli pannelli funzionali.
     *
     * @return string HTML elaborato della pagina principale.
     */
    public function index(): string
    {
        $this->data['action'] = 'index';
        
        $this->data['title'] = lang('backend/settingsBackend.titles.index');
        $this->data['icon'] = '<i class="fa-solid fa-sliders"></i>';

        return $this->render('backend/settingsBackend/indexView', $this->data);
    }

    /**
     * Riceve una richiesta AJAX per l'ambiente specificato, controlla la legittimità del payload e recupera i dati attivi 
     * (identificando l'origine, DB o file base) restituendo quindi l'HTML parziale del modulo dedicato.
     *
     * @return ResponseInterface Risposta JSON che include il codice del form precompilato.
     */
    public function openSettings(): ResponseInterface
    {
        if ($this->request->isAJAX() && $this->request->is('post')):

            $env = $this->request->getPost('env');

            /* Protezione: Blocco immediato se l'ambiente non è rigorosamente autorizzato */
            if ( ! in_array($env, $this->allowedEnvs, true)):
                return $this->jsonResponse(['result' => false, 'message' => lang('backend/settingsBackend.messages.validationErrors')]);
            endif;

            $namespace = 'Backend\\' . ucfirst($env);

            /* Verifica l'origine dei dati per informare l'interfaccia (DB o Config File) */
            $this->data['isFromDatabase'] = $this->settingsBackendModel->hasDatabaseSettings($namespace);

            /* Estrazione globale dell'intero gruppo */
            $this->data[$env . 'Settings'] = $this->settingsBackendModel->getSettings($namespace);
            if ($env === 'email'):
                $this->data['emailSettings']['SMTPPassConfigured'] = ($this->data['emailSettings']['SMTPPass'] ?? '') !== '';
                $this->data['emailSettings']['SMTPPass'] = '';
            endif;

            /* Carica i dati specifici solo se apriamo la sezione 'general' */
            if ($env === 'general'):
                $this->data['timezones'] = $this->settingsBackendClass->getTimezones();
                $this->data['languages'] = $this->settingsBackendClass->getLanguages();
                $this->data['dateFormats'] = $this->settingsBackendClass->getDateFormats();
                $this->data['themes'] = $this->settingsBackendClass->getThemes();
            endif;

            return $this->jsonResponse(['result' => true, 'output' => view('backend/settingsBackend/partials/index/' . $env . 'SettingsPartial', $this->data)]);

        endif;

        return service('response')->setStatusCode(400);
    }

    /**
     * Sottopone a validazione (mediante regole dinamiche definite sul modello) l'insieme di valori per uno specifico ambiente di sistema,
     * per poi delegarne l'archiviazione. Gestisce automaticamente la rigenerazione in tempo reale di frammenti d'interfaccia 
     * se i cambiamenti (es. localizzazione) impattano aree critiche dell'applicativo.
     *
     * @return ResponseInterface Risposta JSON includente l'esito dell'operazione e i potenziali snippet per l'aggiornamento del DOM.
     */
    public function saveSettings(): ResponseInterface
    {
        if ($this->request->isAJAX() && $this->request->is('post')):

            $posts = $this->request->getPost();
            $env = $posts['env'] ?? '';

            if ( ! in_array($env, $this->allowedEnvs, true)):
                return $this->jsonResponse(['result' => false, 'message' => lang('backend/settingsBackend.messages.validationErrors')]);
            endif;

            $method = $env . 'SettingsValidateRules';
            $rules = $this->settingsBackendModel->{$method}($posts);

            if ( ! $this->validateData($posts, $rules)):
                return $this->jsonResponse(['errors' => $this->validator->getErrors(), 'message' => lang('backend/settingsBackend.messages.validationErrors')]);
            endif;

            $forcePassword = $env === 'auth' || ($env === 'email' && ($posts['SMTPPass'] ?? '') !== '');
            if (($passwordError = ($forcePassword ? $this->requireMandatoryCurrentPassword() : $this->requireCurrentPassword())) !== null):
                return $passwordError;
            endif;

            $namespace = 'Backend\\' . ucfirst($env);

            /* 1. Salvataggio via Model */
            $saveResult = $this->settingsBackendModel->saveSettings($namespace, $posts);

            if ($saveResult !== null && $saveResult['result'] === false) :
                return $this->jsonResponse(['result' => false, 'message' => $saveResult['message']]);
            endif;

            /* 3. Flusso standard senza reload */
            $this->data['isFromDatabase'] = $this->settingsBackendModel->hasDatabaseSettings($namespace);
            $this->data[$env . 'Settings'] = $this->settingsBackendModel->getSettings($namespace);
            if ($env === 'email'):
                $this->data['emailSettings']['SMTPPassConfigured'] = ($this->data['emailSettings']['SMTPPass'] ?? '') !== '';
                $this->data['emailSettings']['SMTPPass'] = '';
            endif;

            $json = [];

            if ($env === 'general'):
                
                /* Le traduzioni nel controller useranno automaticamente la lingua corretta */
                $this->data['timezones'] = $this->settingsBackendClass->getTimezones();
                $this->data['languages'] = $this->settingsBackendClass->getLanguages();
                $this->data['dateFormats'] = $this->settingsBackendClass->getDateFormats();
                $this->data['themes'] = $this->settingsBackendClass->getThemes();

                $this->data['action'] = 'index';
                $this->data['title'] = lang('backend/settingsBackend.titles.index');
                $this->data['icon'] = '<i class="fa-solid fa-sliders"></i>';

                /* Il salvataggio esplicito riallinea la sessione alle impostazioni persistenti. */
                $preferences = new \App\Libraries\Backend\InterfacePreferences();
                $preferences->clear();
                $language = $this->data['generalSettings']['language'];
                $this->request->setLocale($language);
                service('language')->setLocale($language);
                $json['interface'] = [
                    'language' => $language,
                    'theme' => $this->data['generalSettings']['theme'],
                    'font' => $preferences->font(),
                    'translations' => $preferences->translations($language),
                ];

            endif;

            /* Corretti gli errori di sintassi qui sotto */
            $json['result'] = true;
            $json['message'] = $saveResult['message'];
            $json['output'] = view('backend/settingsBackend/partials/index/' . $env . 'SettingsPartial', $this->data);

            $json['passwordConfirmationRequired'] = \App\Libraries\Backend\CurrentPasswordGuard::required();
            return $this->jsonResponse($json);

        endif;

        return service('response')->setStatusCode(400);
    }

    /**
     * Restituisce tramite JSON puro l'insieme delle configurazioni attive appartenenti a uno specifico ambiente.
     * 
     * Implementa controlli sulla validità del parametro ambientale e accetta array filtro opzionali (keys) per ridurre l'output.
     *
     * @return ResponseInterface Payload asincrono contenente il dizionario chiave/valore dei settings richiesti.
     */
    public function getSettings(): ResponseInterface
    {
        if ($this->request->isAJAX() && $this->request->is('post')):

            $posts = $this->request->getPost();
            $env = $posts['env'] ?? '';

            /* Protezione: Impedisce l'estrazione di informazioni da namespace di sistema non autorizzati */
            if ( ! in_array($env, $this->allowedEnvs, true)):
                return $this->jsonResponse(['result' => false, 'message' => lang('backend/settingsBackend.messages.validationErrors')]);
            endif;

            $namespace = 'Backend\\' . ucfirst($env);
            $keysFilter = $posts['keys'] ?? null;

            $this->data[$env . 'Settings'] = $this->settingsBackendModel->getSettings($namespace, $keysFilter);
            if ($env === 'email' && array_key_exists('SMTPPass', $this->data['emailSettings'])):
                $this->data['emailSettings']['SMTPPassConfigured'] = ($this->data['emailSettings']['SMTPPass'] ?? '') !== '';
                $this->data['emailSettings']['SMTPPass'] = '';
            endif;

            /* Carica i dati specifici solo se apriamo la sezione 'general' */
            if ($env === 'general'):
                $this->data['timezones'] = $this->settingsBackendClass->getTimezones();
                $this->data['languages'] = $this->settingsBackendClass->getLanguages();
                $this->data['dateFormats'] = $this->settingsBackendClass->getDateFormats();
                $this->data['themes'] = $this->settingsBackendClass->getThemes();
            endif;

            return $this->jsonResponse(['result' => true, 'data' => $this->data[$env . 'Settings']]);

        endif;

        return service('response')->setStatusCode(400);
    }

    /**
     * Esegue un comando di rimozione per annullare ogni preferenza archiviata a livello di database per il dato ambiente, 
     * ripristinando in modo forzato il sistema ad aderire ai valori di default definiti a monte dall'applicazione.
     *
     * @return ResponseInterface Esito dell'operazione di pulizia restituito in formato JSON.
     */
    public function deleteSettings(): ResponseInterface
    {
        if ($this->request->isAJAX() && $this->request->is('post')) :

            if (($passwordError = ($this->request->getPost('env') === 'auth' ? $this->requireMandatoryCurrentPassword() : $this->requireCurrentPassword())) !== null):
                return $passwordError;
            endif;

            $posts = $this->request->getPost();
            $env = $posts['env'] ?? '';

            /* Protezione: Impedisce la cancellazione arbitraria di dati sul database tramite manipolazione di env */
            if ( ! in_array($env, $this->allowedEnvs, true)) :
                return $this->jsonResponse(['result'  => false, 'message' => lang('backend/settingsBackend.messages.validationErrors')]);
            endif;

            $namespace = 'Backend\\' . ucfirst($env);

            /* Elimina interamente la sezione configurata */
            $deleted = $this->settingsBackendModel->deleteSettings($namespace);

            if ($deleted === null) :
                return $this->jsonResponse(['result'  => false, 'message' => lang('backend/settingsBackend.messages.alreadyDefault')]);
            endif;

            if ($deleted === false) :
                return $this->jsonResponse(['result'  => false, 'message' => lang('backend/settingsBackend.messages.deleteError')]);
            endif;

            if ($env === 'general'):
                $preferences = new \App\Libraries\Backend\InterfacePreferences();
                $preferences->clear();
                session()->setFlashdata([
                    'message' => lang('backend/settingsBackend.messages.deleteSuccess', [], $preferences->language()),
                    'message_translation' => ['key' => 'backend/settingsBackend.messages.deleteSuccess', 'escaped' => true],
                    'class' => 'app-alert-success fw-bold',
                    'icon' => '<i class="fa-solid fa-circle-check"></i>',
                    'settingsReopenGeneral' => true
                ]);
            endif;

            return $this->jsonResponse([
                'result'  => true,
                'message' => lang('backend/settingsBackend.messages.deleteSuccess'),
                'reload'  => $env === 'general',
                'passwordConfirmationRequired' => \App\Libraries\Backend\CurrentPasswordGuard::required()
            ]);

        endif;

        return service('response')->setStatusCode(400);
    }

    /**
     * Interroga in modo preliminare il modello per individuare la presenza effettiva di customizzazioni 
     * nell'ambiente specificato, permettendo al client di determinare se attivare o precludere la comparsa dell'avviso modale di ripristino.
     *
     * @return ResponseInterface Risposta JSON di controllo logico (true: record rinvenuti, false: database vuoto per tale dominio).
     */
    public function checkDeleteSettings(): ResponseInterface
    {
        if ($this->request->isAJAX() && $this->request->is('post')) :

            $env = $this->request->getPost('env');

            if ( ! in_array($env, $this->allowedEnvs, true)) :
                return $this->jsonResponse(['result'  => false, 'message' => lang('backend/settingsBackend.messages.validationErrors')]);
            endif;

            $namespace = 'Backend\\' . ucfirst($env);

            /* Se non ci sono dati, blocca e restituisce il messaggio informativo */
            if ( ! $this->settingsBackendModel->hasDatabaseSettings($namespace)) :
                return $this->jsonResponse([
                    'result'  => false, 
                    'message' => lang('backend/settingsBackend.messages.alreadyDefault')
                ]);
            endif;

            /* Se i dati esistono, restituisce true dando il via libera al modale JS */
            return $this->jsonResponse(['result' => true]);

        endif;

        return service('response')->setStatusCode(400);
    }
}
