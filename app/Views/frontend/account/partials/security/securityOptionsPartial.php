<div class="card-body">

    <div class="row mb-3 g-0 app-bg-surface security-section-title pt-2 ps-2 pe-2 border rounded-1">
        <div class="col-12 text-start">
            <h5 id="account-two-factor-label"><?= ui_text('frontend/account.titles.twoFactor', [], false); ?></h5>
        </div>
    </div>


    <!-- Ogni metodo occupa una riga, con altezza determinata dal contenuto. -->
    <div class="row gy-3" role="radiogroup" aria-labelledby="account-two-factor-label" aria-required="true">

        <?php foreach (setting('Frontend\Auth')->twoFactorMethods as $method) : ?>
            
            <div class="col-12">
                <div class="card border <?= ($activeMethod === $method) ? 'app-border-accent app-bg-surface' : ''; ?>">
                    <div class="card-body d-flex align-items-start p-3">
                        <div class="form-check w-100 m-0">
                            <input <?= ui_binding('frontend/account.messages.areYouSureChangeMethod', 'data-message', [], false); ?> class="form-check-input twofactor-method-trigger" 
                                   type="radio" 
                                   name="twoFactorMethod" 
                                   id="method-<?= esc($method); ?>" 
                                   value="<?= esc($method); ?>" 
                                   data-message="<?= lang('frontend/account.messages.areYouSureChangeMethod'); ?>" 
                                   data-requires-setup="<?= ($method === 'totp') ? 'true' : 'false'; ?>"
                                   <?= ($activeMethod === $method) ? 'checked' : ''; ?>>
                            <label class="form-check-label d-block ms-2 cursor-pointer" for="method-<?= esc($method); ?>">
                                <span class="d-block fw-bold mb-1">
                                    <?php if ($method === 'email') : ?>
                                        <i class="fa-solid fa-envelope me-1"></i>
                                    <?php elseif ($method === 'totp') : ?>
                                        <i class="fa-solid fa-mobile-screen me-1"></i>
                                    <?php endif; ?>
                                    <?= ui_text('frontend/account.labels.method' . ucfirst($method) . 'Title', [], false); ?>
                                </span>
                                <small class="app-text-muted d-block"><?= ui_text('frontend/account.labels.method' . ucfirst($method) . 'Desc', [], false); ?></small>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            
        <?php endforeach; ?>

    </div>

    <div id="totp-setup-wrapper"></div>

</div>
