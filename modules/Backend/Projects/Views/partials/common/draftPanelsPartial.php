<?php
    $draftMessages = [];
    foreach (['notice', 'unnamedProject', 'addButton', 'updateButton', 'updatedFeedback', 'removeConfirm', 'noTasks', 'noNotes'] as $draftKey):
        $draftMessages[$draftKey] = lang('backend/projects.draft.' . $draftKey);
    endforeach;
    $draftMessages['reloadError'] = lang('backend/projects.related.reloadError');
?>
<div data-project-draft-root data-draft-messages="<?= esc(json_encode($draftMessages, JSON_THROW_ON_ERROR), 'attr'); ?>">
    <?= $this->include(\Modules\Backend\Projects\Config\Views::RELATED_PANELS_PARTIAL); ?>
</div>
