<?php declare(strict_types = 1);

return [
    'interface' => [
        'showPassword' => 'Afficher le mot de passe',
        'hidePassword' => 'Masquer le mot de passe',
        'themes' => 'Thèmes',
        'changeError' => 'Impossible de modifier la préférence. Réessayez.',
    ],
    'menu' => [
        'topRight' => [
            'admins' => 'Administrateurs',
            'groups' => 'Groupes',
            'audits' => 'Registre d\'activités', 
            'tokens' => 'Jetons', 
            'logs' => 'Accès', 
            'account' => 'Profil',
            'logout' => 'Se déconnecter'
        ],
        'bottomLeft' => [
            'dashboard' => 'Panneaux',
        ],
        'bottomRight' => [
            'settings' => 'Paramètres',
            'tools' => 'Outils', 
        ],
    ],
    'menuCounts' => [
        'active' => 'Actifs',
        'trashed' => 'Supprimés',
        'suspended' => 'Désactivés',
    ],
    'links' => [
        'selectAll' => 'Tout sélectionner'
    ], 
    'labels' => [
        'select' => 'Sélectionner'
    ], 
    'formats' => [
        'conversationalDate' => "EEEE d MMMM yyyy 'à' HH:mm:ss",
    ], 
    'buttons' => [
        'modules' => 'Modules',
        'services' => 'Services',
        'backToTop' => 'Retour en haut', 
        'expandScreen' => 'Agrandir l’écran',
        'reduceScreen' => 'Réduire l’écran',
        'options' => 'Options', 
        'undo' => 'Annuler', 
        'export' => 'Exporter', 
        'yes' => 'Oui', 
        'no' => 'Non', 
        'close' => 'Fermer', 
        'exportPdf' => 'Exporter en PDF', 
        'remove' => 'Retirer', 
    ],
    'errors' => [
        'err403' => 'Accès refusé. Vous ne pouvez pas effectuer cette opération.',
        'err404' => 'La ressource demandée est introuvable ou a été supprimée.',
        'err500' => 'Une erreur critique est survenue lors du traitement de la demande.',
        'err504' => 'Le serveur n’a pas répondu à temps. Vérifiez l’état de l’opération avant de réessayer.',
        'errNetwork' => 'Impossible de communiquer avec le serveur. Vérifiez votre connexion ou réessayez plus tard.',
        'errResponse' => 'Le serveur a renvoyé une réponse non valide. L’opération a peut-être déjà été effectuée.',
        'errClient' => 'Une erreur d’interface est survenue. Vérifiez l’état de l’opération avant de réessayer.',
        'errRequest' => 'La requête n’a pas pu être lancée.',
        'errCsrf' => 'La vérification de sécurité a échoué. Rechargez la page avant de réessayer.',
        'errHttp' => 'Le serveur n’a pas pu traiter la requête.',
        'fullscreenError' => 'Impossible de modifier le mode plein écran.',
    ], 
    'modals' => [
        'exportPdfTitle' => 'Paramètres d\'exportation PDF', 
        'exportPdfOrientation' => 'Orientation', 
        'exportPdfOrientationVert' => 'Verticale', 
        'exportPdfOrientationHoriz' => 'Horizontale', 
        'exportPdfFormat' => 'Format', 
        'exportPdfMargin' => 'Marges', 
        'exportPdfMarginSup' => 'Supérieure', 
        'exportPdfMarginRgt' => 'Droite', 
        'exportPdfMarginInf' => 'Inférieure', 
        'exportPdfMarginLft' => 'Gauche', 
        'exportPdfCompression' => 'Compression', 
        'exportPdfImgQuality' => 'Qualité de l\'image', 
        'globalTitle' => 'Demande de confirmation'
    ], 
    'pagination' => [
        'messageLeft' => 'Page %d sur %d',
        'messageRight' => 'De %d à %d sur %d',
        'first' => 'Premier',
        'last' => 'Dernier',
        'next' => 'Suivant',
        'previous' => 'Précédent',
    ],
    'audits' => [
        'importData' => 'Importation de données dans la table %s. Enregistrements insérés : %d, mis à jour : %d.',
    ],
    'messages' => [
        'confirmSensitiveAction' => 'Confirmez cette opération avec le mot de passe de votre compte.',
        'currentPasswordInfo' => 'Saisissez le mot de passe de votre compte pour confirmer cette opération.',
        'getDataError' => 'Erreur lors de la récupération de la liste.',
        'getUUIDError' => 'Erreur lors de la récupération du détail.', 
        'UUIDNotFound' => 'Enregistrement introuvable.', 
        'permissionDenied' => 'Accès refusé.',
        'unsavedChanges' => 'Des modifications ne sont pas enregistrées. Voulez-vous quitter sans enregistrer ?'
    ]
];
