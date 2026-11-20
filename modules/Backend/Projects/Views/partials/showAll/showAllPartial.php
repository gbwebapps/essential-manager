<?php
    $canEditProjects = \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, 'projects_edit');
    $canShowProjects = \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, 'projects_show');
    $canDeleteProjects = \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, 'projects_delete');
    $canRestoreProjects = \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, 'projects_restore');
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
                                        <?= ui_text('backend/projects.labels.name', [], false); ?> <?= (($posts['column'] === 'name') ? '&nbsp;' . $icon : ''); ?>
                                    </a>
                                </th>
                                <th><?= ui_text('backend/projects.labels.parentProject', [], false); ?></th>
                                <th class="text-center text-nowrap"><?= ui_text('backend/projects.labels.applications', [], false); ?></th>
                                <th class="text-center text-nowrap"><?= ui_text('backend/projects.related.tasks', [], false); ?></th>
                                <th class="text-center text-nowrap"><?= ui_text('backend/projects.related.notes', [], false); ?></th>
                                <th style="width: 1%;" class="text-nowrap text-end"><?= ui_text('backend/projects.buttons.actions', [], false); ?></th>
                            </tr>
                        </thead>
                        <tbody id="projectsBody">
                            <?php foreach($data['records'] as $project): ?>
                                <?php $isTrashed = ( ! is_null($project->deleted_at)); ?>
                                <tr class="border-end border-start">
                                    <td class="align-middle fw-bold">
                                        <button <?= ui_binding('backend/projects.buttons.showSystemDetails', 'title', [], true); ?> class="toggle-meta-btn btn btn-sm btn-link p-0 me-1 app-text shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#meta-<?= esc($project->uuid, 'attr'); ?>" aria-controls="meta-<?= esc($project->uuid, 'attr'); ?>" aria-expanded="false" title="<?= esc(lang('backend/projects.buttons.showSystemDetails'), 'attr'); ?>">
                                            <i class="fa-solid fa-chevron-right"></i>
                                        </button>
                                    </td>
                                    <td class="align-middle fw-bold text-break"><?= esc($project->name); ?></td>
                                    <td class="align-middle text-break"><?= esc($project->parent_project_name ?? ''); ?></td>
                                    <td class="align-middle text-center"><?php if (\App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, 'applications_show_all')): ?><a class="badge app-bg-muted-subtle app-text-muted-emphasis border text-decoration-none" href="<?= esc(base_url('backend/applications/showAll') . '?project_uuid=' . rawurlencode($project->uuid), 'attr'); ?>"><?= (int) ($project->application_count ?? 0); ?></a><?php else: ?><span class="badge app-bg-muted-subtle app-text-muted-emphasis border"><?= (int) ($project->application_count ?? 0); ?></span><?php endif; ?></td>
                                    <td class="align-middle text-center"><span class="badge app-bg-muted-subtle app-text-muted-emphasis border"><?= (int) ($project->task_count ?? 0); ?></span></td>
                                    <td class="align-middle text-center"><span class="badge app-bg-muted-subtle app-text-muted-emphasis border"><?= (int) ($project->note_count ?? 0); ?></span></td>
                                    <!-- Azioni del progetto, allineate a destra -->
                                    <td class="align-middle text-end">
                                        <div class="d-flex flex-row flex-nowrap justify-content-end align-items-center gap-1">
                                            <?php if( ! $isTrashed): ?>
                                                <?php if($canEditProjects): ?>
                                                    <a href="<?= esc(base_url('backend/projects/edit/' . rawurlencode($project->uuid)), 'attr'); ?>" class="btn btn-sm app-btn-secondary text-nowrap">
                                                        <i class="fa-solid fa-pen fa-fw"></i> <?= ui_text('backend/projects.actions.edit', [], false); ?>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if($canShowProjects): ?>
                                                    <a href="<?= esc(base_url('backend/projects/show/' . rawurlencode($project->uuid)), 'attr'); ?>" class="btn btn-sm app-btn-secondary text-nowrap">
                                                        <i class="fa-solid fa-eye fa-fw"></i> <?= ui_text('backend/projects.actions.show', [], false); ?>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if(\App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, 'tasks_add')): ?>
                                                <button type="button" class="btn btn-sm app-btn-secondary text-nowrap" data-bs-toggle="modal" data-bs-target="#projects-add-task-modal" data-project-uuid="<?= esc($project->uuid, 'attr'); ?>" data-project-name="<?= esc($project->name, 'attr'); ?>">
                                                    <i class="fa-solid fa-plus fa-fw"></i> <?= ui_text('backend/projects.actions.addTask', [], false); ?>
                                                </button>
                                                <?php endif; ?>
                                                <?php if(\App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, 'notes_add')): ?>
                                                <button type="button" class="btn btn-sm app-btn-secondary text-nowrap" data-bs-toggle="modal" data-bs-target="#projects-add-note-modal" data-project-uuid="<?= esc($project->uuid, 'attr'); ?>" data-project-name="<?= esc($project->name, 'attr'); ?>">
                                                    <i class="fa-solid fa-note-sticky fa-fw"></i> <?= ui_text('backend/projects.actions.addNote', [], false); ?>
                                                </button>
                                                <?php endif; ?>
                                                <?php if($canDeleteProjects): ?>
                                                    <form novalidate <?= ui_binding('backend/projects.messages.areYouSureSoftDelete', 'data-message', [$project->name], true); ?> class="deleteRecord m-0 p-0" data-controller="projects" data-message="<?= esc(sprintf(lang('backend/projects.messages.areYouSureSoftDelete'), $project->name), 'attr'); ?>">
                                                        <input type="hidden" name="uuid" value="<?= esc($project->uuid, 'attr'); ?>">
                                                        <button type="submit" class="btn btn-sm app-btn-secondary text-nowrap">
                                                            <i class="fa-solid fa-trash-can fa-fw" aria-hidden="true"></i> <?= ui_text('backend/projects.actions.softDelete', [], false); ?>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <?php if($canRestoreProjects): ?>
                                                <form novalidate <?= ui_binding('backend/projects.messages.areYouSureRestoreDelete', 'data-message', [$project->name], true); ?> class="restoreRecord m-0 p-0" data-controller="projects" data-message="<?= esc(sprintf(lang('backend/projects.messages.areYouSureRestoreDelete'), $project->name), 'attr'); ?>">
                                                    <input type="hidden" name="uuid" value="<?= esc($project->uuid, 'attr'); ?>">
                                                    <button type="submit" class="btn btn-sm app-btn-secondary text-nowrap">
                                                        <i class="fa-solid fa-trash-arrow-up fa-fw"></i> <?= ui_text('backend/projects.actions.restoreDelete', [], false); ?>
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                                <?php if($canDeleteProjects): ?>
                                                <form novalidate <?= ui_binding('backend/projects.messages.areYouSureHardDelete', 'data-message', [$project->name], true); ?> class="hardDeleteRecord m-0 p-0" data-controller="projects" data-message="<?= esc(sprintf(lang('backend/projects.messages.areYouSureHardDelete'), $project->name), 'attr'); ?>">
                                                    <input type="hidden" name="uuid" value="<?= esc($project->uuid, 'attr'); ?>">
                                                    <button type="submit" class="btn btn-sm app-btn-danger text-nowrap">
                                                        <i class="fa-solid fa-trash-can fa-fw" aria-hidden="true"></i> <?= ui_text('backend/projects.actions.hardDelete', [], false); ?>
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="border-end border-start">
                                    <td colspan="7" class="p-0 border-0">
                                        <div class="collapse" id="meta-<?= esc($project->uuid, 'attr'); ?>">
                                            <div class="p-2 border-bottom app-bg-surface app-text small">
                                                <?php $hasDate = false; ?>
                                                <?php if( ! empty($project->created_at)): ?>
                                                    <?php if($hasDate): ?>&nbsp;&bull;&nbsp;<?php endif; ?>
                                                    <?= ui_text('backend/projects.labels.createdAt', [], false); ?> <span class="fw-bold app-text-success"><?= ui_date(esc($project->created_at), 'conversational'); ?></span>
                                                    <?php $hasDate = true; ?>
                                                <?php endif; ?>
                                                <?php if( ! empty($project->updated_at)): ?>
                                                    <?php if($hasDate): ?>&nbsp;&bull;&nbsp;<?php endif; ?>
                                                    <?= ui_text('backend/projects.labels.updatedAt', [], false); ?> <span class="fw-bold app-text-success"><?= ui_date(esc($project->updated_at), 'conversational'); ?></span>
                                                    <?php $hasDate = true; ?>
                                                <?php endif; ?>
                                                <?php if( ! empty($project->restored_at)): ?>
                                                    <?php if($hasDate): ?>&nbsp;&bull;&nbsp;<?php endif; ?>
                                                    <?= ui_text('backend/projects.labels.restoredAt', [], false); ?> <span class="fw-bold app-text-success"><?= ui_date(esc($project->restored_at), 'conversational'); ?></span>
                                                    <?php $hasDate = true; ?>
                                                <?php endif; ?>
                                                <?php if( ! empty($project->deleted_at)): ?>
                                                    <?php if($hasDate): ?>&nbsp;&bull;&nbsp;<?php endif; ?>
                                                    <?= ui_text('backend/projects.labels.deletedAt', [], false); ?> <span class="fw-bold app-text-danger"><?= ui_date(esc($project->deleted_at), 'conversational'); ?></span>
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
                <div class="text-center py-3 fw-bold"><?= ui_text('backend/projects.messages.noProjectsFound', [], false); ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>
