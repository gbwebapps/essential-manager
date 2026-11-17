<?php declare(strict_types = 1); 

$language = [
	'titles' => [
		'index' => 'Profile', 
		'general' => 'General Data', 
		'edit' => 'Update', 
		'permissions' => 'Permissions', 
		'images' => 'Images', 
		'tokens' => 'Tokens', 
		'resetPassword' => 'Reset Password', 
		'security' => 'Security', 
		'twoFactor' => 'Two-Factor Authentication', 
		'totpSetupTitle' => 'Configure Authenticator App',
	],
	'leftMenu' => [
		'general' => 'General Data', 
		'edit' => 'Update', 
		'permissions' => 'Permissions', 
		'images' => 'Images', 
		'tokens' => 'Tokens', 
		'resetPassword' => 'Reset Password', 
		'security' => 'Security'
	],
	'labels' => [
		'firstname' => 'First Name', 
		'lastname' => 'Last Name', 
		'email' => 'Email', 
		'phone' => 'Phone', 
		'assigned' => 'Assigned', 
		'notAssigned' => 'Not Assigned', 
		'note' => 'Additional Notes', 
		'operatingSystem' => 'Operating System', 
		'browser' => 'Web Browser', 
		'ipAddress' => 'IP Address', 
		'createdToken' => 'Start Date', 
		'expiredToken' => 'Expiration Date', 
		'typeToken' => 'Type', 
		'session' => 'Session',
        'activation' => 'Activation', 
        'methodNoneTitle' => 'None', 
        'methodEmailTitle' => 'Email', 
        'methodTotpTitle' => 'Authenticator App', 
        'methodNoneDesc'  => 'Disable two-step verification. Access will require only email and password.',
        'methodEmailDesc' => 'Receive a one-time security code directly in your corporate email inbox.',
        'methodTotpDesc'  => 'Scan the QR code with an app like Google Authenticator, Authy, or Microsoft Authenticator to generate instant temporary codes.',
        'totpSetupDesc' => 'Scan the QR code with your authenticator app (e.g., Google Authenticator) or enter the key manually to start receiving security codes.',
        'totpSecretKey'      => 'Secret Key (manual configuration)',
		'enterOtpCode' => 'Enter the code',
		'rememberMe' => 'Remember Me', 
		'currentDevice' => 'Current session',
		'mobile' => 'Mobile device',
		'robot' => 'Robot',
		'currentPassword' => 'Current password',
		'currentPasswordEmailHelp' => 'Required only when changing the email address.',
	], 
	'placeholders' => [
		'firstname' => 'Enter first name...', 
		'lastname' => 'Enter last name...', 
		'email' => 'Enter email...', 
		'phone' => 'Enter phone...', 
		'note' => 'Enter additional notes...',
		'currentPassword' => 'Enter your current password...',
	], 
	'buttons' => [
		'sendData' => 'Save Data', 
		'refreshData' => 'Reload Data', 
		'reload' => 'Reload Panel', 
		'resetPassword' => 'Proceed with Password Reset',
		'verifyAndActivate' => 'Verify and Activate', 
		'undoClose' => 'Close Setup and Exit', 
		'delete' => 'Delete',
		'showPassword' => 'Show password'
	], 
	'errors' => [
		'id' => 'Invalid ID.',
		'safeText' => 'Characters not allowed.',
		'currentPasswordRequired' => 'Enter your current password.',
		'currentPasswordInvalid' => 'The current password is incorrect.',
	], 
	'audits' => [
	    'updateData'     => 'General data update %s %s',
	    'deleteToken'    => 'Token deletion %s %s',
	    'resetPassword'  => 'Password reset %s %s',
	    'activateMethod' => '2FA method setup %s %s',
	    'activateTotp'   => '2FA method setup %s %s',
	],
	'messages' => [
		'areYouSureStartingReset' => 'Are you sure you want to start the password reset process?', 
		'noDataChanged' => 'No changes were made.', 
		'editSuccess' => 'User %s %s updated successfully.', 
		'editError' => 'User update failed.', 
		'resetPasswordSuccessNoEmail' => 'User %s %s password reset successfully, but the email was not sent. Please contact the user.', 
		'resetPasswordSuccess' => 'User %s %s password reset successfully.', 
		'resetPasswordError' => 'User password reset failed.', 
		'validateToastErrors' => '%s',
		'validationErrors' => 'Validation errors.', 
		'areYouSureRefreshData' => 'Are you sure you want to reload the data?', 
		'areYouSureDeleteToken' => 'Are you sure you want to delete the token for <b>%s %s</b>?', 
		'noTokensFound' => 'No tokens found.', 
		'deleteTokenSuccess' => 'The token for %s %s was deleted successfully.', 
		'deleteTokenError' => 'Token deletion failed.', 
		'expiringDate' => '<div class="pb-3">You have requested a password reset.</div><div class="pb-3">Check your email and complete the operation.</div>You have until %s',
		'methodNotValid' => 'Invalid or unsupported method.', 
		'updateSecuritySettingsError' => 'Unable to update security settings.', 
		'updateSecuritySettingsSuccess' => 'Security settings updated successfully.', 
		'configurationInitializeError' => 'Unable to initialize TOTP configuration.', 
		'noConfigurationSession' => 'No active or expired TOTP configuration session.', 
		'wrongCode' => 'The entered code is invalid.', 
		'missingCode' => 'Please enter the code.', 
		'totpActivationNotPossible' => 'Unable to activate TOTP authentication.', 
		'totpConfigurationSuccess' => 'The authenticator app was configured successfully.', 
		'areYouSureChangeMethod' => 'Are you sure you want to change the two-factor authentication method?', 
		'cannotDeleteCurrentToken' => 'Cannot delete the current session.',
		'tooManyResetRequests' => 'Too many password reset requests. Try again later.',
		'tooManyAttempts' => 'Too many attempts. Try again later.',
	]
];

$language['titles']['index'] = 'My account';
$language['labels']['session'] = 'Session';
$language['labels']['activation'] = 'Activation';
$language['labels']['rememberMe'] = 'Remember Me';
$language['labels']['unknown'] = 'Unknown';
$language['labels']['id'] = 'ID';
$language['titles']['deleteAccount'] = 'Delete account';
$language['leftMenu']['deleteAccount'] = 'Delete account';
$language['buttons']['deleteAccount'] = 'Delete account';
$language['messages']['removalDescription'] = 'This request immediately closes all your sign-ins. Within %d days, you can sign in again and confirm that you want to cancel the request. After the deadline, the account will be due for permanent deletion.';
$language['messages']['removalEmail'] = 'Your email address remains unchanged and reserved until the account is permanently deleted.';
$language['messages']['confirmRemoval'] = 'Request deletion of your account? You will be signed out immediately. You can cancel the request by signing in again within %d days.';
$language['messages']['removalRequested'] = 'Account deletion requested. All sign-ins have been closed.';
$language['messages']['removalUnavailable'] = 'This request is not available for this account. Sign in again and check the profile status.';
$language['messages']['removalError'] = 'Unable to request account deletion. Please try again.';
$language['audits']['removalRequested'] = 'Personal request to delete the account.';
return $language;
