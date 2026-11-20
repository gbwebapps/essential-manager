<div class="card-body">
    <div class="row">
        <div class="col-12 col-lg-6 mb-3">
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/projects.labels.name', [], false); ?></li>
                <li class="list-group-item fw-bold text-break" style="white-space: pre-wrap;"><?= esc($project->name ?? ''); ?></li>
            </ul>
        </div>
        <div class="col-12 col-lg-6 mb-3">
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/projects.labels.description', [], false); ?></li>
                <li class="list-group-item fw-bold text-break" style="white-space: pre-wrap;"><?= esc($project->description ?? ''); ?></li>
            </ul>
        </div>
        <div class="col-12 col-lg-6 mb-3">
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/projects.labels.parentProject', [], false); ?></li>
                <li class="list-group-item fw-bold text-break" style="white-space: pre-wrap;"><?= esc($project->parent_project_name ?? ''); ?></li>
            </ul>
        </div>
        <div class="col-12 col-lg-6 mb-3">
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/projects.labels.localPath', [], false); ?></li>
                <li class="list-group-item fw-bold text-break" style="white-space: pre-wrap;"><?= esc($project->local_path ?? ''); ?></li>
            </ul>
        </div>
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

        <div class="col-12 col-lg-6 mb-3">
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/projects.labels.executeName', [], false); ?></li>
                <li class="list-group-item fw-bold text-break" style="white-space: pre-wrap;"><?= esc($project->execute_name ?? ''); ?></li>
            </ul>
        </div>
    </div>
</div>
