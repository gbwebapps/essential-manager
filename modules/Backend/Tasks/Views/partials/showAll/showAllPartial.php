<?php
    $canEditTasks = \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, 'tasks_edit');
    $canShowTasks = \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, 'tasks_show');
    $canDeleteTasks = \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, 'tasks_delete');
    $canWorkOnTasks = \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, 'tasks_set_current') && ($canShowTasks || $canEditTasks);
    $canRestoreTasks = \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, 'tasks_restore');
?>

<div class="card-body py-0">
    <div class="row">
        <div class="col-md-12">
            <?php if(isset($data['records']) && count($data['records'])): ?>

                <?= $this->include('backend/template/paginationView'); ?>

                <div class="table-responsive border-top">
                    <?php $icon = ($posts['order'] === 'desc') ? '<i class="fa-solid fa-arrow-down"></i>' : '<i class="fa-solid fa-arrow-up"></i>'; ?>
                    <div class="lastItemPage" data-lastitempage="<?= esc((string) $data['lastItemPage'], 'attr'); ?>"></div>
                    <table class="table table-condensed mb-0">
                        <thead>
                            <tr class="sorting">
                                <th style="width: 2.5%;">&nbsp;</th>
                                <th>
                                    <a class="sort" href="#" data-column="title" data-order="<?= (($posts['order'] === 'desc' && $posts['column'] === 'title') ? 'asc' : 'desc'); ?>">
                                        <?= ui_text('backend/tasks.labels.title', [], false); ?> <?= (($posts['column'] === 'title') ? '&nbsp;' . $icon : ''); ?>
                                    </a>
                                </th>
                                <th class="text-nowrap"><?= ui_text('backend/tasks.labels.context', [], false); ?></th>
                                <th class="text-center text-nowrap"><?= ui_text('backend/tasks.labels.status', [], false); ?></th>
                                <th class="text-center text-nowrap"><?= ui_text('backend/tasks.labels.steps', [], false); ?></th>
                                <th class="text-center text-nowrap"><?= ui_text('backend/tasks.related.notes', [], false); ?></th>
                                <th style="width: 1%;" class="text-nowrap text-end"><?= ui_text('backend/tasks.buttons.actions', [], false); ?></th>
                            </tr>
                        </thead>
                        <tbody id="tasksBody">
                            <?php foreach($data['records'] as $task): ?>
                                <?php $isTrashed = ( ! is_null($task->deleted_at)); ?>
                                <tr class="border-end border-start" data-task-row="<?= esc($task->uuid, 'attr'); ?>" data-task-current="<?= ($currentTask->uuid ?? null) === $task->uuid ? 'true' : 'false'; ?>">
                                    <td class="align-middle fw-bold">
                                        <button <?= ui_binding('backend/tasks.buttons.showSystemDetails', 'title', [], true); ?> class="toggle-meta-btn btn btn-sm btn-link p-0 me-1 app-text shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#meta-<?= esc($task->uuid, 'attr'); ?>" aria-controls="meta-<?= esc($task->uuid, 'attr'); ?>" aria-expanded="false" title="<?= esc(lang('backend/tasks.buttons.showSystemDetails'), 'attr'); ?>">
                                            <i class="fa-solid fa-chevron-right"></i>
                                        </button>
                                    </td>
                                    <td class="align-middle fw-bold"><span class="d-block text-truncate" style="width: 13rem; max-width: 30vw;" title="<?= esc($task->title, 'attr'); ?>"><?= esc(mb_strlen($task->title) > 50 ? mb_substr($task->title, 0, 49) . '…' : $task->title); ?></span></td>
                                    <?php $contextLabel = ($task->project_name ?? '') . ($task->application_name !== null ? ' / ' . $task->application_name : ''); ?>
                                    <td class="align-middle text-nowrap"><span class="d-block text-truncate" style="max-width: 15rem;" title="<?= esc($contextLabel, 'attr'); ?>"><?= esc($contextLabel); ?></span></td>
                                    <td class="align-middle text-center text-nowrap">
                                        <span class="task-list-status">
                                            <span class="task-list-pin-slot"><?php if (($currentTask->uuid ?? null) === $task->uuid): ?><span class="task-current-pin" role="img" aria-label="<?= esc(lang('backend/tasks.labels.current'), 'attr'); ?>" title="<?= esc(lang('backend/tasks.labels.current'), 'attr'); ?>"><i class="fa-solid fa-thumbtack" aria-hidden="true"></i></span><?php endif; ?></span>
                                            <?= view(\Modules\Backend\Tasks\Config\Views::STATUS_PARTIAL, ['task' => $task, 'currentTask' => $currentTask ?? null], ['saveData' => false]); ?>
                                        </span>
                                    </td>
                                    <td class="align-middle text-center text-nowrap"><?= view(\Modules\Backend\Tasks\Config\Views::PROGRESS_PARTIAL, ['completedSteps' => $task->completed_count, 'totalSteps' => $task->step_count], ['saveData' => false]); ?></td>
                                    <td class="align-middle text-center"><span class="badge task-notes-count"><?= (int) ($task->note_count ?? 0); ?></span></td>
                                    <!-- Azioni del task, allineate a destra -->
                                    <td class="align-middle text-end">
                                        <div class="d-flex flex-row flex-nowrap justify-content-end align-items-center gap-1">
                                            <?php if( ! $isTrashed): ?>
                                                <?= view('Modules\Backend\Tasks\Views\partials\common\archiveActionPartial', ['task'=>$task,'currentAdmin'=>$currentAdmin], ['saveData'=>false]); ?>
                                                <?php if($task->archived_at === null && $canWorkOnTasks && $task->owner_deleted_at === null && $task->status !== 'completed' && ($currentTask->uuid ?? null) !== $task->uuid): ?>
                                                    <form novalidate class="task-action m-0 p-0" data-endpoint="setCurrent">
                                                        <input type="hidden" name="uuid" value="<?= esc($task->uuid, 'attr'); ?>">
                                                        <button type="submit" class="btn btn-link btn-sm task-record-link text-nowrap"><i class="fa-solid fa-play fa-fw" aria-hidden="true"></i> <?= ui_text('backend/tasks.actions.work', [], false); ?></button>
                                                    </form>
                                                <?php endif; ?>
                                                <?php if($task->archived_at === null && $canEditTasks && $task->owner_deleted_at === null): ?>
                                                    <a href="<?= esc(base_url('backend/tasks/edit/' . rawurlencode($task->uuid)), 'attr'); ?>" class="btn btn-sm app-btn-secondary text-nowrap">
                                                        <i class="fa-solid fa-pen fa-fw"></i> <?= ui_text('backend/tasks.actions.edit', [], false); ?>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if($canShowTasks): ?>
                                                    <a href="<?= esc(base_url('backend/tasks/show/' . rawurlencode($task->uuid)), 'attr'); ?>" class="btn btn-sm app-btn-secondary text-nowrap">
                                                        <i class="fa-solid fa-eye fa-fw"></i> <?= ui_text('backend/tasks.actions.show', [], false); ?>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if($canDeleteTasks): ?>
                                                    <form novalidate <?= ui_binding('backend/tasks.messages.areYouSureSoftDelete', 'data-message', [$task->title], true); ?> class="deleteRecord m-0 p-0" data-controller="tasks" data-message="<?= esc(sprintf(lang('backend/tasks.messages.areYouSureSoftDelete'), $task->title), 'attr'); ?>">
                                                        <input type="hidden" name="uuid" value="<?= esc($task->uuid, 'attr'); ?>">
                                                        <button type="submit" class="btn btn-sm app-btn-secondary text-nowrap">
                                                            <i class="fa-solid fa-trash-can fa-fw" aria-hidden="true"></i> <?= ui_text('backend/tasks.actions.softDelete', [], false); ?>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <?php if($canRestoreTasks && $task->owner_deleted_at === null): ?>
                                                <form novalidate <?= ui_binding('backend/tasks.messages.areYouSureRestoreDelete', 'data-message', [$task->title], true); ?> class="restoreRecord m-0 p-0" data-controller="tasks" data-message="<?= esc(sprintf(lang('backend/tasks.messages.areYouSureRestoreDelete'), $task->title), 'attr'); ?>">
                                                    <input type="hidden" name="uuid" value="<?= esc($task->uuid, 'attr'); ?>">
                                                    <button type="submit" class="btn btn-sm app-btn-secondary text-nowrap">
                                                        <i class="fa-solid fa-trash-arrow-up fa-fw"></i> <?= ui_text('backend/tasks.actions.restoreDelete', [], false); ?>
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                                <?php if($canDeleteTasks): ?>
                                                <form novalidate <?= ui_binding('backend/tasks.messages.areYouSureHardDelete', 'data-message', [$task->title], true); ?> class="hardDeleteRecord m-0 p-0" data-controller="tasks" data-message="<?= esc(sprintf(lang('backend/tasks.messages.areYouSureHardDelete'), $task->title), 'attr'); ?>">
                                                    <input type="hidden" name="uuid" value="<?= esc($task->uuid, 'attr'); ?>">
                                                    <button type="submit" class="btn btn-sm app-btn-danger text-nowrap">
                                                        <i class="fa-solid fa-trash-can fa-fw" aria-hidden="true"></i> <?= ui_text('backend/tasks.actions.hardDelete', [], false); ?>
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="border-end border-start">
                                    <td colspan="7" class="p-0 border-0">
                                        <div class="collapse" id="meta-<?= esc($task->uuid, 'attr'); ?>">
                                            <div class="p-2 border-bottom app-bg-surface app-text small">
                                                <?php $hasDate = false; ?>
                                                <?php if( ! empty($task->created_at)): ?>
                                                    <?php if($hasDate): ?>&nbsp;&bull;&nbsp;<?php endif; ?>
                                                    <?= ui_text('backend/tasks.labels.createdAt', [], false); ?> <span class="fw-bold app-text-success"><?= ui_date(esc($task->created_at), 'conversational'); ?></span>
                                                    <?php $hasDate = true; ?>
                                                <?php endif; ?>
                                                <?php if( ! empty($task->updated_at)): ?>
                                                    <?php if($hasDate): ?>&nbsp;&bull;&nbsp;<?php endif; ?>
                                                    <?= ui_text('backend/tasks.labels.updatedAt', [], false); ?> <span class="fw-bold app-text-success"><?= ui_date(esc($task->updated_at), 'conversational'); ?></span>
                                                    <?php $hasDate = true; ?>
                                                <?php endif; ?>
                                                <?php if( ! empty($task->restored_at)): ?>
                                                    <?php if($hasDate): ?>&nbsp;&bull;&nbsp;<?php endif; ?>
                                                    <?= ui_text('backend/tasks.labels.restoredAt', [], false); ?> <span class="fw-bold app-text-success"><?= ui_date(esc($task->restored_at), 'conversational'); ?></span>
                                                    <?php $hasDate = true; ?>
                                                <?php endif; ?>
                                                <?php if( ! empty($task->deleted_at)): ?>
                                                    <?php if($hasDate): ?>&nbsp;&bull;&nbsp;<?php endif; ?>
                                                    <?= ui_text('backend/tasks.labels.deletedAt', [], false); ?> <span class="fw-bold app-text-danger"><?= ui_date(esc($task->deleted_at), 'conversational'); ?></span>
                                                    <?php $hasDate = true; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?= $this->include('backend/template/paginationView'); ?>

            <?php else: ?>
                <div class="text-center py-3 fw-bold"><?= ui_text('backend/tasks.messages.noTasksFound', [], false); ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>
