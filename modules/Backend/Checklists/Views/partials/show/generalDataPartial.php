<div class="card-body">
    <div class="mb-3">
        <ul class="list-group list-group-flush">
            <li class="list-group-item"><i class="fa-solid fa-tag fa-fw me-1" aria-hidden="true"></i><?= ui_text('backend/checklists.labels.name', [], false); ?></li>
            <li class="list-group-item fw-bold text-break"><?= esc($checklist->name); ?></li>
        </ul>
    </div>
    <?php if (trim((string) ($checklist->description ?? '')) !== ''): ?>
        <div class="mb-3">
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><i class="fa-solid fa-align-left fa-fw me-1" aria-hidden="true"></i><?= ui_text('backend/checklists.labels.description', [], false); ?></li>
                <li class="list-group-item text-break" style="white-space: pre-wrap;"><?= esc($checklist->description); ?></li>
            </ul>
        </div>
    <?php endif; ?>
    <div class="mb-2 fw-bold"><?= ui_text('backend/checklists.labels.steps', [], false); ?></div>
    <ol class="mb-0 ps-4">
        <?php foreach ($checklist->steps as $step): ?><li class="py-1 text-break"><?= esc($step->title); ?></li><?php endforeach; ?>
    </ol>
</div>
