<?php declare(strict_types = 1); 

$language = [
	'titles' => [
		'index' => 'Profil', 
		'general' => 'Données générales', 
		'edit' => 'Mettre à jour', 
		'permissions' => 'Autorisations', 
		'images' => 'Images', 
		'tokens' => 'Jetons', 
		'resetPassword' => 'Réinitialiser le mot de passe', 
		'security' => 'Sécurité', 
		'twoFactor' => 'Authentification à 2 facteurs', 
		'totpSetupTitle' => 'Configurer l\'application d\'authentification',
	],
	'leftMenu' => [
		'general' => 'Données générales', 
		'edit' => 'Mettre à jour', 
		'permissions' => 'Autorisations', 
		'images' => 'Images', 
		'tokens' => 'Jetons', 
		'resetPassword' => 'Réinitialiser le mot de passe', 
		'security' => 'Sécurité'
	],
	'labels' => [
		'firstname' => 'Prénom', 
		'lastname' => 'Nom', 
		'email' => 'E-mail', 
		'phone' => 'Téléphone', 
		'assigned' => 'Assigné', 
		'notAssigned' => 'Non assigné', 
		'note' => 'Notes complémentaires', 
		'operatingSystem' => 'Système d\'exploitation', 
		'browser' => 'Navigateur web', 
		'ipAddress' => 'Adresse IP', 
		'createdToken' => 'Date de début', 
		'expiredToken' => 'Date d\'expiration', 
		'typeToken' => 'Type', 
		'session' => 'Session',
        'activation' => 'Activation', 
        'methodNoneTitle' => 'Aucun', 
        'methodEmailTitle' => 'E-mail', 
        'methodTotpTitle' => 'Application d\'authentification', 
        'methodNoneDesc'  => 'Désactive la vérification en deux étapes. L\'accès se fera en saisissant uniquement l\'e-mail et le mot de passe.',
        'methodEmailDesc' => 'Recevez un code de sécurité à usage unique directement sur votre boîte e-mail professionnelle.',
        'methodTotpDesc'  => 'Scannez le code QR avec une application comme Google Authenticator, Authy ou Microsoft Authenticator pour générer des codes temporaires instantanés.',
        'totpSetupDesc' => 'Scannez le code QR avec votre application d\'authentification (ex. Google Authenticator) ou saisissez la clé manuellement pour commencer à recevoir les codes de sécurité.',
        'totpSecretKey'      => 'Clé secrète (configuration manuelle)',
		'enterOtpCode' => 'Saisir le code',
		'rememberMe' => 'Se souvenir de moi', 
		'currentDevice' => 'Session actuelle',
		'mobile' => 'Appareil mobile',
		'robot' => 'Robot',
		'currentPassword' => 'Mot de passe actuel',
		'currentPasswordEmailHelp' => 'Requis uniquement lors du changement d\'adresse e-mail.'
	], 
	'placeholders' => [
		'firstname' => 'Saisir le prénom...', 
		'lastname' => 'Saisir le nom...', 
		'email' => 'Saisir l\'e-mail...', 
		'phone' => 'Saisir le téléphone...', 
		'note' => 'Saisir des notes complémentaires...',
		'currentPassword' => 'Saisir le mot de passe actuel...',
	], 
	'buttons' => [
		'sendData' => 'Enregistrer les données', 
		'refreshData' => 'Recharger les données', 
		'reload' => 'Recharger le panneau', 
		'resetPassword' => 'Procéder à la réinitialisation du mot de passe',
		'verifyAndActivate' => 'Vérifier et activer', 
		'undoClose' => 'Fermer la configuration et quitter', 
		'delete' => 'Supprimer',
		'showPassword' => 'Afficher le mot de passe'
	], 
	'errors' => [
		'id' => 'ID non conforme.',
		'safeText' => 'Caractères non autorisés.',
		'currentPasswordRequired' => 'Saisissez votre mot de passe actuel.',
		'currentPasswordInvalid' => 'Le mot de passe actuel est incorrect.',
	], 
	'audits' => [
	    'updateData'     => 'Mise à jour des données générales %s %s',
	    'deleteToken'    => 'Suppression du jeton %s %s',
	    'resetPassword'  => 'Réinitialisation du mot de passe %s %s',
	    'activateMethod' => 'Configuration de la méthode 2FA %s %s',
	    'activateTotp'   => 'Configuration de la méthode 2FA %s %s',
	],
	'messages' => [
		'areYouSureStartingReset' => 'Êtes-vous sûr de vouloir commencer le processus de réinitialisation du mot de passe ?', 
		'noDataChanged' => 'Aucune modification n\'a été effectuée.', 
		'editSuccess' => 'Utilisateur %s %s mis à jour avec succès.', 
		'editError' => 'La mise à jour de l\'utilisateur a échoué.', 
		'resetPasswordSuccessNoEmail' => 'Le mot de passe de l\'utilisateur %s %s a été réinitialisé avec succès, mais l\'e-mail n\'a pas pu être envoyé. Veuillez contacter l\'utilisateur.', 
		'resetPasswordSuccess' => 'Le mot de passe de l\'utilisateur %s %s a été réinitialisé avec succès.', 
		'resetPasswordError' => 'La réinitialisation du mot de passe de l\'utilisateur a échoué.', 
		'validateToastErrors' => '%s',
		'validationErrors' => 'Erreurs de validation.', 
		'areYouSureRefreshData' => 'Êtes-vous sûr de vouloir recharger les données ?', 
		'areYouSureDeleteToken' => 'Êtes-vous sûr de vouloir supprimer le jeton de <b>%s %s</b> ?', 
		'noTokensFound' => 'Aucun jeton trouvé.', 
		'deleteTokenSuccess' => 'Le jeton de %s %s a été supprimé avec succès.', 
		'deleteTokenError' => 'La suppression du jeton a échoué.', 
		'expiringDate' => '<div class="pb-3">Vous avez demandé la réinitialisation du mot de passe.</div><div class="pb-3">Veuillez vérifier vos e-mails et terminer l\'opération.</div>Vous avez jusqu\'au %s',
		'methodNotValid' => 'Méthode non valide ou non prise en charge.', 
		'updateSecuritySettingsError' => 'Impossible de mettre à jour les paramètres de sécurité.', 
		'updateSecuritySettingsSuccess' => 'Paramètres de sécurité mis à jour avec succès.', 
		'configurationInitializeError' => 'Impossible d\'initialiser la configuration TOTP.', 
		'noConfigurationSession' => 'Aucune session de configuration TOTP active ou expirée.', 
		'wrongCode' => 'Le code saisi n\'est pas valide.', 
		'missingCode' => 'Veuillez saisir le code.', 
		'totpActivationNotPossible' => 'Impossible d\'activer l\'authentification TOTP.', 
		'totpConfigurationSuccess' => 'L\'application d\'authentification a été configurée avec succès.', 
		'areYouSureChangeMethod' => 'Êtes-vous sûr de vouloir changer la méthode du deuxième facteur d\'authentification ?', 
		'cannotDeleteCurrentToken' => 'Impossible de supprimer la session actuelle.',
		'tooManyResetRequests' => 'Trop de demandes de réinitialisation. Réessayez plus tard.',
		'tooManyAttempts' => 'Trop de tentatives. Réessayez plus tard.'
	]
];

$language['titles']['index'] = 'Mon compte';
$language['labels']['session'] = 'Session';
$language['labels']['activation'] = 'Activation';
$language['labels']['rememberMe'] = 'Se souvenir de moi';
$language['labels']['unknown'] = 'Inconnu';
$language['labels']['id'] = 'ID';
$language['titles']['deleteAccount'] = 'Supprimer le compte';
$language['leftMenu']['deleteAccount'] = 'Supprimer le compte';
$language['buttons']['deleteAccount'] = 'Supprimer le compte';
$language['messages']['removalDescription'] = 'La demande ferme immédiatement toutes vos sessions. Dans un délai de %d jours, vous pouvez vous reconnecter et confirmer l’annulation de la demande. Passé ce délai, le compte sera en attente de suppression définitive.';
$language['messages']['removalEmail'] = 'Votre adresse e-mail reste inchangée et réservée jusqu’à la suppression définitive du compte.';
$language['messages']['confirmRemoval'] = 'Demander la suppression de votre compte ? Toutes vos sessions seront fermées immédiatement. Vous pouvez annuler la demande en vous reconnectant sous %d jours.';
$language['messages']['removalRequested'] = 'Suppression du compte demandée. Tous les accès ont été fermés.';
$language['messages']['removalUnavailable'] = 'Cette demande est indisponible pour ce compte. Reconnectez-vous et vérifiez l’état du profil.';
$language['messages']['removalError'] = 'Impossible de demander la suppression du compte. Réessayez.';
$language['audits']['removalRequested'] = 'Demande personnelle de suppression du compte.';
return $language;
