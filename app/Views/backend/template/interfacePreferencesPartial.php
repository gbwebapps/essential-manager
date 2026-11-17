<li class="nav-item d-flex flex-column flex-lg-row" id="interface-preferences" data-url="<?= esc(base_url('backend/interface/change'), 'attr'); ?>" data-active-language="<?= esc(backend_language(), 'attr'); ?>" data-active-theme="<?= esc(backend_theme(), 'attr'); ?>" data-active-font="<?= esc(backend_font(), 'attr'); ?>" data-fonts="<?= esc(json_encode(\App\Libraries\Backend\InterfacePreferences::FONTS, JSON_THROW_ON_ERROR), 'attr'); ?>">
    <div class="dropdown" id="interface-language-dropdown">
        <button class="nav-link dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" <?= ui_binding('backend/settingsBackend.labels.language', 'aria-label'); ?> aria-label="<?= esc(lang('backend/settingsBackend.labels.language'), 'attr'); ?>">
            <i class="fa-solid fa-globe" aria-hidden="true"></i> <?= ui_text('backend/settingsBackend.labels.language'); ?>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <?php foreach (\App\Libraries\LanguageOptions::all('native') as $code => $name): ?>
                <li><button type="button" class="dropdown-item<?= backend_language() === $code ? ' active' : ''; ?>" data-interface-language="<?= esc($code, 'attr'); ?>" aria-pressed="<?= backend_language() === $code ? 'true' : 'false'; ?>"><?= esc($name); ?></button></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div class="dropdown" id="interface-theme-dropdown">
        <button class="nav-link dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" <?= ui_binding('backend/global.interface.themes', 'aria-label'); ?> aria-label="<?= esc(lang('backend/global.interface.themes'), 'attr'); ?>">
            <i class="fa-solid fa-palette" aria-hidden="true"></i> <?= ui_text('backend/global.interface.themes'); ?>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <?php foreach (\App\Libraries\Backend\InterfacePreferences::THEMES as $theme): ?>
                <li><button type="button" class="dropdown-item<?= backend_theme() === $theme ? ' active' : ''; ?>" data-interface-theme="<?= esc($theme, 'attr'); ?>" aria-pressed="<?= backend_theme() === $theme ? 'true' : 'false'; ?>"><?= ui_text('backend/settingsBackend.themes.' . $theme); ?></button></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div class="dropdown" id="interface-font-dropdown">
        <button class="nav-link dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" <?= ui_binding('backend/settingsBackend.labels.font', 'aria-label'); ?> aria-label="<?= esc(lang('backend/settingsBackend.labels.font'), 'attr'); ?>">
            <i class="fa-solid fa-font" aria-hidden="true"></i> <?= ui_text('backend/settingsBackend.labels.font'); ?>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <?php foreach (\App\Libraries\Backend\InterfacePreferences::FONTS as $code => $label): ?>
                <li><button type="button" class="dropdown-item<?= backend_font() === $code ? ' active' : ''; ?>" data-interface-font="<?= esc($code, 'attr'); ?>" aria-pressed="<?= backend_font() === $code ? 'true' : 'false'; ?>"><?= esc($label); ?></button></li>
            <?php endforeach; ?>
        </ul>
    </div>
</li>
