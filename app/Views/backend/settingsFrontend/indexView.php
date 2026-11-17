<?= $this->extend('backend/template/mainView') ?>

<?= $this->section('content') ?>

    <div class="container-fluid container-lg px-0">
        <div class="row g-0">
            <div class="col-12">

                <div class="accordion" id="mainSettingsDashboard" data-frontend-settings
                     data-open-url="<?= esc(base_url('backend/settingsFrontend/openSettings'), 'attr'); ?>"
                     data-save-url="<?= esc(base_url('backend/settingsFrontend/saveSettings'), 'attr'); ?>"
                     data-delete-url="<?= esc(base_url('backend/settingsFrontend/deleteSettings'), 'attr'); ?>"
                     data-check-delete-url="<?= esc(base_url('backend/settingsFrontend/checkDeleteSettings'), 'attr'); ?>">

                    <div class="accordion-item mb-3 border shadow-sm">
                        <h2 class="accordion-header" id="main_heading_general">
                            <button class="accordion-button collapsed shadow-none app-bg-surface app-text-muted py-3 btn-trigger-general-settings" data-settings-panel type="button" aria-expanded="false" aria-controls="main_collapse_general">
                                <span class="card-title mb-0 fs-5 fw-medium">
                                    <i class="fa-solid fa-gear me-2"></i>
                                    <?= ui_text('backend/settingsFrontend.titles.section', [], false); ?>
                                </span>
                            </button>
                        </h2>
                        <div id="main_collapse_general" class="accordion-collapse collapse" aria-labelledby="main_heading_general" data-bs-parent="#mainSettingsDashboard">
                            <div id="general-settings-container" class="accordion-body app-bg-surface-elevated border-top mb-0"></div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>

<?= $this->endSection() ?>
