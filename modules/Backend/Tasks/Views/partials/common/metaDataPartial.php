<div class="card-body">
    <div class="row">
        <?php if( ! empty($task->created_at)): ?>
            <div class="col-12 col-lg-6">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/tasks.labels.createdAt', [], false); ?></li>
                    <li class="list-group-item fw-bold"><?= ui_date(esc($task->created_at), 'conversational'); ?></li>
                </ul>
            </div>
        <?php endif; ?>
        <?php if( ! empty($task->updated_at)): ?>
            <div class="col-12 col-lg-6">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/tasks.labels.updatedAt', [], false); ?></li>
                    <li class="list-group-item fw-bold"><?= ui_date(esc($task->updated_at), 'conversational'); ?></li>
                </ul>
            </div>
        <?php endif; ?>
        <?php if( ! empty($task->completed_at)): ?>
            <div class="col-12 col-lg-6">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/tasks.labels.completedAt', [], false); ?></li>
                    <li class="list-group-item fw-bold"><?= ui_date(esc($task->completed_at), 'conversational'); ?></li>
                </ul>
            </div>
        <?php endif; ?>
        <?php if( ! empty($task->restored_at)): ?>
            <div class="col-12 col-lg-6">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/tasks.labels.restoredAt', [], false); ?></li>
                    <li class="list-group-item fw-bold"><?= ui_date(esc($task->restored_at), 'conversational'); ?></li>
                </ul>
            </div>
        <?php endif; ?>
        <?php if( ! empty($task->deleted_at)): ?>
            <div class="col-12 col-lg-6">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/tasks.labels.deletedAt', [], false); ?></li>
                    <li class="list-group-item fw-bold"><?= ui_date(esc($task->deleted_at), 'conversational'); ?></li>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</div>
