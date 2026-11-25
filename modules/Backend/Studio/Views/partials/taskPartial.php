<?php
    $expanded = in_array($studioTask->uuid, $openedTasks ?? [], true) && $canReadTaskDetails;
    $panelId = 'studio-task-' . $studioTask->uuid;
    $task = $studioTask;
    if ($expanded && ! isset($task->steps)): $task = model(\Modules\Backend\Studio\Models\StudioModel::class)->getTask($studioTask->uuid); endif;
?>
<article class="card studio-panel studio-task-panel <?= ($currentTask->uuid ?? null) === $studioTask->uuid ? 'studio-current-task' : ''; ?>" data-studio-task="<?= esc($studioTask->uuid, 'attr'); ?>" data-task-title="<?= esc($studioTask->title, 'attr'); ?>">
    <div class="card-header studio-panel-heading">
        <button type="button" class="studio-task-toggle" data-studio-toggle <?= ! $canReadTaskDetails ? 'disabled' : ''; ?> aria-expanded="<?= $expanded ? 'true' : 'false'; ?>" aria-controls="<?= esc($panelId, 'attr'); ?>">
            <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
            <span class="studio-task-heading-text"><span class="studio-task-title fw-bold"><?= esc(mb_strlen($studioTask->title) > 50 ? mb_substr($studioTask->title, 0, 49) . '…' : $studioTask->title); ?></span><span class="studio-task-meta">
                <?= view(\Modules\Backend\Tasks\Config\Views::STATUS_PARTIAL, ['task' => $studioTask, 'currentTask' => $currentTask ?? null], ['saveData' => false]); ?>
                <?= view(\Modules\Backend\Tasks\Config\Views::PROGRESS_PARTIAL, ['completedSteps' => $studioTask->completed_count, 'totalSteps' => $studioTask->step_count], ['saveData' => false]); ?>
                <?php if ($canReadNotes): ?><span><span class="badge task-notes-count"><?= (int) $studioTask->note_count; ?></span> <?= ui_text('backend/studio.notes', [], false); ?></span><?php endif; ?>
                <time datetime="<?= esc($studioTask->created_at, 'attr'); ?>"><?= esc(date('d/m/Y H:i', strtotime($studioTask->created_at))); ?></time>
            </span></span>
        </button>
        <div class="studio-panel-actions">
            <?= view('Modules\Backend\Tasks\Views\partials\common\archiveActionPartial', ['archiveButtonClass'=>'btn btn-link btn-sm task-record-link','task'=>$studioTask,'currentAdmin'=>$currentAdmin], ['saveData'=>false]); ?>
            <?php if ($studioTask->archived_at === null && $canReadTaskDetails && ($currentTask->uuid ?? null) !== $studioTask->uuid && $studioTask->status !== 'completed' && \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin, 'tasks_set_current')): ?>
                <form novalidate class="task-action" data-endpoint="setCurrent">
                    <input type="hidden" name="uuid" value="<?= esc($studioTask->uuid, 'attr'); ?>">
                    <button type="submit" class="btn btn-link btn-sm task-record-link"><i class="fa-solid fa-play me-1" aria-hidden="true"></i><?= ui_text('backend/tasks.actions.work', [], false); ?></button>
                </form>
            <?php endif; ?>

            <?php if (\App\Libraries\Backend\AuthorizationClass::allows($currentAdmin, 'tasks_show')): ?><a class="btn btn-link btn-sm task-record-link" href="<?= esc(base_url('backend/studio/tasks/show/' . $studioTask->uuid), 'attr'); ?>"><i class="fa-solid fa-eye me-1" aria-hidden="true"></i><?= ui_text('backend/tasks.actions.show', [], false); ?></a><?php endif; ?>
            <?php if ($studioTask->archived_at === null && $canEditTasks): ?><a class="btn btn-link btn-sm task-record-link" href="<?= esc(base_url('backend/studio/tasks/edit/' . $studioTask->uuid), 'attr'); ?>"><i class="fa-solid fa-pen me-1" aria-hidden="true"></i><?= ui_text('backend/tasks.actions.edit', [], false); ?></a><?php endif; ?>
            <?php if (($canDeleteTasks ?? false)): ?><form novalidate method="post" data-studio-delete-task data-confirm="<?= esc(lang('backend/studio.confirmTaskDelete'), 'attr'); ?>"><input type="hidden" name="uuid" value="<?= esc($studioTask->uuid, 'attr'); ?>"><button type="submit" class="btn btn-link btn-sm task-record-link app-text-danger"><i class="fa-solid fa-trash-can me-1" aria-hidden="true"></i><?= ui_text('backend/tasks.actions.softDelete', [], false); ?></button></form><?php endif; ?>
        </div>
    </div>
    <?php if ( ! empty($studioTask->reference)): ?><div class="px-3 py-1 border-top"><?= view('Modules\Backend\Studio\Views\partials\referencePartial', ['task' => $studioTask, 'currentAdmin' => $currentAdmin], ['saveData' => false]); ?></div><?php endif; ?>
    <div id="<?= esc($panelId, 'attr'); ?>" class="collapse <?= $expanded ? 'show' : ''; ?>" data-studio-task-content data-loaded="<?= $expanded ? 'true' : 'false'; ?>">
        <?php if ($expanded && $task): ?>
            <?= view(\Modules\Backend\Studio\Config\Views::TASK_BODY_PARTIAL, ['task' => $task, 'currentAdmin' => $currentAdmin, 'canReadNotes' => $canReadNotes], ['saveData' => false]); ?>
        <?php endif; ?>
    </div>
</article>
