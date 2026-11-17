<?php declare(strict_types = 1);

return [
    'interface' => [
        'showPassword' => 'Passwort anzeigen',
        'hidePassword' => 'Passwort ausblenden',
        'themes' => 'Designs',
        'changeError' => 'Die Einstellung konnte nicht geändert werden. Bitte erneut versuchen.',
    ],
    'menu' => [
        'topRight' => [
            'admins' => 'Administratoren',
            'groups' => 'Gruppen',
            'audits' => 'Aktivitätsprotokoll', 
            'tokens' => 'Tokens', 
            'logs' => 'Zugriffe', 
            'account' => 'Profil',
            'logout' => 'Abmelden'
        ],
        'bottomLeft' => [
            'dashboard' => 'Panels',
        ],
        'bottomRight' => [
            'settings' => 'Einstellungen',
            'tools' => 'Werkzeuge', 
        ],
    ],
    'menuCounts' => [
        'active' => 'Aktiv',
        'trashed' => 'Gelöscht',
        'suspended' => 'Deaktiviert',
    ],
    'links' => [
        'selectAll' => 'Alle auswählen'
    ], 
    'labels' => [
        'select' => 'Auswählen'
    ], 
    'formats' => [
        'conversationalDate' => "EEEE, d. MMMM yyyy 'um' HH:mm:ss",
    ], 
    'buttons' => [
        'modules' => 'Module',
        'services' => 'Dienste',
        'backToTop' => 'Nach oben', 
        'expandScreen' => 'Vollbild aktivieren',
        'reduceScreen' => 'Vollbild beenden',
        'options' => 'Optionen', 
        'undo' => 'Abbrechen', 
        'export' => 'Exportieren', 
        'yes' => 'Ja', 
        'no' => 'Nein', 
        'close' => 'Schließen', 
        'exportPdf' => 'PDF exportieren', 
        'remove' => 'Entfernen', 
    ],
    'errors' => [
        'err403' => 'Zugriff verweigert. Sie sind für diesen Vorgang nicht berechtigt.',
        'err404' => 'Die angeforderte Ressource wurde nicht gefunden oder entfernt.',
        'err500' => 'Ein kritischer Fehler ist bei der Verarbeitung der Anfrage aufgetreten.',
        'err504' => 'Der Server hat nicht rechtzeitig geantwortet. Prüfen Sie den Vorgangsstatus, bevor Sie es erneut versuchen.',
        'errNetwork' => 'Die Kommunikation mit dem Server ist nicht möglich. Prüfen Sie die Verbindung oder versuchen Sie es später erneut.',
        'errResponse' => 'Der Server hat eine ungültige Antwort gesendet. Der Vorgang wurde möglicherweise bereits ausgeführt.',
        'errClient' => 'Ein Fehler in der Oberfläche ist aufgetreten. Prüfen Sie den Vorgangsstatus, bevor Sie es erneut versuchen.',
        'errRequest' => 'Die Anfrage konnte nicht gestartet werden.',
        'errCsrf' => 'Die Sicherheitsprüfung ist fehlgeschlagen. Laden Sie die Seite neu, bevor Sie es erneut versuchen.',
        'errHttp' => 'Der Server konnte die Anfrage nicht abschließen.',
        'fullscreenError' => 'Der Vollbildmodus konnte nicht geändert werden.',
    ], 
    'modals' => [
        'exportPdfTitle' => 'PDF-Exporteinstellungen', 
        'exportPdfOrientation' => 'Ausrichtung', 
        'exportPdfOrientationVert' => 'Vertikal', 
        'exportPdfOrientationHoriz' => 'Horizontal', 
        'exportPdfFormat' => 'Format', 
        'exportPdfMargin' => 'Ränder', 
        'exportPdfMarginSup' => 'Oben', 
        'exportPdfMarginRgt' => 'Rechts', 
        'exportPdfMarginInf' => 'Unten', 
        'exportPdfMarginLft' => 'Links', 
        'exportPdfCompression' => 'Komprimierung', 
        'exportPdfImgQuality' => 'Bildqualität', 
        'globalTitle' => 'Bestätigung erforderlich'
    ], 
    'pagination' => [
        'messageLeft' => 'Seite %d von %d',
        'messageRight' => 'Von %d bis %d von %d',
        'first' => 'Erste',
        'last' => 'Letzte',
        'next' => 'Nächste',
        'previous' => 'Vorherige',
    ],
    'audits' => [
        'importData' => 'Datenimport in Tabelle %s. %d Datensätze eingefügt, %d Datensätze aktualisiert.',
    ],
    'messages' => [
        'confirmSensitiveAction' => 'Bestätigen Sie diesen Vorgang mit Ihrem Kontopasswort.',
        'currentPasswordInfo' => 'Geben Sie Ihr Kontopasswort ein, um diesen Vorgang zu bestätigen.',
        'getDataError' => 'Fehler beim Abrufen der Liste.',
        'getUUIDError' => 'Fehler beim Abrufen der Details.', 
        'UUIDNotFound' => 'Datensatz nicht gefunden.', 
        'permissionDenied' => 'Zugriff verweigert.',
        'unsavedChanges' => 'Es gibt nicht gespeicherte Änderungen. Möchten Sie die Seite verlassen, ohne zu speichern?'
    ]
];
