<div>
    <p>
        <?= ui_text("frontend/email.account.resetPassword.greeting", [esc($firstname) . ' ' . esc($lastname)], false); ?>
    </p>
    <p>
        <?= ui_text("frontend/email.account.resetPassword.requestPassword", [], false); ?><br>
        <?= ui_text("frontend/email.account.resetPassword.usernameReminder", [esc($email)], false); ?>
    </p>
    <p>
        <a href="<?= base_url('auth/setPassword/' . esc($token)); ?>">
            <?= ui_text("frontend/email.account.resetPassword.setPasswordButton", [], false); ?>
        </a>
    </p>
    <p>
        <?= ui_text("frontend/email.account.resetPassword.ignoreNotice", [], false); ?>
    </p>
</div>
