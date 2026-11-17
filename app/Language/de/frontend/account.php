<?php declare(strict_types = 1); 

$language = [
	'titles' => [
		'index' => 'Profil', 
		'general' => 'Allgemeine Daten', 
		'edit' => 'Aktualisieren', 
		'permissions' => 'Berechtigungen', 
		'images' => 'Bilder', 
		'tokens' => 'Tokens', 
		'resetPassword' => 'Passwort zurücksetzen', 
		'security' => 'Sicherheit', 
		'twoFactor' => 'Zwei-Faktor-Authentifizierung', 
		'totpSetupTitle' => 'Authentifizierungs-App konfigurieren',
	],
	'leftMenu' => [
		'general' => 'Allgemeine Daten', 
		'edit' => 'Aktualisieren', 
		'permissions' => 'Berechtigungen', 
		'images' => 'Bilder', 
		'tokens' => 'Tokens', 
		'resetPassword' => 'Passwort zurücksetzen', 
		'security' => 'Sicherheit'
	],
	'labels' => [
		'firstname' => 'Vorname', 
		'lastname' => 'Nachname', 
		'email' => 'E-Mail', 
		'phone' => 'Telefon', 
		'assigned' => 'Zugewiesen', 
		'notAssigned' => 'Nicht zugewiesen', 
		'note' => 'Zusätzliche Notizen', 
		'operatingSystem' => 'Betriebssystem', 
		'browser' => 'Webbrowser', 
		'ipAddress' => 'IP-Adresse', 
		'createdToken' => 'Startdatum', 
		'expiredToken' => 'Ablaufdatum', 
		'typeToken' => 'Typ', 
		'session' => 'Sitzung',
        'activation' => 'Aktivierung', 
        'methodNoneTitle' => 'Keine', 
        'methodEmailTitle' => 'E-Mail', 
        'methodTotpTitle' => 'Authenticator-App', 
        'methodNoneDesc'  => 'Deaktiviert die Bestätigung in zwei Schritten. Der Zugriff erfolgt nur durch Eingabe von E-Mail und Passwort.',
        'methodEmailDesc' => 'Erhalten Sie einen einmaligen Sicherheitscode direkt in Ihr geschäftliches E-Mail-Postfach.',
        'methodTotpDesc'  => 'Scannen Sie den QR-Code mit Apps wie Google Authenticator, Authy oder Microsoft Authenticator, um sofort temporäre Codes zu generieren.',
        'totpSetupDesc' => 'Scannen Sie den QR-Code mit Ihrer Authentifizierungs-App (z. B. Google Authenticator) oder geben Sie den Schlüssel manuell ein, um Sicherheitscodes zu erhalten.',
        'totpSecretKey'      => 'Geheimschlüssel (manuelle Konfiguration)',
		'enterOtpCode' => 'Geben Sie den Code ein',
		'rememberMe' => 'Angemeldet bleiben', 
		'currentDevice' => 'Aktuelle Sitzung',
		'mobile' => 'Mobilgerät',
		'robot' => 'Robot',
		'currentPassword' => 'Aktuelles Passwort',
		'currentPasswordEmailHelp' => 'Nur bei Änderung der E-Mail-Adresse erforderlich.'
	], 
	'placeholders' => [
		'firstname' => 'Vorname eingeben...', 
		'lastname' => 'Nachname eingeben...', 
		'email' => 'E-Mail eingeben...', 
		'phone' => 'Telefonnummer eingeben...', 
		'note' => 'Zusätzliche Notizen eingeben...',
		'currentPassword' => 'Aktuelles Passwort eingeben...',
	], 
	'buttons' => [
		'sendData' => 'Daten speichern', 
		'refreshData' => 'Daten neu laden', 
		'reload' => 'Panel neu laden', 
		'resetPassword' => 'Mit dem Passwort-Reset fortfahren',
		'verifyAndActivate' => 'Überprüfen und aktivieren', 
		'undoClose' => 'Konfiguration schließen und beenden', 
		'delete' => 'Löschen',
		'showPassword' => 'Passwort anzeigen'
	], 
	'errors' => [
		'id' => 'Ungültige ID.',
		'safeText' => 'Nicht zulässige Zeichen.',
		'currentPasswordRequired' => 'Geben Sie Ihr aktuelles Passwort ein.',
		'currentPasswordInvalid' => 'Das aktuelle Passwort ist falsch.',
	], 
	'audits' => [
	    'updateData'     => 'Allgemeine Datenaktualisierung %s %s',
	    'deleteToken'    => 'Token-Löschung %s %s',
	    'resetPassword'  => 'Passwort zurücksetzen %s %s',
	    'activateMethod' => '2FA-Methodeneinstellung %s %s',
	    'activateTotp'   => '2FA-Methodeneinstellung %s %s',
	],
	'messages' => [
		'areYouSureStartingReset' => 'Sind Sie sicher, dass Sie den Passwort-Reset-Prozess starten möchten?', 
		'noDataChanged' => 'Es wurden keine Änderungen vorgenommen.', 
		'editSuccess' => 'User %s %s erfolgreich aktualisiert.', 
		'editError' => 'Aktualisierung des Users fehlgeschlagen.', 
		'resetPasswordSuccessNoEmail' => 'Passwort für User %s %s erfolgreich zurückgesetzt, aber die E-Mail konnte nicht gesendet werden. Bitte kontaktieren Sie den User.', 
		'resetPasswordSuccess' => 'Passwort für User %s %s erfolgreich zurückgesetzt.', 
		'resetPasswordError' => 'Passwort-Reset des Users fehlgeschlagen.', 
		'validateToastErrors' => '%s',
		'validationErrors' => 'Validierungsfehler.', 
		'areYouSureRefreshData' => 'Sind Sie sicher, dass Sie die Daten neu laden möchten?', 
		'areYouSureDeleteToken' => 'Sind Sie sicher, dass Sie das Token von <b>%s %s</b> löschen möchten?', 
		'noTokensFound' => 'Keine Tokens vorhanden.', 
		'deleteTokenSuccess' => 'Das Token von %s %s wurde erfolgreich gelöscht.', 
		'deleteTokenError' => 'Löschen des Tokens fehlgeschlagen.', 
		'expiringDate' => '<div class="pb-3">Sie haben das Zurücksetzen des Passworts angefordert.</div><div class="pb-3">Bitte überprüfen Sie Ihre E-Mail und schließen Sie den Vorgang ab.</div>Sie haben Zeit bis %s',
		'methodNotValid' => 'Ungültige oder nicht unterstützte Methode.', 
		'updateSecuritySettingsError' => 'Sicherheitseinstellungen konnten nicht aktualisiert werden.', 
		'updateSecuritySettingsSuccess' => 'Sicherheitseinstellungen erfolgreich aktualisiert.', 
		'configurationInitializeError' => 'TOTP-Konfiguration konnte nicht initialisiert werden.', 
		'noConfigurationSession' => 'Keine aktive oder abgelaufene TOTP-Konfigurationssitzung.', 
		'wrongCode' => 'Der eingegebene Code ist ungültig.', 
		'missingCode' => 'Bitte geben Sie den Code ein.', 
		'totpActivationNotPossible' => 'TOTP-Authentifizierung konnte nicht aktiviert werden.', 
		'totpConfigurationSuccess' => 'Die Authentifizierungs-App wurde erfolgreich konfiguriert.', 
		'areYouSureChangeMethod' => 'Sind Sie sicher, dass Sie die Methode für den zweiten Authentifizierungsfaktor ändern möchten?', 
		'cannotDeleteCurrentToken' => 'Die aktuelle Sitzung kann nicht gelöscht werden.',
		'tooManyResetRequests' => 'Zu viele Anfragen zum Zurücksetzen des Passworts. Versuchen Sie es später erneut.',
		'tooManyAttempts' => 'Zu viele Versuche. Versuchen Sie es später erneut.'
	]
];

$language['titles']['index'] = 'Mein Konto';
$language['labels']['session'] = 'Sitzung';
$language['labels']['activation'] = 'Aktivierung';
$language['labels']['rememberMe'] = 'Angemeldet bleiben';
$language['labels']['unknown'] = 'Unbekannt';
$language['labels']['id'] = 'ID';
$language['titles']['deleteAccount'] = 'Konto löschen';
$language['leftMenu']['deleteAccount'] = 'Konto löschen';
$language['buttons']['deleteAccount'] = 'Konto löschen';
$language['messages']['removalDescription'] = 'Der Antrag beendet sofort alle Anmeldungen. Innerhalb von %d Tagen können Sie sich erneut anmelden und die Rücknahme bestätigen. Nach Ablauf der Frist steht das Konto zur endgültigen Löschung an.';
$language['messages']['removalEmail'] = 'Ihre E-Mail-Adresse bleibt unverändert und reserviert, bis das Konto endgültig gelöscht wird.';
$language['messages']['confirmRemoval'] = 'Möchten Sie die Löschung Ihres Kontos beantragen? Sie werden sofort abgemeldet. Innerhalb von %d Tagen können Sie den Antrag durch erneute Anmeldung zurücknehmen.';
$language['messages']['removalRequested'] = 'Kontolöschung angefordert. Alle Anmeldungen wurden beendet.';
$language['messages']['removalUnavailable'] = 'Diese Anfrage ist für das Konto nicht verfügbar. Melden Sie sich erneut an und prüfen Sie den Profilstatus.';
$language['messages']['removalError'] = 'Die Kontolöschung konnte nicht angefordert werden. Bitte versuchen Sie es erneut.';
$language['audits']['removalRequested'] = 'Persönliche Anfrage zur Kontolöschung.';
return $language;
