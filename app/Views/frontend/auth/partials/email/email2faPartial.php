<div>
    <?php 
        /* Determina l'etichetta corretta basandosi sul valore ricevuto dal servizio */
        $expiryLabel = $expiryMinutes === 1 ? lang("frontend/email.auth.2fa.expiryMinute") : lang("frontend/email.auth.2fa.expiryMinutes");
    ?>
    <p>
        <?= sprintf(lang("frontend/email.auth.2fa.greeting"), esc($firstname) . ' ' . esc($lastname)); ?>
    </p>
    <p>
        <?= sprintf(lang("frontend/email.auth.2fa.codeNotice"), esc($code), $expiryMinutes, $expiryLabel); ?>
    </p>
</div>