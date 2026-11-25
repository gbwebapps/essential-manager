<?php
    $draftMessages = [];
    foreach (['notice', 'unnamedApplication', 'addButton', 'updateButton', 'updatedFeedback', 'removeConfirm', 'noTasks', 'noNotes'] as $draftKey):
        $draftMessages[$draftKey] = lang('backend/applications.draft.' . $draftKey);
    endforeach;
    $draftMessages['reloadError'] = lang('backend/applications.related.reloadError');
?>
<div data-application-draft-root data-draft-messages="<?= esc(json_encode($draftMessages, JSON_THROW_ON_ERROR), 'attr'); ?>">
    <?= $this->include(\Modules\Backend\Applications\Config\Views::RELATED_PANELS_PARTIAL); ?>
</div>
