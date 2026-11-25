<div class="card-body">
    <div class="row">
        <?php if( ! empty($checklist->created_at)): ?>
            <div class="col-12 col-lg-6">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/checklists.labels.createdAt', [], false); ?></li>
                    <li class="list-group-item fw-bold"><?= ui_date(esc($checklist->created_at), 'conversational'); ?></li>
                </ul>
            </div>
        <?php endif; ?>
        <?php if( ! empty($checklist->updated_at)): ?>
            <div class="col-12 col-lg-6">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/checklists.labels.updatedAt', [], false); ?></li>
                    <li class="list-group-item fw-bold"><?= ui_date(esc($checklist->updated_at), 'conversational'); ?></li>
                </ul>
            </div>
        <?php endif; ?>
        <?php if( ! empty($checklist->restored_at)): ?>
            <div class="col-12 col-lg-6">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/checklists.labels.restoredAt', [], false); ?></li>
                    <li class="list-group-item fw-bold"><?= ui_date(esc($checklist->restored_at), 'conversational'); ?></li>
                </ul>
            </div>
        <?php endif; ?>
        <?php if( ! empty($checklist->deleted_at)): ?>
            <div class="col-12 col-lg-6">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/checklists.labels.deletedAt', [], false); ?></li>
                    <li class="list-group-item fw-bold"><?= ui_date(esc($checklist->deleted_at), 'conversational'); ?></li>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</div>
