<?php $otpDigits = (int) setting('Frontend\Auth')->twoFactorDigits; ?>
<div class="row">
    <!-- col-12 per mobile, col-sm-8 per tablet portrait piccolo, col-md-6 per tablet, col-lg-4 per desktop -->
    <div class="col-12">

        <form id="verify_form">

            <!-- Campo code -->
            <div class="mb-2">
                <label for="code" class="form-label required-field"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('frontend/auth.labels.code', [], true); ?></label>
                <div class="input-group">
                    <input type="text" id="code" name="code" class="form-control" <?= ui_binding('frontend/auth.placeholders.code', 'placeholder'); ?> placeholder="<?= esc(lang('frontend/auth.placeholders.code'), 'attr'); ?>" inputmode="numeric" pattern="[0-9]{<?= $otpDigits; ?>}" minlength="<?= $otpDigits; ?>" maxlength="<?= $otpDigits; ?>" autocomplete="one-time-code" aria-describedby="error-code" aria-required="true">
                    <span class="input-group-text font-monospace"
                          data-otp-countdown
                          data-otp-countdown-mode="<?= esc($otpCountdownMode, 'attr'); ?>"
                          data-server-time="<?= esc((string) $otpCountdownServerTime, 'attr'); ?>"
                          data-expires-at="<?= esc((string) $otpCountdownExpiresAt, 'attr'); ?>"
                          data-period="<?= esc((string) $otpCountdownPeriod, 'attr'); ?>"
                          role="timer"
                          <?= ui_binding('frontend/auth.labels.code', 'aria-label'); ?> aria-label="<?= esc(lang('frontend/auth.labels.code'), 'attr'); ?>">--s</span>
                </div>
                <div id="error-code" class="error_code app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
            </div>
            <!-- End Campo code -->

            <div class="d-flex justify-content-end align-items-center">

                <!-- Pulsante invio dati -->
                <button type="submit" class="btn btn-sm app-btn-secondary"><i class="fa-solid fa-floppy-disk"></i><?= ui_text('frontend/auth.buttons.verify', [], true); ?></button>
                <!-- End Pulsante invio dati -->

            </div>

        </form>

        <div class="text-center mt-4">
            <a href="<?= base_url('auth/login'); ?>"><i class="fa-solid fa-right-to-bracket"></i><?= ui_text('frontend/auth.links.login', [], true); ?></a>
        </div>

    </div>
</div>
