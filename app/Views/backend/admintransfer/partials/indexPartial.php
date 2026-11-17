<div class="container-fluid container-lg px-0">
    <div class="row g-3">

        <div class="col-12 col-lg-4">
            <div class="card h-100 border shadow-sm app-bg-surface">
                <div class="card-header app-bg-surface-elevated border-bottom py-3">
                    <h2 class="card-title mb-0 fs-5">
                        <i class="fa-solid fa-file-export me-2"></i><?= ui_text('backend/admintransfer.panels.export', [], true); ?>
                    </h2>
                </div>
                <div class="card-body">
                    <span class="badge app-bg-primary mb-3">XLSX</span>
                    <p class="app-text-muted mb-4"><?= ui_text('backend/admintransfer.descriptions.export', [], true); ?></p>
                    <button id="admintransfer-export-button" type="button" class="btn app-btn-primary" data-export-entity="admintransfer">
                        <i class="fa-solid fa-file-export me-2"></i><?= ui_text('backend/admintransfer.buttons.export', [], true); ?>
                    </button>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card h-100 border shadow-sm app-bg-surface">
                <div class="card-header app-bg-surface-elevated border-bottom py-3">
                    <h2 class="card-title mb-0 fs-5">
                        <i class="fa-solid fa-file-import me-2"></i><?= ui_text('backend/admintransfer.panels.import', [], true); ?>
                    </h2>
                </div>
                <div class="card-body">
                    <span class="badge app-bg-primary mb-3">XLSX</span>
                    <p class="app-text-muted mb-4"><?= ui_text('backend/admintransfer.descriptions.import', [], true); ?></p>

                    <a href="#" id="admintransfer-import-link" class="cmd-link" data-import-entity="admintransfer">
                        <i class="fa-solid fa-file-import me-2"></i><?= ui_text('backend/admintransfer.buttons.import', [], true); ?>
                    </a>
                    <?php if ($admintransferImport !== null): ?>
                        <a href="#" id="admintransfer-resume-import-link" class="btn cmd-link d-block mt-3">
                            <i class="fa-solid fa-clock-rotate-left me-2"></i><?= ui_text('backend/admintransfer.buttons.resume', [], true); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card border shadow-sm app-bg-surface">
                <div class="card-header app-bg-surface-elevated border-bottom py-3">
                    <h2 class="card-title mb-0 fs-5">
                        <i class="fa-solid fa-boxes-stacked me-2"></i><?= ui_text('backend/admintransfer.panels.package', [], true); ?>
                    </h2>
                </div>
                <div class="card-body">
                    <p class="app-text-muted"><?= ui_text('backend/admintransfer.descriptions.package', [], true); ?></p>
                    <ul class="mb-0">
                        <?php foreach ($packageTables as $table): ?>
                            <li><code><?= esc($table); ?></code></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Il modale è fornito dalle stesse viste del componente Import. -->
<div id="import-modal-container"></div>
<div <?= ui_binding('backend/admintransfer.messages.exportError', 'data-export-error', [], true); ?> id="export-modal-container" data-export-error="<?= esc(lang('backend/admintransfer.messages.exportError'), 'attr'); ?>"></div>
