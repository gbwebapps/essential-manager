<div>
    <p>
        <?= sprintf(lang("frontend/email.auth.resetPassword.greeting"), esc($firstname) . ' ' . esc($lastname)); ?>
    </p>
    <p>
        <?= lang("frontend/email.auth.resetPassword.requestPassword"); ?><br>
        <?= sprintf(lang("frontend/email.auth.resetPassword.usernameReminder"), esc($email)); ?>
    </p>
    <p>
        <a href="<?= base_url('auth/setPassword/' . esc($token)); ?>">
            <?= lang("frontend/email.auth.resetPassword.setPasswordButton"); ?>
        </a>
    </p>
    <p>
        <?= lang("frontend/email.auth.resetPassword.ignoreNotice"); ?>
    </p>
</div>
