<?php
    $canEditChecklists = \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, 'checklists_edit');
    $canShowChecklists = \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, 'checklists_show');
    $canDeleteChecklists = \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, 'checklists_delete');
    $canRestoreChecklists = \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, 'checklists_restore');
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
                                        <?= ui_text('backend/checklists.labels.name', [], false); ?> <?= (($posts['column'] === 'name') ? '&nbsp;' . $icon : ''); ?>
                                    </a>
                                </th>
                                <th class="text-center text-nowrap"><?= ui_text('backend/checklists.labels.steps', [], false); ?></th>
                                <th style="width: 1%;" class="text-nowrap text-end"><?= ui_text('backend/checklists.buttons.actions', [], false); ?></th>
                            </tr>
                        </thead>
                        <tbody id="checklistsBody">
                            <?php foreach($data['records'] as $checklist): ?>
                                <?php $isTrashed = ( ! is_null($checklist->deleted_at)); ?>
                                <tr class="border-end border-start">
                                    <td class="align-middle fw-bold">
                                        <button <?= ui_binding('backend/checklists.buttons.showSystemDetails', 'title', [], true); ?> class="toggle-meta-btn btn btn-sm btn-link p-0 me-1 app-text shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#meta-<?= esc($checklist->uuid, 'attr'); ?>" aria-controls="meta-<?= esc($checklist->uuid, 'attr'); ?>" aria-expanded="false" title="<?= esc(lang('backend/checklists.buttons.showSystemDetails'), 'attr'); ?>">
                                            <i class="fa-solid fa-chevron-right"></i>
                                        </button>
                                    </td>
                                    <td class="align-middle fw-bold text-break"><?= esc($checklist->name); ?></td>
                                    <td class="align-middle text-center"><span class="badge bg-primary text-white border rounded-1"><?= (int) $checklist->step_count; ?></span></td>
                                    <!-- Azioni del checklist, allineate a destra -->
                                    <td class="align-middle text-end">
                                        <div class="d-flex flex-row flex-nowrap justify-content-end align-items-center gap-1">
                                            <?php if( ! $isTrashed): ?>
                                                <?php if($canEditChecklists): ?>
                                                    <a href="<?= esc(base_url('backend/checklists/edit/' . rawurlencode($checklist->uuid)), 'attr'); ?>" class="btn btn-sm app-btn-secondary text-nowrap">
                                                        <i class="fa-solid fa-pen fa-fw"></i> <?= ui_text('backend/checklists.actions.edit', [], false); ?>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if($canShowChecklists): ?>
                                                    <a href="<?= esc(base_url('backend/checklists/show/' . rawurlencode($checklist->uuid)), 'attr'); ?>" class="btn btn-sm app-btn-secondary text-nowrap">
                                                        <i class="fa-solid fa-eye fa-fw"></i> <?= ui_text('backend/checklists.actions.show', [], false); ?>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if($canDeleteChecklists): ?>
                                                    <form novalidate <?= ui_binding('backend/checklists.messages.areYouSureSoftDelete', 'data-message', [$checklist->name], true); ?> class="deleteRecord m-0 p-0" data-controller="checklists" data-message="<?= esc(sprintf(lang('backend/checklists.messages.areYouSureSoftDelete'), $checklist->name), 'attr'); ?>">
                                                        <input type="hidden" name="uuid" value="<?= esc($checklist->uuid, 'attr'); ?>">
                                                        <button type="submit" class="btn btn-sm app-btn-secondary text-nowrap">
                                                            <i class="fa-solid fa-trash-can fa-fw" aria-hidden="true"></i> <?= ui_text('backend/checklists.actions.softDelete', [], false); ?>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <?php if($canRestoreChecklists): ?>
                                                <form novalidate <?= ui_binding('backend/checklists.messages.areYouSureRestoreDelete', 'data-message', [$checklist->name], true); ?> class="restoreRecord m-0 p-0" data-controller="checklists" data-message="<?= esc(sprintf(lang('backend/checklists.messages.areYouSureRestoreDelete'), $checklist->name), 'attr'); ?>">
                                                    <input type="hidden" name="uuid" value="<?= esc($checklist->uuid, 'attr'); ?>">
                                                    <button type="submit" class="btn btn-sm app-btn-secondary text-nowrap">
                                                        <i class="fa-solid fa-trash-arrow-up fa-fw"></i> <?= ui_text('backend/checklists.actions.restoreDelete', [], false); ?>
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                                <?php if($canDeleteChecklists): ?>
                                                <form novalidate <?= ui_binding('backend/checklists.messages.areYouSureHardDelete', 'data-message', [$checklist->name], true); ?> class="hardDeleteRecord m-0 p-0" data-controller="checklists" data-message="<?= esc(sprintf(lang('backend/checklists.messages.areYouSureHardDelete'), $checklist->name), 'attr'); ?>">
                                                    <input type="hidden" name="uuid" value="<?= esc($checklist->uuid, 'attr'); ?>">
                                                    <button type="submit" class="btn btn-sm app-btn-danger text-nowrap">
                                                        <i class="fa-solid fa-trash-can fa-fw" aria-hidden="true"></i> <?= ui_text('backend/checklists.actions.hardDelete', [], false); ?>
                                                    </button>
                                                </form>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="border-end border-start">
                                    <td colspan="4" class="p-0 border-0">
                                        <div class="collapse" id="meta-<?= esc($checklist->uuid, 'attr'); ?>">
                                            <div class="p-2 border-bottom app-bg-surface app-text small">
                                                <?php $hasDate = false; ?>
                                                <?php if( ! empty($checklist->created_at)): ?>
                                                    <?php if($hasDate): ?>&nbsp;&bull;&nbsp;<?php endif; ?>
                                                    <?= ui_text('backend/checklists.labels.createdAt', [], false); ?> <span class="fw-bold app-text-success"><?= ui_date(esc($checklist->created_at), 'conversational'); ?></span>
                                                    <?php $hasDate = true; ?>
                                                <?php endif; ?>
                                                <?php if( ! empty($checklist->updated_at)): ?>
                                                    <?php if($hasDate): ?>&nbsp;&bull;&nbsp;<?php endif; ?>
                                                    <?= ui_text('backend/checklists.labels.updatedAt', [], false); ?> <span class="fw-bold app-text-success"><?= ui_date(esc($checklist->updated_at), 'conversational'); ?></span>
                                                    <?php $hasDate = true; ?>
                                                <?php endif; ?>
                                                <?php if( ! empty($checklist->restored_at)): ?>
                                                    <?php if($hasDate): ?>&nbsp;&bull;&nbsp;<?php endif; ?>
                                                    <?= ui_text('backend/checklists.labels.restoredAt', [], false); ?> <span class="fw-bold app-text-success"><?= ui_date(esc($checklist->restored_at), 'conversational'); ?></span>
                                                    <?php $hasDate = true; ?>
                                                <?php endif; ?>
                                                <?php if( ! empty($checklist->deleted_at)): ?>
                                                    <?php if($hasDate): ?>&nbsp;&bull;&nbsp;<?php endif; ?>
                                                    <?= ui_text('backend/checklists.labels.deletedAt', [], false); ?> <span class="fw-bold app-text-danger"><?= ui_date(esc($checklist->deleted_at), 'conversational'); ?></span>
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
                <div class="text-center py-3 fw-bold"><?= ui_text('backend/checklists.messages.noChecklistsFound', [], false); ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>
