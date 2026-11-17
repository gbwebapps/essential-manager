<?php declare(strict_types = 1);

return [
    'panels' => [
        'main' => 'Exportation CSV · <span class="text-primary fw-bold">%s</span>',
    ],
    'links' => [
        'export' => 'Exporter en CSV',
    ],
    'labels' => [
        'includedTables' => 'Tables incluses',
        'selectDeselectAll' => 'Tout sélectionner / Tout désélectionner',
        'selectColumns' => 'Sélectionnez les colonnes à inclure dans l\'exportation. La clé primaire sera obligatoirement incluse par le système.',
        'dataElaboration' => 'Préparation des données...',
        'runningExportation' => 'Exportation en cours...',
    ],
    'buttons' => [
        'undo' => 'Annuler',
        'export' => 'Exporter',
        'operationUndo' => 'Annuler l\'opération',
        'exportationStart' => 'Démarrer l\'exportation',
    ],
    'messages' => [
        'validateToastErrors' => '%s',
        'invalidEntity' => 'Nom de table inconnu.',
        'noColumnsSelected' => 'Aucune colonne sélectionnée.',
        'noDataFound' => 'Aucun enregistrement trouvé.',
        'exportSuccess' => '%d enregistrements exportés depuis la table %s.',
        'processedRows' => '%d lignes traitées',
    ],
];