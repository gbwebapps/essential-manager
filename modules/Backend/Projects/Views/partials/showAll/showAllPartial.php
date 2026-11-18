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
                                <th><?= ui_text('backend/projects.labels.localUrl', [], false); ?></th>
                                <th><?= ui_text('backend/projects.labels.productionUrl', [], false); ?></th>
                                <th><?= ui_text('backend/projects.labels.protection', [], false); ?></th>
                                <th style="width: 10%;"><?= ui_text('backend/projects.buttons.actions', [], false); ?></th>
                            </tr>
                        </thead>
                        <tbody id="projectsBody">
                            <?php foreach($data['records'] as $project): ?>
                                <?php $isTrashed = ( ! is_null($project->deleted_at)); ?>
                                <tr class="border-end border-start table-row-110">
                                    <td class="align-middle fw-bold">
                                        <button <?= ui_binding('backend/projects.buttons.showSystemDetails', 'title', [], true); ?> class="toggle-meta-btn btn btn-sm btn-link p-0 me-1 app-text shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#meta-<?= esc($project->uuid, 'attr'); ?>" aria-controls="meta-<?= esc($project->uuid, 'attr'); ?>" aria-expanded="false" title="<?= esc(lang('backend/projects.buttons.showSystemDetails'), 'attr'); ?>">
                                            <i class="fa-solid fa-chevron-right"></i>
                                        </button>
                                    </td>
                                    <td class="align-middle fw-bold text-break"><?= esc($project->name); ?></td>
                                    <td class="align-middle text-break"><?= esc($project->parent_project_name ?? ''); ?></td>
                                    <td class="align-middle text-break"><?= esc($project->local_url ?? ''); ?></td>
                                    <td class="align-middle text-break"><?= esc($project->production_url ?? ''); ?></td>
                                    <td class="align-middle">
                                        <?php if((int) $project->is_protected === 1): ?>
                                            <span class="badge app-bg-muted-subtle app-text-muted-emphasis border"><i class="fa-solid fa-lock me-1"></i><?= ui_text('backend/projects.labels.protected', [], false); ?></span>
                                        <?php endif; ?>
                                        <?php if($isTrashed): ?>
                                            <span class="badge app-bg-danger"><?= ui_text('backend/projects.labels.deleted', [], false); ?></span>
                                        <?php endif; ?>
                                    </td>
                                            <!-- Cella Azioni Rapide -->
                                            <td class="align-middle text-start">
                                                
                                                <div class="d-flex flex-column gap-1">
                                                    
                                                    <?php if( ! $isTrashed): ?>
                                                        
                                                        <!-- Pulsante Dettaglio -->
                                                        <a href="<?= esc(base_url('backend/projects/show/' . rawurlencode($project->uuid)), 'attr'); ?>" class="text-decoration-none badge app-bg-muted-subtle app-text-muted-emphasis action">
                                                            <i class="fa-solid fa-eye fa-fw"></i> <?= ui_text('backend/projects.actions.show', [], false); ?>
                                                        </a>

                                                        <!-- Pulsante Aggiorna -->
                                                        <a href="<?= esc(base_url('backend/projects/edit/' . rawurlencode($project->uuid)), 'attr'); ?>" class="text-decoration-none badge app-bg-muted-subtle app-text-muted-emphasis action">
                                                            <i class="fa-solid fa-pen fa-fw"></i> <?= ui_text('backend/projects.actions.edit', [], false); ?>
                                                        </a>

                                                        <!-- Pulsante Elimina (Soft Delete) -->
                                                        <?php if((int) $project->is_protected !== 1): ?>
                                                        <form <?= ui_binding('backend/projects.messages.areYouSureSoftDelete', 'data-message', [$project->name], true); ?> class="deleteRecord m-0 p-0" data-controller="projects" data-message="<?= esc(sprintf(lang('backend/projects.messages.areYouSureSoftDelete'), $project->name), 'attr'); ?>">
                                                            <input type="hidden" name="uuid" value="<?= esc($project->uuid, 'attr'); ?>">
                                                            <button type="submit" class="btn badge app-bg-danger-subtle app-text-danger-emphasis action shadow-none w-100 border-0">
                                                                <i class="fa-solid fa-xmark fa-fw"></i> <?= ui_text('backend/projects.actions.softDelete', [], false); ?>
                                                            </button>
                                                        </form>
                                                        <?php endif; ?>
                                                        
                                                    <?php else: ?>
                                                        
                                                        <!-- Pulsante Ripristina (Restore Delete) -->
                                                        <form <?= ui_binding('backend/projects.messages.areYouSureRestoreDelete', 'data-message', [$project->name], true); ?> class="restoreRecord m-0 p-0" data-controller="projects" data-message="<?= esc(sprintf(lang('backend/projects.messages.areYouSureRestoreDelete'), $project->name), 'attr'); ?>">
                                                            <input type="hidden" name="uuid" value="<?= esc($project->uuid, 'attr'); ?>">
                                                            <button type="submit" class="btn badge app-bg-success-subtle app-text-success-emphasis action shadow-none w-100 border-0">
                                                                <i class="fa-solid fa-trash-arrow-up fa-fw"></i> <?= ui_text('backend/projects.actions.restoreDelete', [], false); ?>
                                                            </button>
                                                        </form>

                                                        <!-- Pulsante Elimina Definitivamente (Hard Delete) -->
                                                        <?php if((int) $project->is_protected !== 1): ?>
                                                        <form <?= ui_binding('backend/projects.messages.areYouSureHardDelete', 'data-message', [$project->name], true); ?> class="hardDeleteRecord m-0 p-0" data-controller="projects" data-message="<?= esc(sprintf(lang('backend/projects.messages.areYouSureHardDelete'), $project->name), 'attr'); ?>">
                                                            <input type="hidden" name="uuid" value="<?= esc($project->uuid, 'attr'); ?>">
                                                            <button type="submit" class="btn badge app-bg-danger-subtle app-text-danger-emphasis action shadow-none w-100 border-0">
                                                                <i class="fa-solid fa-xmark fa-fw"></i> <?= ui_text('backend/projects.actions.hardDelete', [], false); ?>
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
