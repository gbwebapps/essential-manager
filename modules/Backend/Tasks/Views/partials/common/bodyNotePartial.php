<?php
    $preview=trim(preg_replace('/\s+/u',' ',$note->content));
    $noteCollapseId='owner-note-'.($note->uuid ?? $noteKey);
?>
<div class="owner-note" <?= ! empty($note->uuid) ? 'data-owner-note="'.esc($note->uuid,'attr').'"' : ''; ?>>
    <div class="task-note-header d-flex align-items-center gap-1">
        <button type="button" id="<?= esc($noteCollapseId, 'attr'); ?>-toggle" class="task-related-toggle owner-note-title collapsed" data-bs-toggle="collapse" data-bs-target="#<?= esc($noteCollapseId, 'attr'); ?>" aria-controls="<?= esc($noteCollapseId, 'attr'); ?>" aria-expanded="false">
            <i class="fa-solid fa-chevron-right me-2" aria-hidden="true"></i><span class="fw-bold text-truncate"><?= esc(mb_strlen($preview)>50 ? mb_substr($preview,0,49).'…' : $preview); ?></span>
        </button>
        <button type="button" class="btn btn-link btn-sm task-record-link text-nowrap me-1 collapsed" data-bs-toggle="collapse" data-bs-target="#<?= esc($noteCollapseId, 'attr'); ?>" aria-controls="<?= esc($noteCollapseId, 'attr'); ?>" aria-expanded="false"><?= ui_text('backend/tasks.actions.readNote', [], false); ?></button>
    </div>
    <div id="<?= esc($noteCollapseId, 'attr'); ?>" class="collapse" data-note-collapse role="region" aria-labelledby="<?= esc($noteCollapseId, 'attr'); ?>-toggle">
        <div class="owner-note-content"><?= esc($note->content); ?></div>
    </div>
</div>
