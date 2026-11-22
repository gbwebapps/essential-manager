<div class="card-body">
    <div class="row">
        <div class="col-12 col-lg-6 mb-3">
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/applications.labels.name', [], false); ?></li>
                <li class="list-group-item fw-bold text-break" style="white-space: pre-wrap;"><?= esc($application->name ?? ''); ?></li>
            </ul>
        </div>
        <div class="col-12 col-lg-6 mb-3">
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/applications.labels.project', [], false); ?></li>
                <li class="list-group-item fw-bold text-break" style="white-space: pre-wrap;"><?= esc($application->project_name ?? ''); ?></li>
            </ul>
        </div>
        <?php if (trim($application->description ?? '') !== ''): ?>
        <div class="col-12 mb-3">
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/applications.labels.description', [], false); ?></li>
                <li class="list-group-item fw-bold text-break" style="white-space: pre-wrap;"><?= esc($application->description ?? ''); ?></li>
            </ul>
        </div>
        <?php endif; ?>
        <div class="col-12 col-lg-6 mb-3">
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/applications.labels.localPath', [], false); ?></li>
                <li class="list-group-item fw-bold text-break" style="white-space: pre-wrap;"><?= esc($application->local_path ?? ''); ?></li>
            </ul>
        </div>
        <div class="col-12 col-lg-6 mb-3">
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/applications.labels.localUrl', [], false); ?></li>
                <li class="list-group-item fw-bold text-break">
                    <?php $applicationUrl = $application->local_url ?? ''; ?>
                    <?php if(filter_var($applicationUrl, FILTER_VALIDATE_URL) !== false && in_array(strtolower((string) parse_url($applicationUrl, PHP_URL_SCHEME)), ['http', 'https'], true)): ?>
                        <a href="<?= esc($applicationUrl, 'attr'); ?>" target="_blank" rel="noopener noreferrer"><?= esc($applicationUrl); ?></a>
                    <?php else: ?>
                        <?= esc($applicationUrl); ?>
                    <?php endif; ?>
                </li>
            </ul>
        </div>
        <div class="col-12 col-lg-6 mb-3">
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/applications.labels.productionUrl', [], false); ?></li>
                <li class="list-group-item fw-bold text-break">
                    <?php $applicationUrl = $application->production_url ?? ''; ?>
                    <?php if(filter_var($applicationUrl, FILTER_VALIDATE_URL) !== false && in_array(strtolower((string) parse_url($applicationUrl, PHP_URL_SCHEME)), ['http', 'https'], true)): ?>
                        <a href="<?= esc($applicationUrl, 'attr'); ?>" target="_blank" rel="noopener noreferrer"><?= esc($applicationUrl); ?></a>
                    <?php else: ?>
                        <?= esc($applicationUrl); ?>
                    <?php endif; ?>
                </li>
            </ul>
        </div>
        <div class="col-12 col-lg-6 mb-3">
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/applications.labels.executeName', [], false); ?></li>
                <li class="list-group-item fw-bold text-break" style="white-space: pre-wrap;"><?= esc($application->execute_name ?? ''); ?></li>
            </ul>
        </div>
    </div>
</div>
