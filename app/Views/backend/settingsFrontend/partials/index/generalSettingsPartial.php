<div class="card-body">

    <!-- Indicatore della sorgente dati -->
    <div class="ps-2 py-1 app-bg-surface fw-bold small mb-3 border text-center rounded-2">
        <?php if (isset($isFromDatabase) && $isFromDatabase): ?>
            <span class="app-text-accent">
                <i class="fa-solid fa-database me-1"></i> <?= ui_text('backend/settingsBackend.messages.sourceDatabase', [], false); ?>
            </span>
        <?php else: ?>
            <span class="app-text">
                <i class="fa-solid fa-file-code me-1"></i> <?= ui_text('backend/settingsBackend.messages.sourceConfigFile', [], false); ?>
            </span>
        <?php endif; ?>
    </div>

    <form id="general-settings" autocomplete="off" novalidate data-unsaved-check>
        <div class="row">
            <div class="col-12">

                <div class="row">
                    <div class="col-12 mb-4">
                        <div class="row g-0 app-bg-surface pt-2 ps-2 pe-2 border rounded-2">
                            <div class="col-12 text-start">
                                <h5><i class="fa-solid fa-globe me-2"></i><?= ui_text('backend/settingsFrontend.titles.general', [], false); ?></h5>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="site-name" class="form-label mb-1 required-field">
                            <i class="fa-solid fa-circle-arrow-down"></i>
                            <?= ui_text('backend/settingsFrontend.labels.siteName', [], false); ?>
                        </label>
                        <input class="form-control" id="site-name" name="siteName" value="<?= esc($frontendSettings['siteName'], 'attr'); ?>" maxlength="100" aria-required="true">
                        <div class="error_siteName app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="frontend-language" class="form-label mb-1 required-field">
                            <i class="fa-solid fa-circle-arrow-down"></i>
                            <?= ui_text('backend/settingsFrontend.labels.language', [], false); ?>
                        </label>
                        <select class="form-select tom-select" id="frontend-language" name="language" aria-required="true">
                            <?php foreach ($languages as $code => $name): ?>
                                <option value="<?= esc($code, 'attr'); ?>"<?= $frontendSettings['language'] === $code ? ' selected' : ''; ?>><?= esc($name); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="error_language app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="frontend-timezone" class="form-label mb-1 required-field">
                            <i class="fa-solid fa-circle-arrow-down"></i>
                            <?= ui_text('backend/settingsFrontend.labels.timezone', [], false); ?>
                        </label>
                        <select class="form-select tom-select" id="frontend-timezone" name="timezone" aria-required="true">
                            <?php foreach ($timezones as $timezone): ?>
                                <option value="<?= esc($timezone, 'attr'); ?>"<?= $frontendSettings['timezone'] === $timezone ? ' selected' : ''; ?>><?= esc($timezone); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="error_timezone app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
                    </div>
                </div>

                <p class="small app-text-muted mb-3"><?= ui_text('backend/settingsFrontend.messages.englishFallback', [], false); ?></p>

                <div class="accordion mb-3" id="frontend-copy-settings">
                    <?php foreach ($languages as $code => $label): ?>
                        <div class="accordion-item mb-3 border rounded overflow-hidden shadow-sm">
                            <h3 class="accordion-header" id="frontend-copy-heading-<?= esc($code, 'attr'); ?>">
                                <button class="accordion-button collapsed shadow-none app-bg-surface app-text-muted py-3" data-settings-panel type="button" aria-expanded="false" aria-controls="frontend-copy-<?= esc($code, 'attr'); ?>">
                                    <span class="card-title mb-0 fs-5 fw-medium"><?= esc($label); ?> (<?= esc(strtoupper($code)); ?>)</span>
                                </button>
                            </h3>
                            <div id="frontend-copy-<?= esc($code, 'attr'); ?>" class="accordion-collapse collapse" aria-labelledby="frontend-copy-heading-<?= esc($code, 'attr'); ?>" data-bs-parent="#frontend-copy-settings">
                                <div class="accordion-body app-bg-surface-elevated border-top mb-0">
                                    <div class="mb-3">
                                        <label class="form-label mb-1<?= $code === 'en' ? ' required-field' : ''; ?>" for="frontend-title-<?= esc($code, 'attr'); ?>">
                                            <i class="fa-solid fa-circle-arrow-down"></i>
                                            <?= ui_text('backend/settingsFrontend.labels.title', [], false); ?>
                                        </label>
                                        <input class="form-control" id="frontend-title-<?= esc($code, 'attr'); ?>" name="title[<?= esc($code, 'attr'); ?>]" value="<?= esc($frontendSettings['title'][$code] ?? '', 'attr'); ?>" maxlength="255"<?= $code === 'en' ? ' aria-required="true"' : ''; ?>>
                                        <div class="error_title_<?= esc($code, 'attr'); ?> app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
                                    </div>
                                    <div>
                                        <label class="form-label mb-1<?= $code === 'en' ? ' required-field' : ''; ?>" for="frontend-description-<?= esc($code, 'attr'); ?>">
                                            <i class="fa-solid fa-circle-arrow-down"></i>
                                            <?= ui_text('backend/settingsFrontend.labels.description', [], false); ?>
                                        </label>
                                        <textarea class="form-control" id="frontend-description-<?= esc($code, 'attr'); ?>" name="description[<?= esc($code, 'attr'); ?>]" rows="3" maxlength="1000"<?= $code === 'en' ? ' aria-required="true"' : ''; ?>><?= esc($frontendSettings['description'][$code] ?? ''); ?></textarea>
                                        <div class="error_description_<?= esc($code, 'attr'); ?> app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="row">
                    <div class="col-12">
                        <!-- Flexbox: In colonna su mobile, in riga da tablet in poi, centrato orizzontalmente -->
                        <div class="d-flex flex-column flex-md-row justify-content-center gap-2">

                            <button <?= ui_binding('backend/settingsBackend.messages.areYouSureDeleteSettings', 'data-message', [], false); ?> type="button" class="btn app-btn-danger btn-sm btn-delete-general" data-message="<?= lang('backend/settingsBackend.messages.areYouSureDeleteSettings'); ?>">
                                <i class="fa-solid fa-trash-can me-1"></i><?= ui_text('backend/settingsBackend.buttons.restoreData', [], false); ?>
                            </button>

                            <button <?= ui_binding('backend/settingsBackend.messages.areYouSureRefreshSettings', 'data-message', [], false); ?> type="button" class="btn app-btn-warning btn-sm btn-refresh-general" data-message="<?= lang('backend/settingsBackend.messages.areYouSureRefreshSettings'); ?>">
                                <i class="fa-solid fa-rotate me-1"></i><?= ui_text('backend/settingsBackend.buttons.refreshData', [], false); ?>
                            </button>

                            <button type="submit" class="btn app-btn-success btn-sm btn-save-general">
                                <i class="fa-solid fa-floppy-disk me-1"></i><?= ui_text('backend/settingsBackend.buttons.sendData', [], false); ?>
                            </button>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>
