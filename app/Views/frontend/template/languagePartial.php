<form class="frontend-language-form ms-3 d-flex align-items-center" data-frontend-language-form>
    <label class="visually-hidden" for="frontend-language-choice"><?= ui_text('frontend/interface.labels.language', [], false); ?></label>
    <select class="form-select form-select-sm frontend-language-select" id="frontend-language-choice" name="language" data-native-select <?= ui_binding('frontend/interface.labels.language', 'aria-label'); ?> aria-label="<?= esc(lang('frontend/interface.labels.language'), 'attr'); ?>">
        <?php foreach ($frontendLanguages as $code => $name): ?>
            <option value="<?= esc($code, 'attr'); ?>"<?= service('request')->getLocale() === $code ? ' selected' : ''; ?>><?= esc($name); ?></option>
        <?php endforeach; ?>
    </select>
</form>
