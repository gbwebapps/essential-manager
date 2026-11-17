<?php declare(strict_types = 1); 

$language = [
	'titles' => [
		'index' => 'Profilo', 
		'general' => 'Dati generali', 
		'edit' => 'Aggiorna', 
		'permissions' => 'Permessi', 
		'images' => 'Immagini', 
		'tokens' => 'Tokens', 
		'resetPassword' => 'Reset password', 
		'security' => 'Sicurezza', 
		'twoFactor' => 'Autenticazione a 2 fattori', 
		'totpSetupTitle' => 'Configura l\'applicazione di autenticazione',
	],
	'leftMenu' => [
		'general' => 'Dati generali', 
		'edit' => 'Aggiorna', 
		'permissions' => 'Permessi', 
		'images' => 'Immagini', 
		'tokens' => 'Tokens', 
		'resetPassword' => 'Reset password', 
		'security' => 'Sicurezza'
	],
	'labels' => [
		'firstname' => 'Nome', 
		'lastname' => 'Cognome', 
		'email' => 'Email', 
		'phone' => 'Telefono', 
		'assigned' => 'Assegnato', 
		'notAssigned' => 'Non assegnato', 
		'note' => 'Note aggiuntive', 
		'operatingSystem' => 'Sistema operativo', 
		'browser' => 'Browser web', 
		'ipAddress' => 'Indirizzo IP', 
		'createdToken' => 'Data inizio', 
		'expiredToken' => 'Data scadenza', 
		'typeToken' => 'Tipo', 
		'session' => 'Sessione',
        'activation' => 'Attivazione', 
        'methodNoneTitle' => 'Nessuno', 
        'methodEmailTitle' => 'Email', 
        'methodTotpTitle' => 'Authenticator app', 
        'methodNoneDesc'  => 'Disattiva la verifica in due passaggi. L\'accesso avverrà inserendo solo email e password.',
        'methodEmailDesc' => 'Ricevi un codice di sicurezza usa e getta direttamente sulla tua casella email aziendale.',
        'methodTotpDesc'  => 'Scansiona il codice QR con app come Google Authenticator, Authy o Microsoft Authenticator per generare codici temporanei istantanei.',
        'totpSetupDesc' => 'Scansiona il codice QR con la tua app di autenticazione (es. Google Authenticator) o inserisci la chiave manualmente per iniziare a ricevere i codici di sicurezza.',
        'totpSecretKey'      => 'Chiave segreta (configurazione manuale)',
		'enterOtpCode' => 'Inserisci il codice',
		'rememberMe' => 'Ricordami', 
		'currentDevice' => 'Sessione corrente',
		'mobile' => 'Dispositivo mobile',
		'robot' => 'Robot',
		'currentPassword' => 'Password corrente',
		'currentPasswordEmailHelp' => 'Richiesta solo se modifichi l\'indirizzo email.'
	], 
	'placeholders' => [
		'firstname' => 'Inserisci nome...', 
		'lastname' => 'Inserisci cognome...', 
		'email' => 'Inserisci email...', 
		'phone' => 'Inserisci telefono...', 
		'note' => 'Inserisci note aggiuntive...',
		'currentPassword' => 'Inserisci la password corrente...',
	], 
	'buttons' => [
		'sendData' => 'Salva dati', 
		'refreshData' => 'Ricarica dati', 
		'reload' => 'Ricarica pannello', 
		'resetPassword' => 'Procedi al reset della password',
		'verifyAndActivate' => 'Verifica e attiva', 
		'undoClose' => 'Chiudi configurazione ed esci', 
		'delete' => 'Elimina',
		'showPassword' => 'Mostra password'
	], 
	'errors' => [
		'id' => 'ID non conforme.',
		'safeText' => 'Caratteri non ammessi.',
		'currentPasswordRequired' => 'Inserisci la password corrente.',
		'currentPasswordInvalid' => 'La password corrente non è corretta.',
	], 
	'audits' => [
		'updateData' => 'Aggiornamento dati generali %s %s', 
		'deleteToken' => 'Eliminazione token %s %s', 
		'resetPassword' => 'Reset password %s %s', 
		'activateMethod' => 'Impostazione metodo 2Fa %s %s', 
		'activateTotp' => 'Impostazione metodo 2Fa %s %s', 
	], 
	'messages' => [
		'areYouSureStartingReset' => 'Sei sicuro di voler iniziare il processo di reset password?', 
		'noDataChanged' => 'Non sono state effettuate modifiche.', 
		'editSuccess' => 'Utente %s %s aggiornato con successo.', 
		'editError' => 'Aggiornamento utente non andato a buon fine.', 
		'resetPasswordSuccessNoEmail' => 'Password utente %s %s resettata con successo, ma la mail non è stata inviata. Contattare utente.', 
		'resetPasswordSuccess' => 'Password utente %s %s resettata con successo.', 
		'resetPasswordError' => 'Reset password utente non andato a buon fine.', 
		'validateToastErrors' => '%s',
		'validationErrors' => 'Errori di validazione.', 
		'areYouSureRefreshData' => 'Sei sicuro di voler ricaricare i dati?', 
		'areYouSureDeleteToken' => 'Sei sicuro di eliminare il token di <b>%s %s</b>?', 
		'noTokensFound' => 'Non sono presenti tokens.', 
		'deleteTokenSuccess' => 'Il token di %s %s è stato eliminato con successo.', 
		'deleteTokenError' => 'Eliminazione token non andata a buon fine', 
		'expiringDate' => '<div class="pb-3">Hai richiesto il reset della password.</div><div class="pb-3">Verifica la tua email e completa l\'operazione.</div>Hai tempo fino al %s',
		'methodNotValid' => 'Metodo non valido o non supportato.', 
		'updateSecuritySettingsError' => 'Impossibile aggiornare le impostazioni di sicurezza.', 
		'updateSecuritySettingsSuccess' => 'Impostazioni di sicurezza aggiornate con successo.', 
		'configurationInitializeError' => 'Impossibile inizializzare la configurazione TOTP.', 
		'noConfigurationSession' => 'Nessuna sessione di configurazione TOTP attiva o scaduta.', 
		'wrongCode' => 'Il codice inserito non è valido.', 
		'missingCode' => 'Inserire il codice.', 
		'totpActivationNotPossible' => 'Impossibile attivare l\'autenticazione TOTP.', 
		'totpConfigurationSuccess' => 'L\'applicazione di autenticazione è stata configurata con successo.', 
		'areYouSureChangeMethod' => 'Sei sicuro di voler cambiare il metodo del secondo fattore di autenticazione?', 
		'cannotDeleteCurrentToken' => 'Non è possibile eliminare la sessione corrente.',
		'tooManyResetRequests' => 'Troppe richieste di reset password. Riprova più tardi.',
		'tooManyAttempts' => 'Troppi tentativi. Riprova più tardi.'
	]
];

$language['titles']['index'] = 'Area riservata';
$language['labels']['session'] = 'Sessione';
$language['labels']['activation'] = 'Attivazione';
$language['labels']['rememberMe'] = 'Ricordami';
$language['labels']['unknown'] = 'Sconosciuto';
$language['labels']['id'] = 'ID';
$language['titles']['deleteAccount'] = 'Elimina account';
$language['leftMenu']['deleteAccount'] = 'Elimina account';
$language['buttons']['deleteAccount'] = 'Elimina account';
$language['messages']['removalDescription'] = 'La richiesta chiude subito tutti i tuoi accessi. Entro %d giorni puoi accedere di nuovo e confermare l’annullamento della richiesta. Dopo la scadenza l’account sarà da eliminare definitivamente.';
$language['messages']['removalEmail'] = 'Il tuo indirizzo email rimane invariato e riservato finché l’account non viene eliminato definitivamente.';
$language['messages']['confirmRemoval'] = 'Vuoi richiedere l’eliminazione del tuo account? Sarai disconnesso subito. Entro %d giorni potrai annullare la richiesta accedendo di nuovo.';
$language['messages']['removalRequested'] = 'Richiesta di eliminazione registrata. Tutti gli accessi sono stati chiusi.';
$language['messages']['removalUnavailable'] = 'La richiesta non è disponibile per questo account. Accedi nuovamente e verifica lo stato del profilo.';
$language['messages']['removalError'] = 'Impossibile registrare la richiesta di eliminazione. Riprova.';
$language['audits']['removalRequested'] = 'Richiesta personale di eliminazione account.';
return $language;
