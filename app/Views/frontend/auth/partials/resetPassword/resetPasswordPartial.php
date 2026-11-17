<div class="row">
    <!-- Aggiunto col-sm-8 e offset-sm-2 per coprire i tablet in portrait sotto i 768px. Su md passa a 6 colonne. -->
    <div class="col-12">

        <form id="reset_password_form">

            <!-- Campo email -->
            <div class="mb-2">
                <label for="email" class="form-label required-field"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('frontend/auth.labels.email', [], true); ?></label>
                <input type="email" id="email" name="email" class="form-control" <?= ui_binding('frontend/auth.placeholders.email', 'placeholder'); ?> placeholder="<?= esc(lang('frontend/auth.placeholders.email'), 'attr'); ?>" autocomplete="email" aria-describedby="error-email" aria-required="true">
                <div id="error-email" class="error_email app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
            </div>
            <!-- End Campo email -->

            <!-- Pulsante invio dati -->
            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-sm app-btn-secondary"><i class="fa-solid fa-floppy-disk"></i><?= ui_text('frontend/auth.buttons.resetPassword', [], true); ?></button>
            </div>
            <!-- End Pulsante invio dati -->

        </form>

        <div class="d-flex flex-wrap justify-content-center gap-3 mt-4">
            <a href="<?= base_url('auth/login'); ?>"><i class="fa-solid fa-right-to-bracket"></i><?= ui_text('frontend/auth.links.login', [], true); ?></a>
            <?php if (config(\Config\Frontend\Auth::class)->registration): ?>
                <a href="<?= base_url('auth/register'); ?>"><i class="fa-solid fa-user-plus"></i> <?= ui_text('frontend/auth.buttons.register', [], true); ?></a>
            <?php endif; ?>
        </div>

    </div>
</div>
