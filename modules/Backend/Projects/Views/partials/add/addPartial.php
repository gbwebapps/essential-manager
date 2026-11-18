<!-- Struttura principale del form di inserimento -->
<div class="row">
    <div class="col-12 col-lg-8 offset-lg-2">
        <div class="card">
            <form id="projects-add" data-unsaved-check>
                <div id="generalData">
                    <div class="card-header">
                        <h2 class="card-title text-center text-lg-start mb-0"><?= ui_text('backend/projects.panels.generalData', [], false); ?></h2>
                    </div>
                    <div class="card-body pb-0">
                        <div class="row">
                            <div class="col-12 col-lg-6">
                                <div class="mb-2">
                                    <label for="name" class="form-label required-field"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/projects.labels.name', [], false); ?></label>
                                    <input <?= ui_binding('backend/projects.placeholders.name', 'placeholder', [], true); ?> type="text" name="name" id="name" maxlength="255" class="form-control" placeholder="<?= esc(lang('backend/projects.placeholders.name'), 'attr'); ?>" aria-required="true">
                                    <div class="error_name app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
                                </div>
                            </div>
                            <div class="col-12 col-lg-6">
                                <div class="mb-2">
                                    <label for="parent_project_uuid" class="form-label"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/projects.labels.parentProject', [], false); ?></label>
                                    <select name="parent_project_uuid" id="parent_project_uuid" class="form-select">
                                        <option <?= ui_binding('backend/projects.labels.noParent', 'text', [], false); ?> value=""><?= lang('backend/projects.labels.noParent'); ?></option>
                                        <?php foreach($parentProjectOptions as $parentProject): ?>
                                            <option value="<?= esc($parentProject->uuid, 'attr'); ?>"><?= esc($parentProject->name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="error_parent_project_uuid app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
                                </div>
                            </div>
                            <div class="col-12 ">
                                <div class="mb-2">
                                    <label for="description" class="form-label"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/projects.labels.description', [], false); ?></label>
                                    <textarea <?= ui_binding('backend/projects.placeholders.description', 'placeholder', [], true); ?> name="description" id="description" rows="5" class="form-control" placeholder="<?= esc(lang('backend/projects.placeholders.description'), 'attr'); ?>"></textarea>
                                    <div class="error_description app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
                                </div>
                            </div>
                            <div class="col-12 col-lg-6">
                                <div class="mb-2">
                                    <label for="local_path" class="form-label"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/projects.labels.localPath', [], false); ?></label>
                                    <input <?= ui_binding('backend/projects.placeholders.localPath', 'placeholder', [], true); ?> type="text" name="local_path" id="local_path" maxlength="1024" class="form-control" placeholder="<?= esc(lang('backend/projects.placeholders.localPath'), 'attr'); ?>">
                                    <div class="error_local_path app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
                                </div>
                            </div>
                            <div class="col-12 col-lg-6">
                                <div class="mb-2">
                                    <label for="local_url" class="form-label"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/projects.labels.localUrl', [], false); ?></label>
                                    <input <?= ui_binding('backend/projects.placeholders.localUrl', 'placeholder', [], true); ?> type="url" name="local_url" id="local_url" maxlength="2048" class="form-control" placeholder="<?= esc(lang('backend/projects.placeholders.localUrl'), 'attr'); ?>">
                                    <div class="error_local_url app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
                                </div>
                            </div>
                            <div class="col-12 col-lg-6">
                                <div class="mb-2">
                                    <label for="production_url" class="form-label"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/projects.labels.productionUrl', [], false); ?></label>
                                    <input <?= ui_binding('backend/projects.placeholders.productionUrl', 'placeholder', [], true); ?> type="url" name="production_url" id="production_url" maxlength="2048" class="form-control" placeholder="<?= esc(lang('backend/projects.placeholders.productionUrl'), 'attr'); ?>">
                                    <div class="error_production_url app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
                                </div>
                            </div>
                            <div class="col-12 col-lg-6">
                                <div class="mb-2">
                                    <label for="execute_name" class="form-label"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/projects.labels.executeName', [], false); ?></label>
                                    <input <?= ui_binding('backend/projects.placeholders.executeName', 'placeholder', [], true); ?> type="text" name="execute_name" id="execute_name" maxlength="100" class="form-control" placeholder="<?= esc(lang('backend/projects.placeholders.executeName'), 'attr'); ?>">
                                    <div class="error_execute_name app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
