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
    
    <form id="upload-settings" autocomplete="off" novalidate data-unsaved-check>
        <div class="row">
            <div class="col-12">
            
                <!-- Sezione 1: Regole di Caricamento Immagini -->
                <div class="row">
                    <div class="col-12 mb-4">
                        <div class="row g-0 app-bg-surface pt-2 ps-2 pe-2 border rounded-2">
                            <div class="col-12 text-start">
                                <h5><i class="fa-solid fa-images me-2"></i><?= ui_text('backend/settingsBackend.labels.uploadImageRules', [], false); ?></h5>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="upload_renameImages" class="form-label  mb-1 required-field"><i class="fa-solid fa-circle-arrow-down"></i> <?= ui_text('backend/settingsBackend.labels.renameImages', [], false); ?></label>
                        <select id="upload_renameImages" name="renameImages" class="form-select shadow-none" aria-required="true">
                            <option <?= ui_binding('backend/settingsBackend.labels.disabled', 'text', [], false); ?> value="0" <?= (int) $uploadSettings['renameImages'] === 0 ? 'selected' : ''; ?>><?= lang('backend/settingsBackend.labels.disabled'); ?></option>
                            <option <?= ui_binding('backend/settingsBackend.labels.enabled', 'text', [], false); ?> value="1" <?= (int) $uploadSettings['renameImages'] === 1 ? 'selected' : ''; ?>><?= lang('backend/settingsBackend.labels.enabled'); ?></option>
                        </select>
                        <div class="error_renameImages app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="upload_overwriteImages" class="form-label  mb-1 required-field"><i class="fa-solid fa-circle-arrow-down"></i> <?= ui_text('backend/settingsBackend.labels.overwriteImages', [], false); ?></label>
                        <select id="upload_overwriteImages" name="overwriteImages" class="form-select shadow-none" aria-required="true">
                            <option <?= ui_binding('backend/settingsBackend.labels.disabled', 'text', [], false); ?> value="0" <?= (int) $uploadSettings['overwriteImages'] === 0 ? 'selected' : ''; ?>><?= lang('backend/settingsBackend.labels.disabled'); ?></option>
                            <option <?= ui_binding('backend/settingsBackend.labels.enabled', 'text', [], false); ?> value="1" <?= (int) $uploadSettings['overwriteImages'] === 1 ? 'selected' : ''; ?>><?= lang('backend/settingsBackend.labels.enabled'); ?></option>
                        </select>
                        <div class="error_overwriteImages app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
                    </div>
                </div>

                <!-- Limiti di Peso File -->
                <div class="row">
                    <div class="col-12 mb-4">
                        <div class="row g-0 app-bg-surface pt-2 ps-2 pe-2 border rounded-2">
                            <div class="col-12 text-start">
                                <h5><i class="fa-solid fa-weight-hanging me-2"></i><?= ui_text('backend/settingsBackend.labels.uploadWeightRules', [], false); ?></h5>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 mb-3">
                        <label for="upload_maxFileSize" class="form-label  mb-1 required-field"><i class="fa-solid fa-circle-arrow-down"></i> <?= ui_text('backend/settingsBackend.labels.maxFileSize', [], false); ?></label>
                        <input <?= ui_binding('backend/settingsBackend.placeholders.maxFileSize', 'placeholder', [], false); ?> type="number" id="upload_maxFileSize" name="maxFileSize" class="form-control shadow-none" min="1" value="<?= esc($uploadSettings['maxFileSize']); ?>" placeholder="<?= lang('backend/settingsBackend.placeholders.maxFileSize'); ?>" aria-required="true">
                        <div class="error_maxFileSize app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
                    </div>

                    <div class="col-12 col-md-6 mb-3">
                        <label for="upload_allowedExtensions" class="form-label  mb-1 required-field">
                            <i class="fa-solid fa-circle-arrow-down"></i> <?= ui_text('backend/settingsBackend.labels.allowedExtensions', [], false); ?>
                        </label>
                        
                        <!-- Trasformiamo l'input in una select multipla. L'attributo name termina con [] per inviare un array a PHP -->
                        <select id="upload_allowedExtensions" name="allowedExtensions[]" class="tom-select shadow-none" multiple aria-required="true">
                            <?php
                            /* 
                               Prendiamo la stringa salvata a database (es. "png|jpg|webp") 
                               e la convertiamo in un array per verificare quali elementi selezionare di default 
                            */
                            $currentExtensions = ! empty($uploadSettings['allowedExtensions']) ? explode('|', $uploadSettings['allowedExtensions']) : [];
                                
                            /* Elenco delle estensioni comuni che vogliamo proporre nella selezione */
                            $availableExtensions = ['png', 'jpg', 'jpeg', 'webp', 'avif', 'gif', 'svg'];
                            ?>

                            <?php foreach ($availableExtensions as $ext) : ?>
                                <option value="<?= esc($ext); ?>" <?= in_array($ext, $currentExtensions, true) ? 'selected' : ''; ?>>
                                    <?= esc(strtoupper($ext)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <div class="error_allowedExtensions app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
                    </div>
                </div>

                <!-- Dimensioni larghezza e altezza massima -->
                <div class="row">
                    <div class="col-12 mb-4">
                        <div class="row g-0 app-bg-surface pt-2 ps-2 pe-2 border rounded-2">
                            <div class="col-12 text-start">
                                <h5><i class="fa-solid fa-expand me-2"></i><?= ui_text('backend/settingsBackend.labels.dimensionsOriginal', [], false); ?></h5>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="upload_maxImageX" class="form-label  mb-1 required-field"><i class="fa-solid fa-circle-arrow-down"></i> <?= ui_text('backend/settingsBackend.labels.maxImageX', [], false); ?></label>
                        <input <?= ui_binding('backend/settingsBackend.placeholders.maxImageX', 'placeholder', [], false); ?> type="number" id="upload_maxImageX" name="maxImageX" class="form-control shadow-none" min="0" value="<?= esc($uploadSettings['maxImageX']); ?>" placeholder="<?= lang('backend/settingsBackend.placeholders.maxImageX'); ?>" aria-required="true">
                        <div class="error_maxImageX app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="upload_maxImageY" class="form-label  mb-1 required-field"><i class="fa-solid fa-circle-arrow-down"></i> <?= ui_text('backend/settingsBackend.labels.maxImageY', [], false); ?></label>
                        <input <?= ui_binding('backend/settingsBackend.placeholders.maxImageY', 'placeholder', [], false); ?> type="number" id="upload_maxImageY" name="maxImageY" class="form-control shadow-none" min="0" value="<?= esc($uploadSettings['maxImageY']); ?>" placeholder="<?= lang('backend/settingsBackend.placeholders.maxImageY'); ?>" aria-required="true">
                        <div class="error_maxImageY app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
                    </div>
                </div>

                <!-- Sezione 2: Dimensioni Anteprime Medie -->
                <div class="row">
                    <div class="col-12 mb-4">
                        <div class="row g-0 app-bg-surface pt-2 ps-2 pe-2 border rounded-2">
                            <div class="col-12 text-start">
                                <h5><i class="fa-solid fa-maximize me-2"></i><?= ui_text('backend/settingsBackend.labels.dimensionsMedium', [], false); ?></h5>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="upload_resizeMediumX" class="form-label  mb-1 required-field"><i class="fa-solid fa-circle-arrow-down"></i> <?= ui_text('backend/settingsBackend.labels.resizeMediumX', [], false); ?></label>
                        <input <?= ui_binding('backend/settingsBackend.placeholders.resizeMediumX', 'placeholder', [], false); ?> type="number" id="upload_resizeMediumX" name="resizeMediumX" class="form-control shadow-none" min="0" value="<?= esc($uploadSettings['resizeMediumX']); ?>" placeholder="<?= lang('backend/settingsBackend.placeholders.resizeMediumX'); ?>" aria-required="true">
                        <div class="error_resizeMediumX app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="upload_resizeMediumY" class="form-label  mb-1 required-field"><i class="fa-solid fa-circle-arrow-down"></i> <?= ui_text('backend/settingsBackend.labels.resizeMediumY', [], false); ?></label>
                        <input <?= ui_binding('backend/settingsBackend.placeholders.resizeMediumY', 'placeholder', [], false); ?> type="number" id="upload_resizeMediumY" name="resizeMediumY" class="form-control shadow-none" min="0" value="<?= esc($uploadSettings['resizeMediumY']); ?>" placeholder="<?= lang('backend/settingsBackend.placeholders.resizeMediumY'); ?>" aria-required="true">
                        <div class="error_resizeMediumY app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
                    </div>
                </div>

                <!-- Sezione 3: Dimensioni Anteprime Piccole -->
                <div class="row">
                    <div class="col-12 mb-4">
                        <div class="row g-0 app-bg-surface pt-2 ps-2 pe-2 border rounded-2">
                            <div class="col-12 text-start">
                                <h5><i class="fa-solid fa-minimize me-2"></i><?= ui_text('backend/settingsBackend.labels.dimensionsSmall', [], false); ?></h5>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="upload_resizeSmallX" class="form-label  mb-1 required-field"><i class="fa-solid fa-circle-arrow-down"></i> <?= ui_text('backend/settingsBackend.labels.resizeSmallX', [], false); ?></label>
                        <input <?= ui_binding('backend/settingsBackend.placeholders.resizeSmallX', 'placeholder', [], false); ?> type="number" id="upload_resizeSmallX" name="resizeSmallX" class="form-control shadow-none" min="0" value="<?= esc($uploadSettings['resizeSmallX']); ?>" placeholder="<?= lang('backend/settingsBackend.placeholders.resizeSmallX'); ?>" aria-required="true">
                        <div class="error_resizeSmallX app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="upload_resizeSmallY" class="form-label  mb-1 required-field"><i class="fa-solid fa-circle-arrow-down"></i> <?= ui_text('backend/settingsBackend.labels.resizeSmallY', [], false); ?></label>
                        <input <?= ui_binding('backend/settingsBackend.placeholders.resizeSmallY', 'placeholder', [], false); ?> type="number" id="upload_resizeSmallY" name="resizeSmallY" class="form-control shadow-none" min="0" value="<?= esc($uploadSettings['resizeSmallY']); ?>" placeholder="<?= lang('backend/settingsBackend.placeholders.resizeSmallY'); ?>" aria-required="true">
                        <div class="error_resizeSmallY app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
                    </div>
                </div>


                <!-- Pulsanti di Controllo -->
                <div class="row">
                    <div class="col-12">
                        <!-- Flexbox: In colonna su mobile, in riga da tablet in poi, centrato orizzontalmente -->
                        <div class="d-flex flex-column flex-md-row justify-content-center gap-2">
                            
                            <button <?= ui_binding('backend/settingsBackend.messages.areYouSureDeleteSettings', 'data-message', [], false); ?> type="button" class="btn app-btn-danger btn-sm btn-delete-upload" data-message="<?= lang('backend/settingsBackend.messages.areYouSureDeleteSettings'); ?>">
                                <i class="fa-solid fa-trash-can me-1"></i><?= ui_text('backend/settingsBackend.buttons.restoreData', [], false); ?>
                            </button>
                            
                            <button <?= ui_binding('backend/settingsBackend.messages.areYouSureRefreshSettings', 'data-message', [], false); ?> type="button" class="btn app-btn-warning btn-sm btn-refresh-upload" data-message="<?= lang('backend/settingsBackend.messages.areYouSureRefreshSettings'); ?>">
                                <i class="fa-solid fa-rotate me-1"></i><?= ui_text('backend/settingsBackend.buttons.refreshData', [], false); ?>
                            </button>
                            
                            <button type="submit" class="btn app-btn-success btn-sm btn-save-upload">
                                <i class="fa-solid fa-floppy-disk me-1"></i><?= ui_text('backend/settingsBackend.buttons.sendData', [], false); ?>
                            </button>
                            
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>
