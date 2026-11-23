<?= $this->extend(\Modules\Backend\Template\Config\Views::MAIN_VIEW) ?>

<?= $this->section('content') ?>

    <?= $this->include(\Modules\Backend\Tasks\Config\Views::ACTION_LINKS_STYLE_PARTIAL); ?>

    <div id="tasks-list" class="tasks crud-list">

        <div class="row">
            <div class="col-12">

                <div class="card">

                    <!-- Inizio testata lista -->
                    <div class="card-header">
                        <div class="row">

                            <!-- Select per il numero delle righe da mostrare -->
                            <div class="col-12 col-md-1 mb-3 mb-md-0">
                                <select class="form-select changeNumRows">
                                    <option value="5">5</option>
                                    <option value="10">10</option>
                                    <option value="15">15</option>
                                    <option value="20">20</option>
                                </select>
                            </div>
                            <!-- End Select per il numero delle righe da mostrare -->

                            <!-- Bottone Mobile per aprire l'Offcanvas -->
                            <div class="col-12 d-md-none mb-3">
                                <button class="btn app-btn-secondary w-100" type="button" data-bs-toggle="offcanvas" data-bs-target="#tasks-actionsOffcanvas" aria-controls="tasks-actionsOffcanvas">
                                    <i class="fa-solid fa-bars"></i> <?= ui_text('backend/tasks.labels.filtersAndOptions', [], false); ?>
                                </button>
                            </div>
                            <!-- End Bottone Mobile -->

                            <!-- Contenitore Offcanvas -->
                            <div class="col-12 col-md-11 offcanvas-md offcanvas-bottom" tabindex="-1" id="tasks-actionsOffcanvas">
                                <div class="offcanvas-header">
                                    <h5 class="offcanvas-title">
                                        <i class="fa-solid fa-bars"></i> <?= ui_text('backend/tasks.labels.filtersAndOptions', [], false); ?>
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#tasks-actionsOffcanvas" aria-label="Close"></button>
                                </div>

                                <div class="offcanvas-body pt-md-2">
                                    <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center justify-content-md-end w-100 gap-0 gap-md-3">

                                        <!-- Selezione stato record -->
                                        <div class="d-flex flex-column flex-md-row gap-0 gap-md-3 me-md-auto mb-3 mb-md-0 border-md-end pe-md-3">
                                            <a href="#" class="cmd-link active-filter" data-trash-filter="active">
                                                <i class="fa-solid fa-check"></i> <?= ui_text('backend/tasks.links.activeRecords', [], false); ?>
                                                <span class="badge app-bg-surface app-text-muted border rounded-1 ms-1" data-trash-count="active" hidden></span>
                                            </a>
                                            <a href="#" class="cmd-link" data-trash-filter="trashed">
                                                <i class="fa-solid fa-trash-can"></i> <?= ui_text('backend/tasks.links.trashedRecords', [], false); ?>
                                                <span class="badge app-bg-surface app-text-muted border rounded-1 ms-1" data-trash-count="trashed" hidden></span>
                                            </a>
                                            <a href="#" class="cmd-link" data-trash-filter="all">
                                                <i class="fa-solid fa-list"></i> <?= ui_text('backend/tasks.links.allRecords', [], false); ?>
                                                <span class="badge app-bg-surface app-text-muted border rounded-1 ms-1" data-trash-count="all" hidden></span>
                                            </a>
                                        </div>
                                        <!-- End Selezione stato record -->

                                        <!-- Azioni e Filtri -->
                                        <a href="#" data-bs-toggle="collapse" data-bs-target="#tasks-search-bar" class="cmd-link link-search">
                                            <i class="fa-solid fa-magnifying-glass"></i> <?= ui_text('backend/tasks.links.filters', [], false); ?>
                                        </a>

                                        <a href="#" class="cmd-link link-reset-search">
                                            <i class="fa-solid fa-xmark"></i> <?= ui_text('backend/tasks.links.resetFilters', [], false); ?>
                                        </a>

                                        <a href="#" class="cmd-link reset-sorting-link">
                                            <i class="fa-solid fa-sort"></i> <?= ui_text('backend/tasks.links.resetSorting', [], false); ?>
                                        </a>

                                        <a href="#" class="cmd-link refresh-list">
                                            <i class="fa-solid fa-arrows-rotate"></i> <?= ui_text('backend/tasks.links.reloadList', [], false); ?>
                                        </a>
                                        <!-- End Azioni e Filtri -->

                                    </div>
                                </div>
                            </div>
                            <!-- End Contenitore Offcanvas -->

                        </div>
                    </div>
                    <!-- Fine testata lista -->

                    <!-- Inizio parte filtri -->
                    <div id="tasks-search-bar" class="collapse search-bar">
                        <div class="row card-body pb-0">

                            <div class="col-md-3">
                                <div class="mb-2 mb-md-0">
                                    <label for="tasks-project_uuid"><?= ui_text('backend/tasks.labels.project', [], false); ?></label>
                                    <select id="tasks-project_uuid" class="form-select" data-search-field="project_uuid">
                                        <option value=""><?= ui_text('backend/tasks.labels.allProjects', [], false); ?></option>
                                        <?php foreach ($ownerOptions as $owner): if ($owner->kind !== 'project'): continue; endif; ?>
                                            <option value="<?= esc(substr($owner->value, 8), 'attr'); ?>"><?= esc($owner->label); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div data-search-error="project_uuid" class="error_project_uuid app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-2 mb-md-0">
                                    <label for="tasks-application_uuid"><?= ui_text('backend/tasks.labels.application', [], false); ?></label>
                                    <select id="tasks-application_uuid" class="form-select" data-search-field="application_uuid">
                                        <option value=""><?= ui_text('backend/tasks.labels.allApps', [], false); ?></option>
                                        <?php foreach ($ownerOptions as $owner): if ($owner->kind !== 'application'): continue; endif; ?>
                                            <option value="<?= esc(substr($owner->value, 12), 'attr'); ?>"><?= esc($owner->label); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div data-search-error="application_uuid" class="error_application_uuid app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
                                </div>
                            </div>
                            <!-- Ricerca avanzata per nome -->
                            <div class="col-md-3">
                                <div class="mb-2 mb-md-0">
                                    <label for="tasks-title"><?= ui_text('backend/tasks.labels.title', [], false); ?></label>
                                    <div class="input-group">
                                        <input <?= ui_binding('backend/tasks.placeholders.searchTitle', 'placeholder', [], true); ?> type="text" id="tasks-title" class="form-control" placeholder="<?= esc(lang('backend/tasks.placeholders.searchTitle'), 'attr'); ?>" data-search-field="title" autocomplete="off">
                                        <span class="input-group-text reset-search-field"><i class="fa-solid fa-times"></i></span>
                                    </div>
                                    <div data-search-error="title" class="error_title app-text-danger fw-bold small pt-1">&nbsp;</div>
                                </div>
                            </div>
                            <!-- End Ricerca avanzata per nome -->
                            <div class="col-md-3">
                                <div class="mb-2 mb-md-0">
                                    <label for="tasks-status"><?= ui_text('backend/tasks.labels.status', [], false); ?></label>
                                    <select id="tasks-status" class="form-select" data-search-field="status">
                                        <option value=""><?= ui_text('backend/tasks.labels.allStates', [], false); ?></option>
                                        <option value="open"><?= ui_text('backend/tasks.labels.open', [], false); ?></option>
                                        <?php foreach (['todo','in_progress','completed'] as $status): ?>
                                            <option value="<?= $status; ?>"><?= ui_text('backend/tasks.statuses.' . $status, [], false); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div data-search-error="status" class="error_status app-text-danger fw-bold small pt-1">&nbsp;</div>
                                </div>
                            </div>


                        </div>

                        <div class="row card-body py-0">

                            <!-- Ricerca avanzata per data creazione da -->
                            <div class="col-md-3">
                                <div class="mb-2 mb-md-0">
                                    <label for="tasks-created_at-from"><?= ui_text('backend/tasks.labels.dateFromAdd', [], false); ?></label>
                                    <div id="wrapper-tasks-created_at-from" class="input-group">
                                        <input <?= ui_binding('backend/tasks.placeholders.dateFromAdd', 'placeholder', [], true); ?> type="text" id="tasks-created_at-from" data-input class="form-control" placeholder="<?= esc(lang('backend/tasks.placeholders.dateFromAdd'), 'attr'); ?>" data-search-field="created_at-from" autocomplete="off">
                                        <span class="input-group-text reset-search-field" data-clear><i class="fa-solid fa-times"></i></span>
                                        <span class="input-group-text" data-open><i class="fa-solid fa-calendar-days"></i></span>
                                    </div>
                                    <div data-search-error="created_at-from" class="error_created_at-from app-text-danger fw-bold small pt-1">&nbsp;</div>
                                </div>
                            </div>
                            <!-- End Ricerca avanzata per data creazione da -->

                            <!-- Ricerca avanzata per data creazione a -->
                            <div class="col-md-3">
                                <div class="mb-2 mb-md-0">
                                    <label for="tasks-created_at-to"><?= ui_text('backend/tasks.labels.dateToAdd', [], false); ?></label>
                                    <div id="wrapper-tasks-created_at-to" class="input-group">
                                        <input <?= ui_binding('backend/tasks.placeholders.dateToAdd', 'placeholder', [], true); ?> type="text" id="tasks-created_at-to" data-input class="form-control" placeholder="<?= esc(lang('backend/tasks.placeholders.dateToAdd'), 'attr'); ?>" data-search-field="created_at-to" autocomplete="off">
                                        <span class="input-group-text reset-search-field" data-clear><i class="fa-solid fa-times"></i></span>
                                        <span class="input-group-text" data-open><i class="fa-solid fa-calendar-days"></i></span>
                                    </div>
                                    <div data-search-error="created_at-to" class="error_created_at-to app-text-danger fw-bold small pt-1">&nbsp;</div>
                                </div>
                            </div>
                            <!-- End Ricerca avanzata per data creazione a -->

                            <!-- Ricerca avanzata per data modifica da -->
                            <div class="col-md-3">
                                <div class="mb-2 mb-md-0">
                                    <label for="tasks-updated_at-from"><?= ui_text('backend/tasks.labels.dateFromEdit', [], false); ?></label>
                                    <div id="wrapper-tasks-updated_at-from" class="input-group">
                                        <input <?= ui_binding('backend/tasks.placeholders.dateFromEdit', 'placeholder', [], true); ?> type="text" id="tasks-updated_at-from" data-input class="form-control" placeholder="<?= esc(lang('backend/tasks.placeholders.dateFromEdit'), 'attr'); ?>" data-search-field="updated_at-from" autocomplete="off">
                                        <span class="input-group-text reset-search-field" data-clear><i class="fa-solid fa-times"></i></span>
                                        <span class="input-group-text" data-open><i class="fa-solid fa-calendar-days"></i></span>
                                    </div>
                                    <div data-search-error="updated_at-from" class="error_updated_at-from app-text-danger fw-bold small pt-1">&nbsp;</div>
                                </div>
                            </div>
                            <!-- End Ricerca avanzata per data modifica da -->

                            <!-- Ricerca avanzata per data modifica a -->
                            <div class="col-md-3">
                                <div class="mb-2 mb-md-0">
                                    <label for="tasks-updated_at-to"><?= ui_text('backend/tasks.labels.dateToEdit', [], false); ?></label>
                                    <div id="wrapper-tasks-updated_at-to" class="input-group">
                                        <input <?= ui_binding('backend/tasks.placeholders.dateToEdit', 'placeholder', [], true); ?> type="text" id="tasks-updated_at-to" data-input class="form-control" placeholder="<?= esc(lang('backend/tasks.placeholders.dateToEdit'), 'attr'); ?>" data-search-field="updated_at-to" autocomplete="off">
                                        <span class="input-group-text reset-search-field" data-clear><i class="fa-solid fa-times"></i></span>
                                        <span class="input-group-text" data-open><i class="fa-solid fa-calendar-days"></i></span>
                                    </div>
                                    <div data-search-error="updated_at-to" class="error_updated_at-to app-text-danger fw-bold small pt-1">&nbsp;</div>
                                </div>
                            </div>
                            <!-- End Ricerca avanzata per data modifica a -->

                        </div>

                    </div>
                    <!-- Fine parte filtri -->

                    <!-- Inizio contenuto -->
                    <div class="row">
                        <div class="col-12">
                            <div id="showAll-tasks-container"></div>
                        </div>
                    </div>
                    <!-- Fine contenuto -->

                </div>
            </div>
        </div>
    </div>


    <?= $this->include(\Modules\Backend\Tasks\Config\Views::NOTE_MODAL_PARTIAL); ?>

<?= $this->endSection() ?>

<?= $this->section('beforeLinksBarView') ?>
    <?= $this->include(\Modules\Backend\Tasks\Config\Views::SECTION_STYLE_PARTIAL); ?>
    <?= $this->include(\Modules\Backend\Tasks\Config\Views::OVERVIEW_STYLE_PARTIAL); ?>
    <div id="tasks-overview-container" class="mb-3"><?= $this->include(\Modules\Backend\Tasks\Config\Views::OVERVIEW_PARTIAL); ?></div>
<?= $this->endSection() ?>
