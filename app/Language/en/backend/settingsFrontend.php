<?php declare(strict_types = 1);

$language = [
    'titles' => [
        'index' => 'Frontend settings',
        'section' => 'General',
        'general' => 'Public website',
    ],
    'labels' => [
        'siteName' => 'Site name',
        'title' => 'Home page title',
        'description' => 'Home page description',
        'language' => 'Language',
        'timezone' => 'Time zone',
    ],
    'messages' => [
        'englishFallback' => 'Leave translations blank to use the English text. English title and description are required.',
        'saveSuccess' => 'Settings saved successfully.',
        'saveError' => 'Unable to save settings.',
        'deleteSuccess' => 'Settings restored successfully.',
        'deleteError' => 'Unable to restore settings.',
        'alreadyDefault' => 'Configuration defaults are already active.',
        'validationErrors' => 'Validation errors.',
        'noDataChanged' => 'No settings have changed.',
    ],
    'audits' => [
        'saveSettings' => 'Frontend settings saved.',
        'deleteSettings' => 'Frontend settings restored.',
    ],
];

return $language;
