<?php declare(strict_types = 1);

namespace App\Libraries\Backend;

use App\Models\Backend\SettingsBackendModel;

/**
 * Classe di supporto (utility/presentazione) dedicata alla gestione dell'interfaccia delle Impostazioni (Settings).
 * 
 * Fornisce fusi orari, formati data e lingue per popolare i selettori del pannello.
 */
class SettingsBackendClass 
{
	/**
	 * Istanza del modello di riferimento, a disposizione per estrarre 
	 * o confrontare parametri di configurazione globali propedeutici alla costruzione dell'interfaccia.
	 * @var SettingsBackendModel 
	 */
	protected SettingsBackendModel $settingsBackendModel;

	/**
	 * Inizializza la libreria di supporto iniettando il modello associato alle impostazioni di sistema.
	 *
	 * @param SettingsBackendModel $settingsBackendModel Il modello preposto alla gestione e all'elaborazione dei parametri globali
	 */
	public function __construct(SettingsBackendModel $settingsBackendModel) 
	{
		$this->settingsBackendModel = $settingsBackendModel;
	}

	/**
	 * Recupera la lista completa e aggiornata dei fusi orari (timezones) supportati a livello server.
	 * 
	 * Sfrutta la funzione nativa di PHP per estrarre l'elenco standard degli identificatori di fuso orario, 
	 * fornendo il dataset primario necessario ad alimentare il relativo selettore nelle impostazioni di sistema.
	 *
	 * @return array Array indicizzato contenente tutti gli identificatori validi per i fusi orari (es. 'Europe/Rome')
	 */
	public function getTimezones()
	{
	    return timezone_identifiers_list();  
	}

	/**
	 * Costruisce il dizionario delle lingue ufficialmente supportate dal pannello di amministrazione.
	 * 
	 * Mappa i codici standard ISO a due lettere (chiavi) alle rispettive etichette tradotte (valori), permettendo 
	 * la generazione dinamica del selettore di localizzazione con stringhe sempre pertinenti all'idioma attuale dell'operatore.
	 *
	 * @return array Array associativo [codice_lingua => etichetta_localizzata]
	 */
	public function getLanguages()
	{
	    return \App\Libraries\LanguageOptions::all('native');
	}

	/**
	 * Costruisce il dizionario dei temi grafici disponibili nel backend.
	 *
	 * @return array Array associativo [tema => etichetta_localizzata]
	 */
	public function getThemes(): array
	{
	    return [
	        'white' => lang('backend/settingsBackend.themes.white'),
	        'black' => lang('backend/settingsBackend.themes.black'),
	        'grey'  => lang('backend/settingsBackend.themes.grey'),
	        'dark'  => lang('backend/settingsBackend.themes.dark')
	    ];
	}

	/**
	 * Definisce il set di formati data/ora (basati sui pattern ICU) disponibili per la configurazione globale.
	 * 
	 * Fornisce un catalogo strutturato di formati standard (europei estesi/compatti, anglosassoni con meridiem e ISO tecnico) 
	 * mappati sulle rispettive descrizioni descrittive localizzate. Questi pattern detteranno l'output visivo 
	 * dei marcatori temporali nell'intero applicativo.
	 *
	 * @return array Array associativo [pattern_icu => descrizione_localizzata]
	 */
	public function getDateFormats(): array
    {
        return [
            /* Formati Europei / Internazionali (Giorno-Mese-Anno) */
            'd MMMM yyyy HH:mm:ss'  => lang('backend/settingsBackend.dateFormats.extended'),
            'dd/MM/yyyy HH:mm'      => lang('backend/settingsBackend.dateFormats.compact'),

            /* Formati Anglosassoni USA (Mese-Giorno-Anno, 12h) */
            'MMMM d yyyy h:mm:ss a' => lang('backend/settingsBackend.dateFormats.usExtended'),
            'MM/dd/yyyy h:mm a'     => lang('backend/settingsBackend.dateFormats.usCompact'),

            /* Formato Asiatico / Tecnico (Anno-Mese-Giorno) */
            'yyyy-MM-dd HH:mm:ss'   => lang('backend/settingsBackend.dateFormats.isoStandard')
        ]; 
    }
}
