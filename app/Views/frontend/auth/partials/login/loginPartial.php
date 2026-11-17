<div class="row">
    <!-- Aggiunto col-sm-8 e offset-sm-2 per coprire i tablet in portrait sotto i 768px. Su md passa a 6 colonne. -->
    <div class="col-12">

        <form id="login_form">

            <!-- Campo email -->
            <div class="mb-2">
                <label for="email" class="form-label required-field"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('frontend/auth.labels.email', [], true); ?></label>
                <input type="email" id="email" name="email" class="form-control" <?= ui_binding('frontend/auth.placeholders.email', 'placeholder'); ?> placeholder="<?= esc(lang('frontend/auth.placeholders.email'), 'attr'); ?>" autocomplete="username" aria-describedby="error-email" aria-required="true">
                <div id="error-email" class="error_email app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
            </div>
            <!-- End Campo email -->

            <!-- Campo password -->
            <div class="mb-2">
                <label for="password" class="form-label required-field"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('frontend/auth.labels.password', [], true); ?></label>
                <div class="input-group">
                    <input type="password" id="password" name="password" class="form-control" <?= ui_binding('frontend/auth.placeholders.password', 'placeholder'); ?> placeholder="<?= esc(lang('frontend/auth.placeholders.password'), 'attr'); ?>" autocomplete="current-password" aria-describedby="error-password" aria-required="true">
                    <button class="btn app-btn-secondary app-bg-surface-elevated border toggle-password-visibility" type="button" <?= ui_binding('backend/global.interface.showPassword', 'aria-label'); ?> aria-label="<?= esc(lang('backend/global.interface.showPassword'), 'attr'); ?>" aria-pressed="false">
                        <i class="fa-solid fa-eye app-text-muted"></i>
                    </button>
                </div>
                <div id="error-password" class="error_password app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
            </div>
            <!-- End Campo password -->

            <div class="d-flex justify-content-between align-items-center mt-3">

                <!-- Checkbox remember me -->
                <div class="form-check text-start mb-0">
                    <input class="form-check-input" type="checkbox" name="rememberMe" id="rememberMe">
                    <label class="form-check-label" for="rememberMe">
                        <?= ui_text('frontend/auth.labels.rememberMe', [], true); ?>
                    </label>
                </div>
                <!-- End Checkbox remember me -->

                <!-- Pulsante invio dati -->
                <button type="submit" class="btn btn-sm app-btn-secondary"><i class="fa-solid fa-floppy-disk"></i><?= ui_text('frontend/auth.buttons.login', [], true); ?></button>
                <!-- End Pulsante invio dati -->

            </div>

        </form>

        <div class="d-flex flex-wrap justify-content-center gap-3 mt-4">
            <a href="<?= base_url('auth/resetPassword'); ?>"><i class="fa-solid fa-unlock"></i><?= ui_text('frontend/auth.links.resetPassword', [], true); ?></a>
            <?php if (config(\Config\Frontend\Auth::class)->registration): ?>
                <a href="<?= base_url('auth/register'); ?>"><i class="fa-solid fa-user-plus"></i> <?= ui_text('frontend/auth.buttons.register', [], true); ?></a>
            <?php endif; ?>
        </div>

    </div>
</div>
