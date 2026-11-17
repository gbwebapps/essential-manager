<?php declare(strict_types = 1);

namespace App\Models\Backend;

/**
 * Condivide lettura, persistenza e ripristino delle impostazioni nella tabella settings.
 *
 * I model delle sezioni definiscono i campi scrivibili e le validazioni. L'helper setting()
 * usa questo model per leggere default e sovrascritture senza dipendere da una sezione UI.
 */
class SettingsModel extends BackendModel
{
    /** Catalogo delle risposte e degli audit della sezione chiamante. */
    protected string $settingsLanguage = 'backend/settingsBackend';

    /** Ambito registrato negli audit delle operazioni amministrative. */
    protected string $settingsAuditSection = 'settingsBackend';

    /**
     * Cache in memoria. Conserva i dati letti dal database per ogni sezione (namespace). 
     * Se viene richiesto più volte lo stesso gruppo di impostazioni, il sistema lo legge da qui senza interrogare di nuovo il DB.
     * 
     * @var array 
     */
    protected array $settingsCache = [];

    private ?string $smtpPasswordCache = null;

    /**
     * Metodo di inizializzazione nativo di CodeIgniter.
     * 
     * Prepara il modello caricando le dipendenze essenziali ereditate dal BackendModel padre.
     */
	protected function initModel(): void 
	{
		parent::initModel();
	}

    /**
     * Recupera e fonde le impostazioni lette dal database con i valori di default.
     * 
     * Il cuore di questo modello. Controlla prima se il gruppo di chiavi è già in memoria (cache). 
     * Se non c'è, fa una singola query al DB e salva i dati. Successivamente, carica la classe di 
     * configurazione nativa (`Config\NomeSezione`) e sovrascrive i suoi valori di default 
     * con quelli personalizzati trovati nel DB.
     *
     * @param string $namespace Il percorso/nome della classe (es. 'Backend\Auth')
     * @param array|null $keys (Opzionale) Permette di filtrare e restituire solo alcune chiavi specifiche dell'array risultante
     * @return array Array associativo con tutte le configurazioni finali pronte all'uso
     */
    public function getSettings(string $namespace, ?array $keys = null): array
    {
        /** Se il gruppo non è ancora presente nella nostra cache in-memory, lo estraiamo dal DB */
        if ( ! isset($this->settingsCache[$namespace])) :
            
            $sql = "SELECT `key`, `value` FROM `settings` WHERE `class` = ?";
            $query = $this->db->query($sql, [$namespace]);
            $rows = $query->getResultArray();

            $dbSettings = [];
            foreach ($rows as $row) :
                $dbSettings[$row['key']] = $row['value'];
            endforeach;

            /** Salviamo il blocco intero nella variabile di classe */
            $this->settingsCache[$namespace] = $dbSettings;

        endif;

        /** Recuperiamo i valori memorizzati nella nostra variabile del modello */
        $cachedData = $this->settingsCache[$namespace];

        /** Carichiamo i valori nativi di fallback presenti nel file Config di CodeIgniter */
        $configClass = '\\Config\\' . $namespace;
        $defaultSettings = [];

        if (class_exists($configClass)) :
            $configInstance = new $configClass();
            $defaultSettings = get_object_vars($configInstance);
        endif;

        /* Le mappe tradotte sono archiviate come JSON, mantenendo i default tipizzati nei file. */
        foreach ($cachedData as $key => $value):
            if (isset($defaultSettings[$key]) && is_array($defaultSettings[$key])):
                $decoded = is_string($value) ? json_decode($value, true) : $value;
                $cachedData[$key] = is_array($decoded) ? $decoded : $defaultSettings[$key];
            endif;
        endforeach;

        /** Uniamo i default con i dati in-memory (il DB vince sui default) */
        $finalSettings = array_merge($defaultSettings, $cachedData);

        if ($namespace === 'Backend\Email' && isset($finalSettings['SMTPPass'])):
            if ($this->smtpPasswordCache === null):
                $this->smtpPasswordCache = (new \App\Libraries\SettingsSecrets())->decrypt((string) $finalSettings['SMTPPass']);
            endif;
            $finalSettings['SMTPPass'] = $this->smtpPasswordCache;
        endif;

        /** Se sono state richieste chiavi specifiche, filtriamo l'array */
        if ($keys !== null) :
            $finalSettings = array_intersect_key($finalSettings, array_flip($keys));
        endif;

        return $finalSettings;
    }

    /**
     * Controlla fisicamente sul database se esiste almeno un salvataggio per un dato namespace.
     * 
     * Viene usato prima di provare a cancellare o aggiornare dati per capire 
     * se si tratta del primo salvataggio in assoluto o di una modifica.
     *
     * @param string $namespace Il nome della sezione (es. 'Backend\General')
     * @return bool True se c'è almeno un record nel DB, False altrimenti
     */
    public function hasDatabaseSettings(string $namespace): bool
    {
        $sql = "SELECT COUNT(*) as total FROM `settings` WHERE `class` = ?";
        $query = $this->db->query($sql, [$namespace]);
        $row = $query->getRowArray();

        return isset($row['total']) && (int) $row['total'] > 0;
    }

    /**
     * Salva (o aggiorna) massivamente le impostazioni inviate dall'interfaccia web.
     * 
     * Il metodo prende i dati, li filtra in base alla Whitelist della sezione (scartando input pericolosi). 
     * Esegue un controllo rapido per assicurarsi che i dati siano davvero cambiati (evitando query inutili). 
     * Se ci sono novità, svuota la cache locale, prepara i dati (es. unendo le estensioni dei file in una stringa separata da '|') 
     * e lancia una velocissima "Bulk Insert" che inserisce i dati o, in caso esistano già, li aggiorna ("on duplicate key update"). 
     * Alla fine, registra l'operazione nei log di sistema.
     *
     * @param string $namespace Il nome della sezione (es. 'Backend\Upload')
     * @param array $posts I dati inviati dal modulo web
     * @return array|null Esito (result) e messaggio da visualizzare
     */
    public function saveSettings(string $namespace, array $posts): ?array
    {
        /** 1. Recuperiamo la lista dei campi consentiti in base al namespace */
        $allowedFields = $this->allowedSettingsFields($namespace);

        /** 2. Filtriamo immediatamente l'input lasciando solo i campi autorizzati */
        $posts = $this->checkAllowedFields($posts, $allowedFields);
        if ($posts === []):
            return ['result' => false, 'message' => lang($this->settingsLanguage . '.messages.validationErrors')];
        endif;

        if ($namespace === 'Backend\Email' && ($posts['SMTPPass'] ?? '') === ''):
            unset($posts['SMTPPass']);
        endif;

        /** 3. Controllo di sbarramento e rilevazione campi critici */
        if ($this->hasDatabaseSettings($namespace)) :

            $changedKeys = $this->getChangedKeys($namespace, $posts);

            if (empty($changedKeys)) :
                return ['result' => false, 'message' => lang($this->settingsLanguage . '.messages.noDataChanged')];
            endif;

        endif;

        /** 4. Svuota la cache locale */
        if (isset($this->settingsCache[$namespace])) :
            unset($this->settingsCache[$namespace]);
            if ($namespace === 'Backend\Email'):
                $this->smtpPasswordCache = null;
            endif;
        endif;

        if (isset($posts['allowedExtensions']) && is_array($posts['allowedExtensions'])) :
            $posts['allowedExtensions'] = implode('|', $posts['allowedExtensions']);
        endif;

        /** 5. Costruzione della scrittura massiva */
        $valuesQueries = [];
        $params = [];

        foreach ($posts as $key => $value) :
            $valuesQueries[] = '(?, ?, ?)';
            if ($namespace === 'Backend\Email' && $key === 'SMTPPass'):
                $value = (new \App\Libraries\SettingsSecrets())->encrypt((string) $value);
            endif;
            if (is_array($value)):
                $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
            endif;
            array_push($params, $namespace, $key, $value);
        endforeach;

        $sql = "insert into `settings` (`class`, `key`, `value`) 
                values " . implode(', ', $valuesQueries) . " 
                on duplicate key update `value` = values(`value`), `updated_at` = current_timestamp";

        try {
            $saved = $this->db->query($sql, $params);
        }
        catch (\Throwable $e) {
            log_message('error', 'Errore salvataggio settings ' . $namespace . ': ' . $e->getMessage());
            return ['result' => false, 'message' => lang($this->settingsLanguage . '.messages.saveError')];
        }

        if ($saved === false):
            log_message('error', 'Errore salvataggio settings ' . $namespace . '.');
            return ['result' => false, 'message' => lang($this->settingsLanguage . '.messages.saveError')];
        endif;

        $this->clearSettingsCache($namespace);
        $currentAdmin = service('authorization')->currentAdmin();
        log_admin_activity('SAVE_SETTINGS', $this->settingsAuditSection, lang($this->settingsLanguage . '.audits.saveSettings'), $currentAdmin);

        return ['result' => true, 'message' => lang($this->settingsLanguage . '.messages.saveSuccess')];
    }

    /**
     * Confronta i dati in arrivo con quelli salvati e restituisce la lista esatta delle chiavi modificate.
     * 
     * Estrae le impostazioni attuali (compresi i default uniti col DB), prende i nuovi input 
     * e li confronta uno a uno. Svolge anche un lavoro di "normalizzazione" rapida: se una chiave 
     * è un array (es. la selezione multipla delle estensioni), la ordina e la trasforma in stringa prima 
     * di fare il paragone per evitare finti rilevamenti dovuti solo all'ordine disordinato degli elementi.
     *
     * @param string $namespace Il nome della sezione da analizzare
     * @param array $posts I nuovi dati proposti dal form
     * @return array Elenco delle chiavi che hanno subìto una reale modifica
     */
    public function getChangedKeys(string $namespace, array $posts): array
    {
        $current = $this->getSettings($namespace);
        $changed = [];

        foreach ($posts as $key => $value) :
            if ( ! array_key_exists($key, $current)) :
                continue;
            endif;

            /** Normalizzazione immediata */
            if (is_array($value) && is_array($current[$key])):
                $valPost = array_map(static fn($item): string => trim((string) $item), $value);
                $valDb = array_map(static fn($item): string => trim((string) $item), $current[$key]);
                ksort($valPost);
                ksort($valDb);
            elseif (is_array($value)) :
                $filtered = array_filter($value);
                sort($filtered);
                $valPost = implode('|', $filtered);

                $dbArray = array_filter(explode('|', $current[$key]));
                sort($dbArray);
                $valDb = implode('|', $dbArray);
            else :
                $valPost = trim((string) $value);
                $valDb   = trim((string) $current[$key]);
            endif;

            if ($valPost !== $valDb) :
                $changed[] = $key;
            endif;
        endforeach;

        return $changed;
    }

    /**
     * Ripristina un'intera sezione ai suoi valori di default ("Ripristina predefiniti").
     * 
     * Esegue un controllo rapido per capire se ci sono personalizzazioni nel DB. Se ci sono, 
     * cancella fisicamente tutti i record legati a quel namespace, svuota la cache locale, e 
     * scrive un evento nell'Audit log per mantenere traccia dell'operazione.
     *
     * @param string $namespace Il nome della sezione da ripulire
     * @return bool|null True in caso di successo, False in caso di errore, Null se non esistono dati da eliminare
     */
    public function deleteSettings(string $namespace): ?bool
    {
        /** Verifica preliminare se ci sono effettivamente dati da cancellare */
        if ( ! $this->hasDatabaseSettings($namespace)):
            return null;
        endif;

        /** Esegue l'eliminazione globale del namespace */
        $sql = "delete from `settings` where `class` = ?";

        try {
            $deleted = $this->db->query($sql, [$namespace]);
        }
        catch (\Throwable $e) {
            log_message('error', 'Errore eliminazione settings ' . $namespace . ': ' . $e->getMessage());
            return false;
        }

        if ($deleted === false):
            log_message('error', 'Errore eliminazione settings ' . $namespace . '.');
            return false;
        endif;

        /** Svuota la cache locale in memoria per questo namespace */
        if (isset($this->settingsCache[$namespace])) :
            unset($this->settingsCache[$namespace]);
            if ($namespace === 'Backend\Email'):
                $this->smtpPasswordCache = null;
            endif;
        endif;

        $this->clearSettingsCache($namespace);
        $currentAdmin = service('authorization')->currentAdmin();
        log_admin_activity('DELETE_SETTINGS', $this->settingsAuditSection, lang($this->settingsLanguage . '.audits.deleteSettings'), $currentAdmin);

        return true;
    }

    /**
     * Rilevatore rapido di cambiamenti (True/False).
     * 
     * Simile a `getChangedKeys()`, ma ottimizzato per le performance. Al primo campo che 
     * risulta diverso rispetto al database, interrompe il ciclo e risponde "True". 
     * Molto utile per decidere velocemente se fermare o meno l'azione di salvataggio.
     *
     * @param string $namespace Il nome della sezione da analizzare
     * @param array $posts I nuovi dati proposti
     * @return bool True se è stato trovato almeno un parametro modificato, False se è tutto identico
     */
    public function hasSettingsChanged(string $namespace, array $posts): bool
    {
        $current = $this->getSettings($namespace);

        foreach ($posts as $key => $value) :
            if ( ! array_key_exists($key, $current)) :
                continue;
            endif;

            /** Normalizzazione immediata: se è un array, lo ordina e lo unisce con il pipe */
            if (is_array($value) && is_array($current[$key])):
                $valPost = array_map(static fn($item): string => trim((string) $item), $value);
                $valDb = array_map(static fn($item): string => trim((string) $item), $current[$key]);
                ksort($valPost);
                ksort($valDb);
            elseif (is_array($value)) :
                $filtered = array_filter($value);
                sort($filtered);
                $valPost = implode('|', $filtered);

                $dbArray = array_filter(explode('|', $current[$key]));
                sort($dbArray);
                $valDb = implode('|', $dbArray);
            else :
                $valPost = trim((string) $value);
                $valDb   = trim((string) $current[$key]);
            endif;

            if ($valPost !== $valDb) :
                return true;
            endif;
        endforeach;

        return false;
    }

    /**
     * Impedisce scritture dal model di sola lettura usato dall'helper globale.
     *
     * @param string $namespace Classe di configurazione richiesta.
     * @return array<string>
     */
    protected function allowedSettingsFields(string $namespace): array
    {
        return [];
    }


    /**
     * Invalida la cache della sezione e quella dell'istanza letta dall'helper globale setting().
     *
     * @param string $namespace Classe di configurazione appena modificata.
     * @return void
     */
    public function clearSettingsCache(string $namespace): void
    {
        unset($this->settingsCache[$namespace]);
        if ($namespace === 'Backend\Email'):
            $this->smtpPasswordCache = null;
        endif;
        $shared = model(self::class);
        if ($shared !== $this):
            $shared->clearSettingsCache($namespace);
        endif;
    }

}
