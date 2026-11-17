<?php declare(strict_types = 1); 

$language = [
	'titles' => [
		'index' => 'Bienvenue', 
		'login' => 'Se connecter', 
		'resetPassword' => 'Réinitialiser le mot de passe', 
		'setPassword' => 'Définir le mot de passe', 
		'verify' => 'Vérifier le code OTP'
	],
	'labels' => [
		'email' => 'E-mail', 
		'username' => 'Nom d\'utilisateur', 
		'password' => 'Mot de passe', 
		'rememberMe' => 'Se souvenir de moi', 
		'newPassword' => 'Saisir le mot de passe', 
		'confirmNewPassword' => 'Confirmer le mot de passe', 
		'code' => 'Code OTP'
	], 
	'placeholders' => [
		'email' => 'Saisir l\'adresse e-mail ici...', 
		'username' => 'Saisir le nom d\'utilisateur ici...', 
		'password' => 'Saisir le mot de passe ici...', 
		'newPassword' => 'Saisir le mot de passe ici...', 
		'confirmNewPassword' => 'Confirmer le mot de passe', 
		'code' => 'Saisir le code OTP ici...', 
	], 
	'buttons' => [
		'login' => 'Se connecter', 
		'resetPassword' => 'Réinitialiser le mot de passe', 
		'setPassword' => 'Définir le mot de passe', 
		'verify' => 'Vérifier le code OTP'

	], 
	'links' => [
		'login' => 'Se connecter', 
		'resetPassword' => 'Réinitialiser le mot de passe'
	], 
	'errors' => [
	    'passwordFormatNotValid' => 'Le mot de passe ne répond pas aux exigences de sécurité.',
	],
	'audits' => [
	    'loginRefused'      => 'Tentative de connexion avec un compte inexistant',
	    'loginBlocked'      => 'Accès refusé en raison de la limite de tentatives',
	    'loginFailed'       => 'Échec de la tentative de connexion %s %s',
	    '2faRequired'       => 'Code de vérification 2FA requis %s %s %s',
	    'loginSuccess'      => 'Connexion réussie %s %s',
	    'resetPasswordAuth' => 'Réinitialisation du mot de passe %s %s',
	    'setPassword'       => 'Définition du mot de passe %s %s',
	    'verifyFailed'      => 'Tentative de connexion avec un compte inexistant',
	    '2faBlocked'        => 'Blocage 2FA %s %s',
	    '2faFailed'         => 'Code 2FA incorrect ou expiré %s %s',
	],
	'messages' => [
		'goodbye' => 'Au revoir %s %s.', 
		'welcome' => 'Bonjour %s %s.', 
		'loginFailed' => 'Tentative de connexion échouée.', 
	    'loginNeeded' => 'Veuillez vous connecter pour accéder.', 
	    'resetPasswordFailed' => 'Erreur lors de la réinitialisation du mot de passe.', 
		'setPasswordSuccess' => 'Mot de passe défini. <a class="fw-bold text-white" href="' . base_url('auth/login') . '"><i class="fa-solid fa-right-to-bracket"></i> Se connecter.</a>', 
		'setPasswordFailed' => 'Erreur lors de la définition du mot de passe.', 
		'setPasswordError' => 'Erreurs lors de la définition du mot de passe.', 
		'checkAuthError' => 'Code d\'authentification invalide ou expiré.', 
		'tooManyAttempts' => 'Limite de tentatives atteinte. Réessayez plus tard.',
	    'currentSessionOn' => 'Session de %s %s toujours en cours.', 
		'validationErrors' => 'Erreurs de validation.', 
		'resetPasswordSuccess' => 'Si l\'adresse appartient à un compte actif, vous recevrez un e-mail contenant les instructions de réinitialisation.',
		'resetPasswordSuccessNoEmail' => 'Processus de réinitialisation du mot de passe configuré, mais l\'e-mail n\'a pas pu être envoyé. Veuillez contacter l\'utilisateur.', 
		'expiredCode' => 'Code OTP expiré.',
		'wrongCode' => 'Code OTP incorrect.', 
		'twoFactorEmailSendError' => 'Impossible d’envoyer le code de vérification par e-mail. Veuillez réessayer dans quelques instants.',
	]
];

$language['titles']['register'] = 'Inscription';
$language['buttons']['register'] = 'S\'inscrire';
$language['messages']['registerSuccess'] = 'Si cette adresse permet une inscription, vous recevrez un lien pour definir votre mot de passe.';
$language['messages']['registerError'] = 'Echec de l\'inscription. Veuillez reessayer plus tard.';
$language['email']['registerSubject'] = 'Activez votre compte';
$language['messages']['sessionExpired'] = 'Votre session a expiré. Veuillez vous reconnecter.';
$language['messages']['verifyError'] = 'Impossible de terminer la vérification. Veuillez réessayer.';
$language['messages']['logoutError'] = 'Une erreur est survenue lors de la déconnexion.';
$language['links']['accessOrRegister'] = 'Se connecter ou s\'inscrire';
$language['titles']['restore'] = 'Restaurer le compte';
$language['buttons']['restoreAccount'] = 'Annuler la demande de suppression et se connecter';
$language['messages']['confirmAccountRestore'] = 'Votre compte est en attente de suppression. Confirmez pour annuler la demande et vous connecter.';
$language['audits']['removalCancelled'] = 'L’utilisateur a annulé sa demande de suppression lors de la connexion.';
$language['links']['returnToLogin'] = 'Retour à la connexion';
return $language;
