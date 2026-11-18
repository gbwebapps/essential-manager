<div class="card-body">
    <div class="row">
        <?php if( ! empty($project->created_at)): ?>
            <div class="col-12 col-lg-6">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/projects.labels.createdAt', [], false); ?></li>
                    <li class="list-group-item fw-bold"><?= ui_date(esc($project->created_at), 'conversational'); ?></li>
                </ul>
            </div>
        <?php endif; ?>
        <?php if( ! empty($project->updated_at)): ?>
            <div class="col-12 col-lg-6">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/projects.labels.updatedAt', [], false); ?></li>
                    <li class="list-group-item fw-bold"><?= ui_date(esc($project->updated_at), 'conversational'); ?></li>
                </ul>
            </div>
        <?php endif; ?>
        <?php if( ! empty($project->restored_at)): ?>
            <div class="col-12 col-lg-6">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/projects.labels.restoredAt', [], false); ?></li>
                    <li class="list-group-item fw-bold"><?= ui_date(esc($project->restored_at), 'conversational'); ?></li>
                </ul>
            </div>
        <?php endif; ?>
        <?php if( ! empty($project->deleted_at)): ?>
            <div class="col-12 col-lg-6">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/projects.labels.deletedAt', [], false); ?></li>
                    <li class="list-group-item fw-bold"><?= ui_date(esc($project->deleted_at), 'conversational'); ?></li>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</div>
