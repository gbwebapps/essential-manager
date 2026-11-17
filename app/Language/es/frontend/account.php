<?php declare(strict_types = 1);

$language = [
	'titles' => [
		'index' => 'Perfil',
		'general' => 'Datos generales',
		'edit' => 'Actualizar',
		'permissions' => 'Permisos',
		'images' => 'Imágenes',
		'tokens' => 'Tokens',
		'resetPassword' => 'Restablecer contraseña',
		'security' => 'Seguridad',
		'twoFactor' => 'Autenticación de 2 factores',
		'totpSetupTitle' => 'Configurar la aplicación de autenticación',
	],
	'leftMenu' => [
		'general' => 'Datos generales',
		'edit' => 'Actualizar',
		'permissions' => 'Permisos',
		'images' => 'Imágenes',
		'tokens' => 'Tokens',
		'resetPassword' => 'Restablecer contraseña',
		'security' => 'Seguridad'
	],
	'labels' => [
		'firstname' => 'Nombre',
		'lastname' => 'Apellido',
		'email' => 'Correo electrónico',
		'phone' => 'Teléfono',
		'assigned' => 'Asignado',
		'notAssigned' => 'No asignado',
		'note' => 'Notas adicionales',
		'operatingSystem' => 'Sistema operativo',
		'browser' => 'Navegador web',
		'ipAddress' => 'Dirección IP',
		'createdToken' => 'Fecha de inicio',
		'expiredToken' => 'Fecha de caducidad',
		'typeToken' => 'Tipo',
		'session' => 'Sesión',
        'activation' => 'Activación',
        'methodNoneTitle' => 'Ninguno',
        'methodEmailTitle' => 'Correo electrónico',
        'methodTotpTitle' => 'Aplicación de autenticación',
        'methodNoneDesc'  => 'Desactiva la verificación en dos pasos. El acceso se realizará introduciendo únicamente el correo electrónico y la contraseña.',
        'methodEmailDesc' => 'Recibe un código de seguridad de un solo uso directamente en tu bandeja de entrada de correo electrónico corporativo.',
        'methodTotpDesc'  => 'Escanea el código QR con aplicaciones como Google Authenticator, Authy o Microsoft Authenticator para generar códigos temporales instantáneos.',
        'totpSetupDesc' => 'Escanea el código QR con tu aplicación de autenticación (ej. Google Authenticator) o introduce la clave manualmente para empezar a recibir códigos de seguridad.',
        'totpSecretKey'      => 'Clave secreta (configuración manual)',
		'enterOtpCode' => 'Introduce el código',
		'rememberMe' => 'Recuérdame',
		'currentDevice' => 'Sesión actual',
		'mobile' => 'Dispositivo móvil',
		'robot' => 'Robot',
		'currentPassword' => 'Contraseña actual',
		'currentPasswordEmailHelp' => 'Solo es necesaria al cambiar el correo electrónico.'
	],
	'placeholders' => [
		'firstname' => 'Introduce el nombre...',
		'lastname' => 'Introduce el apellido...',
		'email' => 'Introduce el correo electrónico...',
		'phone' => 'Introduce el teléfono...',
		'note' => 'Introduce notas adicionales...',
		'currentPassword' => 'Introduce la contraseña actual...',
	],
	'buttons' => [
		'sendData' => 'Guardar datos',
		'refreshData' => 'Actualizar datos',
		'reload' => 'Recargar panel',
		'resetPassword' => 'Proceder al restablecimiento de contraseña',
		'verifyAndActivate' => 'Verificar y activar',
		'undoClose' => 'Cerrar configuración y salir',
		'delete' => 'Eliminar',
		'showPassword' => 'Mostrar contraseña'
	],
	'errors' => [
		'id' => 'ID no conforme.',
		'safeText' => 'Caracteres no permitidos.',
		'currentPasswordRequired' => 'Introduce tu contraseña actual.',
		'currentPasswordInvalid' => 'La contraseña actual no es correcta.',
	],
	'audits' => [
	    'updateData'     => 'Actualización de datos generales %s %s',
	    'deleteToken'    => 'Eliminación de token %s %s',
	    'resetPassword'  => 'Restablecimiento de contraseña %s %s',
	    'activateMethod' => 'Configuración del método 2FA %s %s',
	    'activateTotp'   => 'Configuración del método 2FA %s %s',
	],
	'messages' => [
		'areYouSureStartingReset' => '¿Estás seguro de que deseas iniciar el proceso de restablecimiento de contraseña?',
		'noDataChanged' => 'No se han realizado cambios.',
		'editSuccess' => 'Usuario %s %s actualizado con éxito.',
		'editError' => 'La actualización del usuario no se ha podido completar.',
		'resetPasswordSuccessNoEmail' => 'Contraseña del usuario %s %s restablecida con éxito, pero el correo no ha sido enviado. Contacte con el usuario.',
		'resetPasswordSuccess' => 'Contraseña del usuario %s %s restablecida con éxito.',
		'resetPasswordError' => 'El restablecimiento de contraseña del usuario no se ha podido completar.',
		'validateToastErrors' => '%s',
		'validationErrors' => 'Errores de validación.',
		'areYouSureRefreshData' => '¿Estás seguro de que deseas actualizar los datos?',
		'areYouSureDeleteToken' => '¿Estás seguro de eliminar el token de <b>%s %s</b>?',
		'noTokensFound' => 'No hay tokens disponibles.',
		'deleteTokenSuccess' => 'El token de %s %s se ha eliminado con éxito.',
		'deleteTokenError' => 'La eliminación del token no se ha podido completar',
		'expiringDate' => '<div class="pb-3">Has solicitado el restablecimiento de la contraseña.</div><div class="pb-3">Verifica tu correo electrónico y completa la operación.</div>Tienes hasta el %s',
		'methodNotValid' => 'Método no válido o no soportado.',
		'updateSecuritySettingsError' => 'Imposible actualizar la configuración de seguridad.',
		'updateSecuritySettingsSuccess' => 'Configuración de seguridad actualizada con éxito.',
		'configurationInitializeError' => 'Imposible inicializar la configuración TOTP.',
		'noConfigurationSession' => 'Ninguna sesión de configuración TOTP activa o caducada.',
		'wrongCode' => 'El código introducido no es válido.',
		'missingCode' => 'Introduce el código.',
		'totpActivationNotPossible' => 'Imposible activar la autenticación TOTP.',
		'totpConfigurationSuccess' => 'La aplicación de autenticación se ha configurado con éxito.',
		'areYouSureChangeMethod' => '¿Estás seguro de que deseas cambiar el método del segundo factor de autenticación?',
		'cannotDeleteCurrentToken' => 'No es posible eliminar la sesión actual.',
		'tooManyResetRequests' => 'Demasiadas solicitudes de restablecimiento. Inténtalo más tarde.',
		'tooManyAttempts' => 'Demasiados intentos. Inténtalo más tarde.'
	]
];

$language['titles']['index'] = 'Mi cuenta';
$language['labels']['session'] = 'Sesión';
$language['labels']['activation'] = 'Activación';
$language['labels']['rememberMe'] = 'Recuérdame';
$language['labels']['unknown'] = 'Desconocido';
$language['labels']['id'] = 'ID';
$language['titles']['deleteAccount'] = 'Eliminar cuenta';
$language['leftMenu']['deleteAccount'] = 'Eliminar cuenta';
$language['buttons']['deleteAccount'] = 'Eliminar cuenta';
$language['messages']['removalDescription'] = 'La solicitud cierra inmediatamente todas tus sesiones. En un plazo de %d días puedes volver a iniciar sesión y confirmar la cancelación de la solicitud. Pasado el plazo, la cuenta quedará pendiente de eliminación definitiva.';
$language['messages']['removalEmail'] = 'Tu dirección de correo no cambia y permanece reservada hasta la eliminación definitiva de la cuenta.';
$language['messages']['confirmRemoval'] = '¿Solicitar la eliminación de tu cuenta? Se cerrarán todas tus sesiones de inmediato. Puedes cancelar la solicitud volviendo a iniciar sesión en un plazo de %d días.';
$language['messages']['removalRequested'] = 'Eliminación de cuenta solicitada. Se han cerrado todos los accesos.';
$language['messages']['removalUnavailable'] = 'La solicitud no está disponible para esta cuenta. Vuelve a iniciar sesión y comprueba el estado del perfil.';
$language['messages']['removalError'] = 'No se pudo solicitar la eliminación de la cuenta. Inténtalo de nuevo.';
$language['audits']['removalRequested'] = 'Solicitud personal de eliminación de cuenta.';
return $language;
