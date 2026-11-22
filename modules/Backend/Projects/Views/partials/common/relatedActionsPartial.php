                    <?php if ( ! ($relatedReadOnly ?? false) && \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, ($isTask ? 'tasks' : 'notes') . '_edit')): ?>
                        <button type="button" class="btn btn-link btn-sm p-1 border-0 shadow-none task-record-link" aria-label="<?= esc(lang('backend/projects.actions.edit'), 'attr'); ?>" data-bs-toggle="modal" data-bs-target="#projects-add-<?= $relatedKind; ?>-modal" data-project-record="<?= esc(json_encode($record, JSON_THROW_ON_ERROR), 'attr'); ?>" data-project-uuid="<?= esc($relatedProject->uuid, 'attr'); ?>" data-project-name="<?= esc($relatedProject->name, 'attr'); ?>">
                            <i class="fa-solid fa-pen fa-fw" aria-hidden="true"></i><span class="d-none d-sm-inline"> <?= ui_text('backend/projects.actions.edit', [], false); ?></span>
                        </button>
                    <?php endif; ?>
                    <?php if ( ! ($relatedReadOnly ?? false) && \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, 'projects_edit') && \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, ($isTask ? 'tasks' : 'notes') . '_delete')): ?>
                        <form novalidate method="post" class="d-inline-block m-0" data-related-delete="<?= $relatedKind; ?>" data-confirm="<?= esc($isTask ? sprintf(lang('backend/tasks.messages.areYouSureSoftDelete'), $relatedItem->title) : lang('backend/tasks.messages.confirmDeleteNote'), 'attr'); ?>">
                            <input type="hidden" name="project_uuid" value="<?= esc($relatedProject->uuid, 'attr'); ?>">
                            <input type="hidden" name="uuid" value="<?= esc($relatedItem->uuid, 'attr'); ?>">
                            <button type="submit" class="btn btn-link btn-sm p-1 border-0 shadow-none task-record-link app-text-danger text-nowrap" aria-label="<?= esc(lang('backend/projects.actions.softDelete'), 'attr'); ?>"><i class="fa-solid fa-trash-can fa-fw" aria-hidden="true"></i><span class="d-none d-sm-inline"> <?= ui_text('backend/projects.actions.softDelete', [], false); ?></span></button>
                        </form>
                    <?php endif; ?>
