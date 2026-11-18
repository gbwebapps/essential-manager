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
                <li class="list-group-item fw-bold text-break" style="white-space: pre-wrap;"><?= esc($project->local_url ?? ''); ?></li>
            </ul>
        </div>
        <div class="col-12 col-lg-6 mb-3">
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/projects.labels.productionUrl', [], false); ?></li>
                <li class="list-group-item fw-bold text-break" style="white-space: pre-wrap;"><?= esc($project->production_url ?? ''); ?></li>
            </ul>
        </div>
        <div class="col-12 col-lg-6 mb-3">
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/projects.labels.executeName', [], false); ?></li>
                <li class="list-group-item fw-bold text-break" style="white-space: pre-wrap;"><?= esc($project->execute_name ?? ''); ?></li>
            </ul>
        </div>
        <?php if((int) $project->is_protected === 1): ?>
            <div class="col-12">
                <span class="badge app-bg-muted-subtle app-text-muted-emphasis border"><i class="fa-solid fa-lock me-1"></i><?= ui_text('backend/projects.labels.protected', [], false); ?></span>
            </div>
        <?php endif; ?>
    </div>
</div>
