<div class="row">
    <!-- col-12 per mobile, col-sm-8 per tablet portrait piccolo, col-md-6 per tablet, col-lg-4 per desktop -->
    <div class="col-12">

        <form id="set_password_form">

            <!-- Campo nascosto che trasporta il token dal caricamento pagina (GET) al salvataggio (POST) -->
            <input type="hidden" name="token" value="<?= esc($token); ?>">

            <!-- Campo password -->
            <div class="mb-2">
                <label for="password" class="form-label required-field"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('frontend/auth.labels.newPassword', [], true); ?></label>
                <input type="password" id="password" name="password" class="form-control" <?= ui_binding('frontend/auth.placeholders.newPassword', 'placeholder'); ?> placeholder="<?= esc(lang('frontend/auth.placeholders.newPassword'), 'attr'); ?>" autocomplete="new-password" aria-describedby="error-password" aria-required="true">
                <div id="error-password" class="error_password app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
            </div>
            <!-- End Campo password -->

            <!-- Campo conferma password -->
            <div class="mb-2">
                <label for="password-confirm" class="form-label required-field"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('frontend/auth.labels.confirmNewPassword', [], true); ?></label>
                <input type="password" id="password-confirm" name="confirmPassword" class="form-control" <?= ui_binding('frontend/auth.placeholders.confirmNewPassword', 'placeholder'); ?> placeholder="<?= esc(lang('frontend/auth.placeholders.confirmNewPassword'), 'attr'); ?>" autocomplete="new-password" aria-describedby="error-confirm-password" aria-required="true">
                <div id="error-confirm-password" class="error_confirmPassword app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
            </div>
            <!-- End Campo conferma password -->

            <!-- Pulsante invio dati -->
            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-sm app-btn-secondary"><i class="fa-solid fa-floppy-disk"></i><?= ui_text('frontend/auth.buttons.setPassword', [], true); ?></button>
            </div>
            <!-- End Pulsante invio dati -->

        </form>

        <div class="text-center mt-4">
            <a href="<?= base_url('auth/login'); ?>"><i class="fa-solid fa-right-to-bracket"></i><?= ui_text('frontend/auth.links.login', [], true); ?></a>
        </div>

    </div>
</div>
