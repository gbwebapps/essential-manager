<?php
    $archived = ($task->archived_at ?? null) !== null;
    $canArchive = \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, 'tasks_archive')
        && (\App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, 'tasks_show') || \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, 'tasks_edit'));
?>
<?php if ($canArchive && $task->deleted_at === null && ($task->owner_deleted_at ?? null) === null): ?>
<form novalidate class="task-action m-0 p-0" data-endpoint="<?= $archived?'unarchive':'archive'; ?>" data-confirm="<?= esc(lang('backend/tasks.archive.'.($archived?'confirmRecover':'confirmArchive')),'attr'); ?>">
    <input type="hidden" name="uuid" value="<?= esc($task->uuid,'attr'); ?>">
    <button type="submit" class="<?= esc($archiveButtonClass ?? 'btn btn-sm app-btn-secondary text-nowrap','attr'); ?>"><i class="fa-solid fa-<?= $archived?'box-open':'box-archive'; ?> fa-fw" aria-hidden="true"></i> <?= ui_text('backend/tasks.archive.'.($archived?'recover':'action'),[],false); ?></button>
</form>
<?php endif; ?>
