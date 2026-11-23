<?php $notePreview=trim(preg_replace('/\s+/u',' ',$studioNote->content));$noteId='studio-note-'.$studioNote->uuid; ?>
<article class="card studio-panel studio-note-panel" data-studio-note="<?= esc($studioNote->uuid,'attr'); ?>">
    <div class="card-header studio-panel-heading" data-studio-note-heading>
        <button type="button" class="studio-task-toggle" data-studio-note-toggle aria-expanded="false" aria-controls="<?= esc($noteId,'attr'); ?>">
            <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
            <span class="studio-note-title"><span class="studio-task-title fw-bold"><?= esc(mb_strlen($notePreview)>50?mb_substr($notePreview,0,49).'…':$notePreview); ?></span><time class="small app-text-muted" datetime="<?= esc($studioNote->created_at,'attr'); ?>"><?= esc(date('d/m/Y H:i',strtotime($studioNote->created_at))); ?></time></span>
        </button>
        <div class="studio-panel-actions">
            <?php if ($canEditNotes): ?><button type="button" class="btn btn-link btn-sm task-record-link" data-bs-toggle="modal" data-bs-target="#studio-note-modal" data-studio-note-record="<?= esc(json_encode(['uuid'=>$studioNote->uuid,'content'=>$studioNote->content],JSON_THROW_ON_ERROR),'attr'); ?>"><i class="fa-solid fa-pen me-1" aria-hidden="true"></i><?= ui_text('backend/tasks.actions.edit',[],false); ?></button><?php endif; ?>
            <?php if ($canDeleteNotes): ?><form novalidate method="post" data-studio-delete-note data-confirm="<?= esc(lang('backend/tasks.messages.confirmDeleteNote'),'attr'); ?>"><input type="hidden" name="uuid" value="<?= esc($studioNote->uuid,'attr'); ?>"><button type="submit" class="btn btn-link btn-sm task-record-link app-text-danger"><i class="fa-solid fa-trash-can me-1" aria-hidden="true"></i><?= ui_text('backend/tasks.actions.hardDelete',[],false); ?></button></form><?php endif; ?>
        </div>
    </div>
    <div id="<?= esc($noteId,'attr'); ?>" class="collapse"><div class="card-body studio-note-body"><p class="studio-note-content text-break"><?= esc($studioNote->content); ?></p></div></div>
</article>
