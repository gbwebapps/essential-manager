<?php declare(strict_types = 1); 

$language = [
	'titles' => [
		'index' => '欢迎', 
		'login' => '登录', 
		'resetPassword' => '重置密码', 
		'setPassword' => '设置密码', 
		'verify' => '验证 OTP'
	],
	'labels' => [
		'email' => '电子邮件', 
		'username' => '用户名', 
		'password' => '密码', 
		'rememberMe' => '记住我', 
		'newPassword' => '输入密码', 
		'confirmNewPassword' => '确认密码', 
		'code' => 'OTP 验证码'
	], 
	'placeholders' => [
		'email' => '在此输入电子邮件地址...', 
		'username' => '在此输入用户名...', 
		'password' => '在此输入密码...', 
		'newPassword' => '在此输入密码...', 
		'confirmNewPassword' => '确认密码', 
		'code' => '在此输入 OTP 验证码...', 
	], 
	'buttons' => [
		'login' => '登录', 
		'resetPassword' => '重置密码', 
		'setPassword' => '设置密码', 
		'verify' => '验证 OTP'

	], 
	'links' => [
		'login' => '登录', 
		'resetPassword' => '重置密码'
	], 
	'errors' => [
	    'passwordFormatNotValid' => '密码不符合安全要求。',
	],
	'audits' => [
	    'loginRefused'      => '使用不存在的账号登录尝试',
	    'loginBlocked'      => '因达到尝试次数限制而拒绝访问',
	    'loginFailed'       => '登录尝试失败 %s %s',
	    '2faRequired'       => '需要 2FA 验证码 %s %s %s',
	    'loginSuccess'      => '登录成功 %s %s',
	    'resetPasswordAuth' => '重置密码 %s %s',
	    'setPassword'       => '设置密码 %s %s',
	    'verifyFailed'      => '使用不存在的账号登录尝试',
	    '2faBlocked'        => '2FA 锁定 %s %s',
	    '2faFailed'         => '2FA 验证码错误或已过期 %s %s',
	],
	'messages' => [
		'goodbye' => '再见 %s %s。', 
		'welcome' => '早上好 %s %s。', 
		'loginFailed' => '登录尝试失败。', 
	    'loginNeeded' => '请登录以访问。', 
	    'resetPasswordFailed' => '重置密码时出错。', 
		'setPasswordSuccess' => '密码已设置。 <a class="fw-bold text-white" href="' . base_url('auth/login') . '"><i class="fa-solid fa-right-to-bracket"></i> 登录。</a>', 
		'setPasswordFailed' => '设置密码时出错。', 
		'setPasswordError' => '设置密码时出错。', 
		'checkAuthError' => '身份验证码无效或已过期。', 
		'tooManyAttempts' => '已达到尝试次数限制，请稍后再试。',
	    'currentSessionOn' => '%s %s 会话仍在进行中。', 
		'validationErrors' => '验证错误。', 
		'resetPasswordSuccess' => '如果该地址属于有效账户，您将收到一封包含密码重置说明的电子邮件。',
		'resetPasswordSuccessNoEmail' => '重置密码流程已设置，但电子邮件未发送。请联系用户。', 
		'expiredCode' => 'OTP 验证码已过期。',
		'wrongCode' => 'OTP 验证码错误。', 
		'twoFactorEmailSendError' => '无法通过电子邮件发送验证码。请稍后重试。',
	]
];

$language['titles']['register'] = '注册';
$language['buttons']['register'] = '注册';
$language['messages']['registerSuccess'] = '如果此邮箱可注册，您将收到设置密码的链接。请检查邮箱。';
$language['messages']['registerError'] = '注册失败，请稍后重试。';
$language['email']['registerSubject'] = '激活您的账户';
$language['messages']['sessionExpired'] = '会话已过期，请重新登录。';
$language['messages']['verifyError'] = '无法完成验证，请重试。';
$language['messages']['logoutError'] = '退出登录时发生错误。';
$language['links']['accessOrRegister'] = '登录或注册';
$language['titles']['restore'] = '恢复账户';
$language['buttons']['restoreAccount'] = '取消删除请求并登录';
$language['messages']['confirmAccountRestore'] = '您的账户正等待删除。请确认取消删除请求并登录。';
$language['audits']['removalCancelled'] = '用户登录时取消了账户删除请求。';
$language['links']['returnToLogin'] = '返回登录';
return $language;
