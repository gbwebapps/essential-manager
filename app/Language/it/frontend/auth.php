<?php declare(strict_types = 1); 

$language = [
	'titles' => [
		'index' => 'Benvenuto', 
		'login' => 'Accedi', 
		'resetPassword' => 'Resetta password', 
		'setPassword' => 'Imposta password', 
		'verify' => 'Verifica OTP'
	],
	'labels' => [
		'email' => 'Email', 
		'username' => 'Nome utente', 
		'password' => 'Password', 
		'rememberMe' => 'Ricordami', 
		'newPassword' => 'Inserisci password', 
		'confirmNewPassword' => 'Conferma password', 
		'code' => 'Codice OTP'
	], 
	'placeholders' => [
		'email' => 'Inserisci qui indirizzo email...', 
		'username' => 'Inserisci qui username...', 
		'password' => 'Inserisci qui password...', 
		'newPassword' => 'Inserisci qui password...', 
		'confirmNewPassword' => 'Conferma password', 
		'code' => 'Inserisci qui codice OTP...', 
	], 
	'buttons' => [
		'login' => 'Accedi', 
		'resetPassword' => 'Resetta password', 
		'setPassword' => 'Imposta password', 
		'verify' => 'Verifica OTP'

	], 
	'links' => [
		'login' => 'Accedi', 
		'resetPassword' => 'Resetta password'
	], 
	'errors' => [
		'passwordFormatNotValid' => 'La password non rispetta i requisiti di sicurezza.',
	], 
	'audits' => [
		'loginRefused' => 'Tentativo di accesso con account inesistente', 
		'loginBlocked' => 'Accesso rifiutato per limite tentativi',
		'loginFailed' => 'Tentativo di accesso fallito %s %s', 
		'2faRequired' => 'Richiesto codice di verifica 2FA %s %s %s', 
		'loginSuccess' => 'Accesso effettuato %s %s', 
		'resetPasswordAuth' => 'Reset password %s %s', 
		'setPassword' => 'Impostazione password %s %s', 
		'verifyFailed' => 'Tentativo di accesso con account inesistente', 
		'2faBlocked' => 'Blocco 2FA %s %s', 
		'2faFailed' => 'Codice 2FA errato o scaduto %s %s', 
	], 
	'messages' => [
		'goodbye' => 'Arrivederci %s %s.', 
		'welcome' => 'Buongiorno %s %s.', 
		'loginFailed' => 'Tentativo di accesso fallito.', 
	    'loginNeeded' => 'Effettuare il login per accedere.', 
	    'resetPasswordFailed' => 'Errore durante reset password.', 
		'setPasswordSuccess' => 'Password impostata. <a class="fw-bold text-white" href="' . base_url('auth/login') . '"><i class="fa-solid fa-right-to-bracket"></i> Accedi.</a>', 
		'setPasswordFailed' => 'Errore durante impostazione password.', 
		'setPasswordError' => 'Errore durante impostazione password.', 
		'checkAuthError' => 'Codice di autenticazione non valido oppure scaduto.', 
		'tooManyAttempts' => 'Limite di tentativi raggiunto. Riprova più tardi.',
	    'currentSessionOn' => '%s %s sessione ancora in corso.', 
		'validationErrors' => 'Errori di validazione.', 
		'resetPasswordSuccess' => 'Se l\'indirizzo è associato a un account attivo, riceverai un\'email con le istruzioni per reimpostare la password.',
		'resetPasswordSuccessNoEmail' => 'Processo di reset password impostato, ma l\'email non è stata inviata. Contattare l\'utente.', 
		'expiredCode' => 'Codice OTP scaduto.',
		'wrongCode' => 'Codice OTP errato.', 
		'twoFactorEmailSendError' => 'Impossibile inviare il codice di verifica via email. Riprova tra qualche istante.',
	]
];

$language['titles']['register'] = 'Registrazione';
$language['buttons']['register'] = 'Registrati';
$language['messages']['registerSuccess'] = 'Se puoi registrarti con questo indirizzo, riceverai un collegamento per impostare la password. Controlla la posta.';
$language['messages']['registerError'] = 'Registrazione non riuscita. Riprova piu tardi.';
$language['email']['registerSubject'] = 'Attiva il tuo account';
$language['messages']['sessionExpired'] = 'La sessione è scaduta. Accedi nuovamente.';
$language['messages']['verifyError'] = 'Impossibile completare la verifica. Riprova.';
$language['messages']['logoutError'] = 'Errore durante la disconnessione.';
$language['links']['accessOrRegister'] = 'Accedi o registrati';
$language['titles']['restore'] = 'Ripristina account';
$language['buttons']['restoreAccount'] = 'Annulla la richiesta di eliminazione e accedi';
$language['messages']['confirmAccountRestore'] = 'L’account è in attesa di eliminazione. Conferma per annullare la richiesta e accedere.';
$language['audits']['removalCancelled'] = 'Richiesta di eliminazione annullata dall’utente al login.';
$language['links']['returnToLogin'] = 'Torna al login';
return $language;
