<?php
    $draftMessages = [];
    foreach (['notice', 'unnamedApplication', 'addButton', 'updateButton', 'updatedFeedback', 'removeConfirm', 'noTasks', 'noNotes'] as $draftKey):
        $draftMessages[$draftKey] = lang('backend/applications.draft.' . $draftKey);
    endforeach;
    $draftMessages['edit'] = lang('backend/applications.actions.edit');
    $draftMessages['remove'] = lang('backend/applications.actions.softDelete');
    $draftMessages['reloadError'] = lang('backend/applications.related.reloadError');
    $draftMessages['stepsProgress'] = lang('backend/tasks.labels.stepsProgress');
    foreach (['todo', 'in_progress', 'completed'] as $draftStatus):
        $draftMessages['status_' . $draftStatus] = lang('backend/tasks.statuses.' . $draftStatus);
    endforeach;
?>
<div data-application-draft-root data-draft-messages="<?= esc(json_encode($draftMessages, JSON_THROW_ON_ERROR), 'attr'); ?>">
    <?= $this->include(\Modules\Backend\Applications\Config\Views::RELATED_PANELS_PARTIAL); ?>
</div>
