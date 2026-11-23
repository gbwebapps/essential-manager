<?php
    $canEditApplications = \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, 'applications_edit');
    $canShowApplications = \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, 'applications_show');
    $canDeleteApplications = \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, 'applications_delete');
    $canRestoreApplications = \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, 'applications_restore');
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
                                    <a class="sort" href="#" data-column="name" data-order="<?= (($posts['order'] === 'desc' && $posts['column'] === 'name') ? 'asc' : 'desc'); ?>">
                                        <?= ui_text('backend/applications.labels.name', [], false); ?> <?= (($posts['column'] === 'name') ? '&nbsp;' . $icon : ''); ?>
                                    </a>
                                </th>
                                <th><?= ui_text('backend/applications.labels.project', [], false); ?></th>
                                <th class="text-center text-nowrap"><?= ui_text('backend/applications.related.tasks', [], false); ?></th>
                                <th class="text-center text-nowrap"><?= ui_text('backend/applications.related.notes', [], false); ?></th>
                                <th style="width: 1%;" class="text-nowrap text-end"><?= ui_text('backend/applications.buttons.actions', [], false); ?></th>
                            </tr>
                        </thead>
                        <tbody id="applicationsBody">
                            <?php foreach($data['records'] as $application): ?>
                                <?php $isTrashed = ( ! is_null($application->deleted_at)); ?>
                                <tr class="border-end border-start">
                                    <td class="align-middle fw-bold">
                                        <button <?= ui_binding('backend/applications.buttons.showSystemDetails', 'title', [], true); ?> class="toggle-meta-btn btn btn-sm btn-link p-0 me-1 app-text shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#meta-<?= esc($application->uuid, 'attr'); ?>" aria-controls="meta-<?= esc($application->uuid, 'attr'); ?>" aria-expanded="false" title="<?= esc(lang('backend/applications.buttons.showSystemDetails'), 'attr'); ?>">
                                            <i class="fa-solid fa-chevron-right"></i>
                                        </button>
                                    </td>
                                    <td class="align-middle fw-bold text-break"><?= esc($application->name); ?></td>
                                    <td class="align-middle text-break"><?= esc($application->project_name ?? ''); ?></td>
                                    <td class="align-middle text-center"><span data-count-kind="tasks" class="badge application-count"><?= (int) ($application->task_count ?? 0); ?></span></td>
                                    <td class="align-middle text-center"><span data-count-kind="notes" class="badge application-count"><?= (int) ($application->note_count ?? 0); ?></span></td>
                                    <!-- Azioni dell’applicazione, allineate a destra -->
                                    <td class="align-middle text-end">
                                        <div class="d-flex flex-row flex-nowrap justify-content-end align-items-center gap-1">
                                            <?php if( ! $isTrashed): ?>
                                                <?php if($canEditApplications): ?>
                                                    <a href="<?= esc(base_url('backend/applications/edit/' . rawurlencode($application->uuid)), 'attr'); ?>" class="btn btn-sm app-btn-secondary text-nowrap">
                                                        <i class="fa-solid fa-pen fa-fw"></i> <?= ui_text('backend/applications.actions.edit', [], false); ?>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if($canShowApplications): ?>
                                                    <a href="<?= esc(base_url('backend/applications/show/' . rawurlencode($application->uuid)), 'attr'); ?>" class="btn btn-sm app-btn-secondary text-nowrap">
                                                        <i class="fa-solid fa-eye fa-fw"></i> <?= ui_text('backend/applications.actions.show', [], false); ?>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if(\App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, 'tasks_add')): ?>
                                                <button type="button" class="btn btn-sm app-btn-secondary text-nowrap" data-bs-toggle="modal" data-bs-target="#applications-add-task-modal" data-application-uuid="<?= esc($application->uuid, 'attr'); ?>" data-application-name="<?= esc($application->name, 'attr'); ?>">
                                                    <i class="fa-solid fa-plus fa-fw"></i> <?= ui_text('backend/applications.actions.addTask', [], false); ?>
                                                </button>
                                                <?php endif; ?>
                                                <?php if(\App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, 'notes_add')): ?>
                                                <button type="button" class="btn btn-sm app-btn-secondary text-nowrap" data-bs-toggle="modal" data-bs-target="#applications-add-note-modal" data-application-uuid="<?= esc($application->uuid, 'attr'); ?>" data-application-name="<?= esc($application->name, 'attr'); ?>">
                                                    <i class="fa-solid fa-note-sticky fa-fw"></i> <?= ui_text('backend/applications.actions.addNote', [], false); ?>
                                                </button>
                                                <?php endif; ?>
                                                <?php if($canDeleteApplications): ?>
                                                    <form novalidate <?= ui_binding('backend/applications.messages.areYouSureSoftDelete', 'data-message', [$application->name], true); ?> class="deleteRecord m-0 p-0" data-controller="applications" data-message="<?= esc(sprintf(lang('backend/applications.messages.areYouSureSoftDelete'), $application->name), 'attr'); ?>">
                                                        <input type="hidden" name="uuid" value="<?= esc($application->uuid, 'attr'); ?>">
                                                        <button type="submit" class="btn btn-sm app-btn-secondary text-nowrap">
                                                            <i class="fa-solid fa-trash-can fa-fw" aria-hidden="true"></i> <?= ui_text('backend/applications.actions.softDelete', [], false); ?>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <?php if($canRestoreApplications): ?>
                                                <form novalidate <?= ui_binding('backend/applications.messages.areYouSureRestoreDelete', 'data-message', [$application->name], true); ?> class="restoreRecord m-0 p-0" data-controller="applications" data-message="<?= esc(sprintf(lang('backend/applications.messages.areYouSureRestoreDelete'), $application->name), 'attr'); ?>">
                                                    <input type="hidden" name="uuid" value="<?= esc($application->uuid, 'attr'); ?>">
                                                    <button type="submit" class="btn btn-sm app-btn-secondary text-nowrap">
                                                        <i class="fa-solid fa-trash-arrow-up fa-fw"></i> <?= ui_text('backend/applications.actions.restoreDelete', [], false); ?>
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                                <?php if($canDeleteApplications): ?>
                                                <form novalidate <?= ui_binding('backend/applications.messages.areYouSureHardDelete', 'data-message', [$application->name], true); ?> class="hardDeleteRecord m-0 p-0" data-controller="applications" data-message="<?= esc(sprintf(lang('backend/applications.messages.areYouSureHardDelete'), $application->name), 'attr'); ?>">
                                                    <input type="hidden" name="uuid" value="<?= esc($application->uuid, 'attr'); ?>">
                                                    <button type="submit" class="btn btn-sm app-btn-danger text-nowrap">
                                                        <i class="fa-solid fa-trash-can fa-fw" aria-hidden="true"></i> <?= ui_text('backend/applications.actions.hardDelete', [], false); ?>
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="border-end border-start">
                                    <td colspan="6" class="p-0 border-0">
                                        <div class="collapse" id="meta-<?= esc($application->uuid, 'attr'); ?>">
                                            <div class="p-2 border-bottom app-bg-surface app-text small">
                                                <?php $hasDate = false; ?>
                                                <?php if( ! empty($application->created_at)): ?>
                                                    <?php if($hasDate): ?>&nbsp;&bull;&nbsp;<?php endif; ?>
                                                    <?= ui_text('backend/applications.labels.createdAt', [], false); ?> <span class="fw-bold app-text-success"><?= ui_date(esc($application->created_at), 'conversational'); ?></span>
                                                    <?php $hasDate = true; ?>
                                                <?php endif; ?>
                                                <?php if( ! empty($application->updated_at)): ?>
                                                    <?php if($hasDate): ?>&nbsp;&bull;&nbsp;<?php endif; ?>
                                                    <?= ui_text('backend/applications.labels.updatedAt', [], false); ?> <span class="fw-bold app-text-success"><?= ui_date(esc($application->updated_at), 'conversational'); ?></span>
                                                    <?php $hasDate = true; ?>
                                                <?php endif; ?>
                                                <?php if( ! empty($application->restored_at)): ?>
                                                    <?php if($hasDate): ?>&nbsp;&bull;&nbsp;<?php endif; ?>
                                                    <?= ui_text('backend/applications.labels.restoredAt', [], false); ?> <span class="fw-bold app-text-success"><?= ui_date(esc($application->restored_at), 'conversational'); ?></span>
                                                    <?php $hasDate = true; ?>
                                                <?php endif; ?>
                                                <?php if( ! empty($application->deleted_at)): ?>
                                                    <?php if($hasDate): ?>&nbsp;&bull;&nbsp;<?php endif; ?>
                                                    <?= ui_text('backend/applications.labels.deletedAt', [], false); ?> <span class="fw-bold app-text-danger"><?= ui_date(esc($application->deleted_at), 'conversational'); ?></span>
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
                <div class="text-center py-3 fw-bold"><?= ui_text('backend/applications.messages.noApplicationsFound', [], false); ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>
