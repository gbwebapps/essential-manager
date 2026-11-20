<?php
    $draftMessages = [];
    foreach (['notice', 'unnamedProject', 'addButton', 'updateButton', 'updatedFeedback', 'removeConfirm', 'noTasks', 'noNotes'] as $draftKey):
        $draftMessages[$draftKey] = lang('backend/projects.draft.' . $draftKey);
    endforeach;
    $draftMessages['edit'] = lang('backend/projects.actions.edit');
    $draftMessages['remove'] = lang('backend/projects.actions.softDelete');
    $draftMessages['reloadError'] = lang('backend/projects.related.reloadError');
    $draftMessages['stepsProgress'] = lang('backend/tasks.labels.stepsProgress');
    foreach (['todo', 'in_progress', 'completed'] as $draftStatus):
        $draftMessages['status_' . $draftStatus] = lang('backend/tasks.statuses.' . $draftStatus);
    endforeach;
?>
<div data-project-draft-root data-draft-messages="<?= esc(json_encode($draftMessages, JSON_THROW_ON_ERROR), 'attr'); ?>">
    <?= $this->include(\Modules\Backend\Projects\Config\Views::RELATED_PANELS_PARTIAL); ?>
</div>
