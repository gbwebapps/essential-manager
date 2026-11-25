<?php $ownerInputId = $fieldPrefix . '-owner'; ?>
<div class="mb-2">
    <label for="<?= esc($ownerInputId, 'attr'); ?>" class="form-label"><i class="fa-solid fa-folder-tree fa-fw me-1" aria-hidden="true"></i><?= ui_text('backend/tasks.labels.context', [], false); ?></label>
    <input type="text" id="<?= esc($ownerInputId, 'attr'); ?>" class="form-control" value="<?= esc($taskOwnerName ?? '', 'attr'); ?>" data-task-owner-name disabled>
    <div class="small pt-1" aria-hidden="true">&nbsp;</div>
</div>
