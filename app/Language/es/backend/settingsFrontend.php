<?php declare(strict_types = 1);

$language = [
    'titles' => [
        'index' => 'Configuración del frontend',
        'section' => 'Generales',
        'general' => 'Sitio público',
    ],
    'labels' => [
        'siteName' => 'Nombre del sitio',
        'title' => 'Título de la página de inicio',
        'description' => 'Descripción de la página de inicio',
        'language' => 'Idioma',
        'timezone' => 'Zona horaria',
    ],
    'messages' => [
        'englishFallback' => 'Las traducciones vacías usan el texto en inglés. El título y la descripción en inglés son obligatorios.',
        'saveSuccess' => 'Configuración guardada correctamente.',
        'saveError' => 'No se pudo guardar la configuración.',
        'deleteSuccess' => 'Configuración restaurada correctamente.',
        'deleteError' => 'No se pudo restaurar la configuración.',
        'alreadyDefault' => 'Los valores predeterminados ya están activos.',
        'validationErrors' => 'Errores de validación.',
        'noDataChanged' => 'No se ha modificado ninguna configuración.',
    ],
    'audits' => [
        'saveSettings' => 'Configuración del frontend guardada.',
        'deleteSettings' => 'Configuración del frontend restaurada.',
    ],
];

return $language;
