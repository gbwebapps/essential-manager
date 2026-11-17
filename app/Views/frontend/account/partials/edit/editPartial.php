<form id="account-edit" data-unsaved-check>
    <div class="card-body pb-0">

    	<!-- Nome e cognome -->
        <div class="row">
            <div class="col-12 col-lg-6">
                <div class="mb-2">
                    <label for="firstname" class="form-label required-field"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('frontend/account.labels.firstname', [], false); ?></label>
					<input <?= ui_binding('frontend/account.placeholders.firstname', 'placeholder', [], true); ?> type="text" id="firstname" name="firstname" value="<?= esc($currentUser->firstname, 'attr'); ?>" class="form-control" placeholder="<?= esc(lang('frontend/account.placeholders.firstname'), 'attr'); ?>" aria-required="true">
                    <div class="error_firstname app-text-danger fw-bold small pt-1">&nbsp;</div>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="mb-2">
                    <label for="lastname" class="form-label required-field"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('frontend/account.labels.lastname', [], false); ?></label>
					<input <?= ui_binding('frontend/account.placeholders.lastname', 'placeholder', [], true); ?> type="text" id="lastname" name="lastname" value="<?= esc($currentUser->lastname, 'attr'); ?>" class="form-control" placeholder="<?= esc(lang('frontend/account.placeholders.lastname'), 'attr'); ?>" aria-required="true">
                    <div class="error_lastname app-text-danger fw-bold small pt-1">&nbsp;</div>
                </div>
            </div>
        </div>
        <!-- End Nome e cognome -->

        <!-- Email e Phone -->
        <div class="row">
            <div class="col-12 col-lg-6">
                <!-- Campo email -->
                <div class="mb-2">
                    <label for="email" class="form-label required-field"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('frontend/account.labels.email', [], false); ?></label>
					<input <?= ui_binding('frontend/account.placeholders.email', 'placeholder', [], true); ?> type="email" id="email" name="email" value="<?= esc($currentUser->email, 'attr'); ?>" class="form-control" autocomplete="email" placeholder="<?= esc(lang('frontend/account.placeholders.email'), 'attr'); ?>" aria-required="true">
                    <div class="error_email app-text-danger fw-bold small pt-1">&nbsp;</div>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <!-- Campo phone -->
                <div class="mb-2">
                    <label for="phone" class="form-label required-field"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('frontend/account.labels.phone', [], false); ?></label>
					<input <?= ui_binding('frontend/account.placeholders.phone', 'placeholder', [], true); ?> type="tel" id="phone" name="phone" value="<?= esc($currentUser->phone, 'attr'); ?>" class="form-control" autocomplete="tel" placeholder="<?= esc(lang('frontend/account.placeholders.phone'), 'attr'); ?>" aria-required="true">
                    <div class="error_phone app-text-danger fw-bold small pt-1">&nbsp;</div>
                </div>
            </div>
        </div>
        <!-- End Email e Phone -->

        <!-- Note Aggiuntive -->
        <div class="row">
            <div class="col-12">
                <div>
                    <label for="note"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('frontend/account.labels.note', [], false); ?></label>
					<textarea <?= ui_binding('frontend/account.placeholders.note', 'placeholder', [], true); ?> name="note" id="note" rows="7" class="form-control" placeholder="<?= esc(lang('frontend/account.placeholders.note'), 'attr'); ?>"><?= esc($currentUser->note); ?></textarea>
                    <div class="error_note app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
                </div>
            </div>
        </div>
        <!-- End Note Aggiuntive -->

    </div>
</form>
