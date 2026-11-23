<div class="card-body">
    <div class="row">
        <?php if (trim((string) ($project->name ?? '')) !== ''): ?>
        <div class="col-12 col-lg-6 mb-3">
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/projects.labels.name', [], false); ?></li>
                <li class="list-group-item fw-bold text-break" style="white-space: pre-wrap;"><?= esc($project->name ?? ''); ?></li>
            </ul>
        </div>
        <?php endif; ?>
        <?php if (trim((string) ($project->parent_project_name ?? '')) !== ''): ?>
        <div class="col-12 col-lg-6 mb-3">
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/projects.labels.parentProject', [], false); ?></li>
                <li class="list-group-item fw-bold text-break" style="white-space: pre-wrap;"><?= esc($project->parent_project_name ?? ''); ?></li>
            </ul>
        </div>
        <?php endif; ?>
        <?php if (trim($project->description ?? '') !== ''): ?>
        <div class="col-12 mb-3">
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/projects.labels.description', [], false); ?></li>
                <li class="list-group-item fw-bold text-break" style="white-space: pre-wrap;"><?= esc($project->description ?? ''); ?></li>
            </ul>
        </div>
        <?php endif; ?>
        <?php if (trim((string) ($project->local_path ?? '')) !== ''): ?>
        <div class="col-12 col-lg-6 mb-3">
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/projects.labels.localPath', [], false); ?></li>
                <li class="list-group-item fw-bold text-break" style="white-space: pre-wrap;"><?= esc($project->local_path ?? ''); ?></li>
            </ul>
        </div>
        <?php endif; ?>
        <?php if (trim((string) ($project->local_url ?? '')) !== ''): ?>
        <div class="col-12 col-lg-6 mb-3">
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/projects.labels.localUrl', [], false); ?></li>
                <li class="list-group-item fw-bold text-break">
                    <?php $projectUrl = $project->local_url ?? ''; ?>
                    <?php if(filter_var($projectUrl, FILTER_VALIDATE_URL) !== false && in_array(strtolower((string) parse_url($projectUrl, PHP_URL_SCHEME)), ['http', 'https'], true)): ?>
                        <a href="<?= esc($projectUrl, 'attr'); ?>" target="_blank" rel="noopener noreferrer"><?= esc($projectUrl); ?></a>
                    <?php else: ?>
                        <?= esc($projectUrl); ?>
                    <?php endif; ?>
                </li>
            </ul>
        </div>
        <?php endif; ?>
        <?php if (trim((string) ($project->production_url ?? '')) !== ''): ?>
        <div class="col-12 col-lg-6 mb-3">
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/projects.labels.productionUrl', [], false); ?></li>
                <li class="list-group-item fw-bold text-break">
                    <?php $projectUrl = $project->production_url ?? ''; ?>
                    <?php if(filter_var($projectUrl, FILTER_VALIDATE_URL) !== false && in_array(strtolower((string) parse_url($projectUrl, PHP_URL_SCHEME)), ['http', 'https'], true)): ?>
                        <a href="<?= esc($projectUrl, 'attr'); ?>" target="_blank" rel="noopener noreferrer"><?= esc($projectUrl); ?></a>
                    <?php else: ?>
                        <?= esc($projectUrl); ?>
                    <?php endif; ?>
                </li>
            </ul>
        </div>
        <?php endif; ?>
        <?php if (trim((string) ($project->execute_name ?? '')) !== ''): ?>
        <div class="col-12 col-lg-6 mb-3">
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/projects.labels.executeName', [], false); ?></li>
                <li class="list-group-item fw-bold text-break" style="white-space: pre-wrap;"><?= esc($project->execute_name ?? ''); ?></li>
            </ul>
        </div>
        <?php endif; ?>
    </div>
</div>
