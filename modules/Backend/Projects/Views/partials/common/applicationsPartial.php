<?php if (\App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, 'applications_show_all')): ?>
    <section data-project-applications>
        <div class="card-header rounded-0 d-flex justify-content-between align-items-center flex-wrap gap-1" style="border-top: var(--bs-card-border-width) solid var(--bs-card-border-color);">
            <h2 class="card-title text-start mb-0"><?= ui_text('backend/permissions.applications.title', [], false); ?></h2>
            <?php if (\App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, 'applications_add')): ?>
                <a class="btn btn-sm app-btn-secondary" href="<?= esc(base_url('backend/applications/add') . '?project_uuid=' . rawurlencode($project->uuid), 'attr'); ?>"><i class="fa-solid fa-plus me-1" aria-hidden="true"></i><?= ui_text('backend/applications.titles.add', [], false); ?></a>
            <?php endif; ?>
        </div>
        <?php if (empty($projectApplications)): ?>
            <p class="small app-text-muted px-3 py-2 mb-0"><?= ui_text('backend/applications.messages.noApplicationsFound', [], false); ?></p>
        <?php else: ?>
            <ul class="list-group list-group-flush">
                <?php foreach ($projectApplications as $child): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center gap-2">
                        <span class="fw-bold text-break"><?= esc($child->name); ?></span>
                        <div class="d-flex flex-row flex-nowrap justify-content-end gap-1">
                            <?php if (\App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, 'applications_show')): ?>
                                <a class="btn btn-sm app-btn-secondary text-nowrap" href="<?= esc(base_url('backend/applications/show/' . $child->uuid), 'attr'); ?>"><?= ui_text('backend/applications.actions.show', [], false); ?></a>
                            <?php endif; ?>
                            <?php if (($action ?? '') === 'edit' && \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, 'applications_edit')): ?>
                                <a class="btn btn-sm app-btn-secondary text-nowrap" href="<?= esc(base_url('backend/applications/edit/' . $child->uuid), 'attr'); ?>"><?= ui_text('backend/applications.actions.edit', [], false); ?></a>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
<?php endif; ?>
