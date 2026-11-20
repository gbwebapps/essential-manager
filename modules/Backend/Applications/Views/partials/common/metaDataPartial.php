<div class="card-body">
    <div class="row">
        <?php if( ! empty($application->created_at)): ?>
            <div class="col-12 col-lg-6">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/applications.labels.createdAt', [], false); ?></li>
                    <li class="list-group-item fw-bold"><?= ui_date(esc($application->created_at), 'conversational'); ?></li>
                </ul>
            </div>
        <?php endif; ?>
        <?php if( ! empty($application->updated_at)): ?>
            <div class="col-12 col-lg-6">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/applications.labels.updatedAt', [], false); ?></li>
                    <li class="list-group-item fw-bold"><?= ui_date(esc($application->updated_at), 'conversational'); ?></li>
                </ul>
            </div>
        <?php endif; ?>
        <?php if( ! empty($application->restored_at)): ?>
            <div class="col-12 col-lg-6">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/applications.labels.restoredAt', [], false); ?></li>
                    <li class="list-group-item fw-bold"><?= ui_date(esc($application->restored_at), 'conversational'); ?></li>
                </ul>
            </div>
        <?php endif; ?>
        <?php if( ! empty($application->deleted_at)): ?>
            <div class="col-12 col-lg-6">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/applications.labels.deletedAt', [], false); ?></li>
                    <li class="list-group-item fw-bold"><?= ui_date(esc($application->deleted_at), 'conversational'); ?></li>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</div>
