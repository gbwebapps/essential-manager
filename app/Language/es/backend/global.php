<?php declare(strict_types = 1);

return [
    'interface' => [
        'showPassword' => 'Mostrar contraseña',
        'hidePassword' => 'Ocultar contraseña',
        'themes' => 'Temas',
        'changeError' => 'No se pudo cambiar la preferencia. Inténtalo de nuevo.',
    ],
    'menu' => [
        'topRight' => [
            'admins' => 'Administradores',
            'groups' => 'Grupos',
            'audits' => 'Registro de actividad',
            'tokens' => 'Tokens',
            'logs' => 'Accesos',
            'account' => 'Perfil',
            'logout' => 'Salir'
        ],
        'bottomLeft' => [
            'dashboard' => 'Paneles',
        ],
        'bottomRight' => [
            'settings' => 'Configuración',
            'tools' => 'Herramientas',
        ],
    ],
    'menuCounts' => [
        'active' => 'Activos',
        'trashed' => 'Eliminados',
        'suspended' => 'Deshabilitados',
    ],
    'links' => [
        'selectAll' => 'Seleccionar todos'
    ],
    'labels' => [
        'select' => 'Seleccionar'
    ],
    'formats' => [
        'conversationalDate' => "EEEE d 'de' MMMM 'de' yyyy 'a las' HH:mm:ss",
    ],
    'buttons' => [
        'modules' => 'Módulos',
        'services' => 'Servicios',
        'backToTop' => 'Volver arriba',
        'expandScreen' => 'Ampliar pantalla',
        'reduceScreen' => 'Reducir pantalla',
        'options' => 'Opciones',
        'undo' => 'Cancelar',
        'export' => 'Exportar',
        'yes' => 'Sí',
        'no' => 'No',
        'close' => 'Cerrar',
        'exportPdf' => 'Exportar a PDF',
        'remove' => 'Eliminar',
    ],
    'errors' => [
        'err403' => 'Acceso denegado. No tienes autorización para realizar esta operación.',
        'err404' => 'La recurso solicitado no se ha encontrado o ha sido eliminado.',
        'err500' => 'Se ha producido un error crítico durante el procesamiento de la solicitud.',
        'err504' => 'El servidor no ha respondido a tiempo. Comprueba el estado de la operación antes de volver a intentarlo.',
        'errNetwork' => 'No se puede comunicar con el servidor. Comprueba la conexión o inténtalo más tarde.',
        'errResponse' => 'El servidor ha devuelto una respuesta no válida. Es posible que la operación ya se haya realizado.',
        'errClient' => 'Se ha producido un error en la interfaz. Comprueba el estado de la operación antes de volver a intentarlo.',
        'errRequest' => 'No se ha podido iniciar la solicitud.',
        'errCsrf' => 'La verificación de seguridad ha fallado. Recarga la página antes de volver a intentarlo.',
        'errHttp' => 'El servidor no ha podido completar la solicitud.',
        'fullscreenError' => 'No se puede cambiar el modo de pantalla completa.',
    ],
    'modals' => [
        'exportPdfTitle' => 'Configuración de exportación PDF',
        'exportPdfOrientation' => 'Orientación',
        'exportPdfOrientationVert' => 'Vertical',
        'exportPdfOrientationHoriz' => 'Horizontal',
        'exportPdfFormat' => 'Formato',
        'exportPdfMargin' => 'Márgenes',
        'exportPdfMarginSup' => 'Superior',
        'exportPdfMarginRgt' => 'Derecho',
        'exportPdfMarginInf' => 'Inferior',
        'exportPdfMarginLft' => 'Izquierdo',
        'exportPdfCompression' => 'Compresión',
        'exportPdfImgQuality' => 'Calidad de imagen',
        'globalTitle' => 'Solicitud de confirmación'
    ],
    'pagination' => [
        'messageLeft' => 'Página %d de %d',
        'messageRight' => 'Del %d al %d de %d',
        'first' => 'Primero',
        'last' => 'Último',
        'next' => 'Siguiente',
        'previous' => 'Anterior',
    ],
    'audits' => [
        'importData' => 'Importación de datos en la tabla %s. Registros insertados: %d, registros actualizados: %d.',
    ],
    'messages' => [
        'confirmSensitiveAction' => 'Confirma esta operación con la contraseña de tu cuenta.',
        'currentPasswordInfo' => 'Introduce la contraseña de tu cuenta para confirmar esta operación.',
        'getDataError' => 'Error durante la operación de recuperación de la lista.',
        'getUUIDError' => 'Error durante la operación de recuperación del detalle.',
        'UUIDNotFound' => 'Registro no encontrado.',
        'permissionDenied' => 'Acceso denegado.',
        'unsavedChanges' => 'Hay cambios sin guardar. ¿Quieres salir sin guardar?'
    ]
];
