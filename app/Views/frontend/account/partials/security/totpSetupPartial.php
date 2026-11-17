<?php $otpDigits = (int) setting('Frontend\Auth')->twoFactorDigits; ?>
<div class="card app-bg-surface border mt-3 animate__animated animate__fadeIn">
    <div class="card-body p-4">
        <div class="row g-4 align-items-center">
            
            <!-- Colonna QR Code (Centrato ovunque) -->
            <div class="col-12 col-lg-6 text-center">
                <div class="app-bg-surface-elevated p-3 d-inline-block rounded border shadow-sm">
					<img src="<?= esc($qrCode, 'attr'); ?>" alt="QR Code TOTP" class="img-fluid" style="max-width: 220px;">
                </div>
            </div>

            <!-- Colonna Contenuti e Form -->
            <div class="col-12 col-lg-6">
                <h5 class="fw-bold mb-2"><?= ui_text('frontend/account.titles.totpSetupTitle', [], false); ?></h5>
                <p class="app-text-muted small mb-3"><?= ui_text('frontend/account.labels.totpSetupDesc', [], false); ?></p>

                <form id="confirm-totp-form" novalidate data-unsaved-check>
                    <div class="row g-3 align-items-end">
                        
                        <!-- Chiave Segreta: Piena larghezza in portrait, affiancata in landscape -->
                        <div class="col-12 col-lg-7">
                            <label class="form-label app-text-muted small fw-bold mb-1"><?= ui_text('frontend/account.labels.totpSecretKey', [], false); ?></label>
                            <div class="input-group">
                                <button <?= ui_binding('frontend/account.buttons.showPassword', 'aria-label', [], true); ?> class="btn app-btn-secondary app-bg-surface-elevated border toggle-password-visibility"
                                        type="button"
                                        <?= ui_binding('frontend/account.buttons.showPassword', 'aria-label'); ?>
                                        aria-label="<?= esc(lang('frontend/account.buttons.showPassword'), 'attr'); ?>"
                                        aria-pressed="false">
                                    <i class="fa-solid fa-eye app-text-muted"></i>
                                </button>
                                <input type="password"
                                       id="totp-secret-field"
                                       class="form-control app-bg-surface-elevated font-monospace text-uppercase"
									   value="<?= esc($totpSecret, 'attr'); ?>"
                                       readonly>
                            </div>
                        </div>

                        <!-- Input Codice OTP: Piena larghezza in portrait, affiancato in landscape -->
                        <div class="col-12 col-lg-5">
                            <label for="otp-code" class="form-label small fw-bold mb-1 required-field"><?= ui_text('frontend/account.labels.enterOtpCode', [], false); ?></label>
                            <div class="input-group input-group-sm">
                                <input type="text"
                                       id="otp-code"
                                       name="otp"
                                       class="form-control text-center font-monospace"
									   inputmode="numeric"
									   autocomplete="one-time-code"
									   pattern="[0-9]{<?= $otpDigits; ?>}"
                                       maxlength="<?= $otpDigits; ?>"
                                       minlength="<?= $otpDigits; ?>" placeholder="<?= str_repeat('0', $otpDigits); ?>"
                                       style="letter-spacing: 4px; font-size: 1.1rem;" aria-required="true">
                                <span <?= ui_binding('frontend/account.labels.enterOtpCode', 'aria-label', [], true); ?> class="input-group-text font-monospace"
                                      data-otp-countdown
                                      data-otp-countdown-mode="period"
                                      data-server-time="<?= esc((string) $otpCountdownServerTime, 'attr'); ?>"
                                      data-period="<?= esc((string) $otpCountdownPeriod, 'attr'); ?>"
                                      role="timer"
                                      <?= ui_binding('frontend/account.labels.enterOtpCode', 'aria-label'); ?>
                                      aria-label="<?= esc(lang('frontend/account.labels.enterOtpCode'), 'attr'); ?>">--s</span>
                            </div>
                        </div>

                        <!-- Pulsanti di azione: Uno sopra l'altro a piena larghezza in portrait, affiancati sotto in landscape -->
                        <div class="col-12">
                            <div class="d-flex flex-column flex-sm-row gap-2">
                                <button type="submit" class="btn app-btn-success btn-sm flex-fill">
                                    <i class="fa-solid fa-circle-check"></i> <?= ui_text('frontend/account.buttons.verifyAndActivate', [], false); ?>
                                </button>
                                <button type="button" id="cancel-setup-btn" class="btn app-btn-danger btn-sm flex-fill">
                                    <i class="fa-solid fa-xmark"></i> <?= ui_text('frontend/account.buttons.undoClose', [], false); ?>
                                </button>
                            </div>
                        </div>

                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
