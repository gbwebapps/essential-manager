<div class="contact-feedback" id="contact-feedback" role="status" aria-live="polite" aria-atomic="true" hidden>
    <span data-contact-feedback-message></span>
    <button class="contact-feedback-close" type="button" <?= ui_binding('backend/global.buttons.close', 'aria-label'); ?> aria-label="<?= esc(lang('backend/global.buttons.close'), 'attr'); ?>">&times;</button>
</div>

<form id="contacts-form" method="post" action="<?= esc(site_url('contacts'), 'attr'); ?>" data-bs-theme="dark" data-csrf-name="<?= esc(csrf_token(), 'attr'); ?>" <?= ui_binding('frontend/contacts.messages.failed', 'data-failure'); ?> data-failure="<?= esc(lang('frontend/contacts.messages.failed'), 'attr'); ?>">
    <?= csrf_field(); ?>

    <div hidden aria-hidden="true">
        <label for="contact-website">Website</label>
        <input type="text" id="contact-website" name="website" tabindex="-1" autocomplete="off">
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="contact-name">
                <?= ui_text('frontend/contacts.fields.name', [], true); ?><span class="ms-1 text-danger" aria-hidden="true">*</span>
            </label>
            <input class="form-control" id="contact-name" name="name" type="text" maxlength="120" autocomplete="name" <?= ui_binding('frontend/contacts.placeholders.name', 'placeholder'); ?> placeholder="<?= esc(lang('frontend/contacts.placeholders.name'), 'attr'); ?>" aria-describedby="contact-name-error" required>
            <div id="contact-name-error" class="error_name text-danger-emphasis small" aria-live="polite"></div>
        </div>

        <div class="col-md-6">
            <label class="form-label" for="contact-email">
                <?= ui_text('frontend/contacts.fields.email', [], true); ?><span class="ms-1 text-danger" aria-hidden="true">*</span>
            </label>
            <input class="form-control" id="contact-email" name="email" type="email" maxlength="254" autocomplete="email" <?= ui_binding('frontend/contacts.placeholders.email', 'placeholder'); ?> placeholder="<?= esc(lang('frontend/contacts.placeholders.email'), 'attr'); ?>" aria-describedby="contact-email-error" required>
            <div id="contact-email-error" class="error_email text-danger-emphasis small" aria-live="polite"></div>
        </div>

        <div class="col-md-6">
            <label class="form-label" for="contact-phone">
                <?= ui_text('frontend/contacts.fields.phone', [], true); ?><span class="ms-1 text-danger" aria-hidden="true">*</span>
            </label>
            <input class="form-control" id="contact-phone" name="phone" type="tel" maxlength="30" pattern="[0-9+.\(\)\-\s]{6,30}" autocomplete="tel" <?= ui_binding('frontend/contacts.placeholders.phone', 'placeholder'); ?> placeholder="<?= esc(lang('frontend/contacts.placeholders.phone'), 'attr'); ?>" aria-describedby="contact-phone-error" required>
            <div id="contact-phone-error" class="error_phone text-danger-emphasis small" aria-live="polite"></div>
        </div>

        <div class="col-12">
            <label class="form-label" for="contact-message">
                <?= ui_text('frontend/contacts.fields.message', [], true); ?><span class="ms-1 text-danger" aria-hidden="true">*</span>
            </label>
            <textarea class="form-control" id="contact-message" name="message" rows="4" maxlength="3000" <?= ui_binding('frontend/contacts.placeholders.message', 'placeholder'); ?> placeholder="<?= esc(lang('frontend/contacts.placeholders.message'), 'attr'); ?>" aria-describedby="contact-message-error" required></textarea>
            <div id="contact-message-error" class="error_message text-danger-emphasis small" aria-live="polite"></div>
        </div>
    </div>

    <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-3 mt-4">
        <div class="flex-grow-1">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="contact-privacy" name="privacy" value="1" aria-describedby="contact-privacy-error" required>
                <label class="form-check-label" for="contact-privacy"><?= ui_text('frontend/contacts.privacy.accept', [], true); ?><a class="link-light text-decoration-underline fw-bold" href="<?= esc(site_url('contacts/privacy'), 'attr'); ?>"><?= ui_text('frontend/contacts.privacy.link', [], true); ?></a>.</label>
            </div>
            <div id="contact-privacy-error" class="error_privacy text-danger-emphasis small" aria-live="polite"></div>
        </div>
        <button class="btn btn-outline-light btn-sm text-nowrap flex-shrink-0 align-self-end align-self-sm-center" type="submit">
            <span data-contact-ready><i class="fa-solid fa-paper-plane me-1" aria-hidden="true"></i> <?= ui_text('frontend/contacts.buttons.send', [], true); ?></span>
            <span data-contact-sending hidden><span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span> <?= ui_text('frontend/contacts.buttons.sending', [], true); ?></span>
        </button>
    </div>
</form>
