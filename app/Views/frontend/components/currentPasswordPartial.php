<div class="row mt-3 mb-3">
    <div class="<?= esc($passwordColumnClass ?? 'col-12 col-md-4 mx-auto', 'attr'); ?>">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <label class="form-label mb-0 required-field" for="<?= esc($passwordFieldId, 'attr'); ?>"><?= ui_text('frontend/account.labels.currentPassword', [], false); ?></label>
            <button <?= ui_binding('backend/global.messages.currentPasswordInfo', 'data-password-info', [], true); ?> <?= ui_binding('backend/global.messages.currentPasswordInfo', 'aria-label', [], true); ?> type="button" class="btn btn-link p-0 lh-1" data-password-info="<?= esc(lang('backend/global.messages.currentPasswordInfo'), 'attr'); ?>" aria-label="<?= esc(lang('backend/global.messages.currentPasswordInfo'), 'attr'); ?>" aria-expanded="false"><i class="fa-solid fa-circle-info" aria-hidden="true"></i></button>
        </div>
        <div class="input-group">
            <input <?= ui_binding('frontend/account.errors.currentPasswordRequired', 'data-required-message', [], true); ?> <?= ui_binding('frontend/account.placeholders.currentPassword', 'placeholder', [], true); ?> type="password" name="currentPassword" id="<?= esc($passwordFieldId, 'attr'); ?>" class="form-control" autocomplete="current-password" placeholder="<?= esc(lang('frontend/account.placeholders.currentPassword'), 'attr'); ?>" data-required-message="<?= esc(lang('frontend/account.errors.currentPasswordRequired'), 'attr'); ?>" aria-required="true">
            <button <?= ui_binding('backend/global.interface.showPassword', 'aria-label', [], false); ?> type="button" class="btn btn-secondary toggle-password-visibility" aria-label="<?= esc(lang('backend/global.interface.showPassword'), 'attr'); ?>" aria-pressed="false"><i class="fa-solid fa-eye"></i></button>
        </div>
        <div class="error_currentPassword app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
    </div>
</div>
