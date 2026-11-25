<div class="card-body pb-0">
    <div class="row">
        <div class="col-12 col-lg-6">
            <div class="mb-2">
                <label for="name" class="form-label required-field"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/applications.labels.name', [], false); ?></label>
                <input <?= ui_binding('backend/applications.placeholders.name', 'placeholder', [], true); ?> type="text" name="name" id="name" value="<?= esc($application->name ?? '', 'attr'); ?>" class="form-control" placeholder="<?= esc(lang('backend/applications.placeholders.name'), 'attr'); ?>" aria-required="true">
                <div class="error_name app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="mb-2">
                <label for="project_uuid" class="form-label required-field"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/applications.labels.project', [], false); ?></label>
                <select name="project_uuid" id="project_uuid" class="form-select" aria-required="true">
                    <option <?= ui_binding('backend/applications.labels.noParent', 'text', [], false); ?> value=""><?= lang('backend/applications.labels.noParent'); ?></option>
                    <?php foreach($projectOptions as $project): ?>
                        <option value="<?= esc($project->uuid, 'attr'); ?>"<?= ($application->project_uuid === $project->uuid) ? ' selected' : ''; ?>><?= esc($project->name); ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="error_project_uuid app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="mb-2">
                <label for="local_path" class="form-label"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/applications.labels.localPath', [], false); ?></label>
                <input <?= ui_binding('backend/applications.placeholders.localPath', 'placeholder', [], true); ?> type="text" name="local_path" id="local_path" value="<?= esc($application->local_path ?? '', 'attr'); ?>" class="form-control" placeholder="<?= esc(lang('backend/applications.placeholders.localPath'), 'attr'); ?>">
                <div class="error_local_path app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="mb-2">
                <label for="local_url" class="form-label"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/applications.labels.localUrl', [], false); ?></label>
                <input <?= ui_binding('backend/applications.placeholders.localUrl', 'placeholder', [], true); ?> type="text" inputmode="url" name="local_url" id="local_url" value="<?= esc($application->local_url ?? '', 'attr'); ?>" class="form-control" placeholder="<?= esc(lang('backend/applications.placeholders.localUrl'), 'attr'); ?>">
                <div class="error_local_url app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="mb-2">
                <label for="production_url" class="form-label"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/applications.labels.productionUrl', [], false); ?></label>
                <input <?= ui_binding('backend/applications.placeholders.productionUrl', 'placeholder', [], true); ?> type="text" inputmode="url" name="production_url" id="production_url" value="<?= esc($application->production_url ?? '', 'attr'); ?>" class="form-control" placeholder="<?= esc(lang('backend/applications.placeholders.productionUrl'), 'attr'); ?>">
                <div class="error_production_url app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="mb-2">
                <label for="execute_name" class="form-label"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/applications.labels.executeName', [], false); ?></label>
                <input <?= ui_binding('backend/applications.placeholders.executeName', 'placeholder', [], true); ?> type="text" name="execute_name" id="execute_name" value="<?= esc($application->execute_name ?? '', 'attr'); ?>" class="form-control" placeholder="<?= esc(lang('backend/applications.placeholders.executeName'), 'attr'); ?>">
                <div class="error_execute_name app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
            </div>
        </div>
        <div class="col-12 ">
            <div class="mb-2">
                <label for="description" class="form-label"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/applications.labels.description', [], false); ?></label>
                <textarea <?= ui_binding('backend/applications.placeholders.description', 'placeholder', [], true); ?> name="description" id="description" rows="5" class="form-control" placeholder="<?= esc(lang('backend/applications.placeholders.description'), 'attr'); ?>"><?= esc($application->description ?? ''); ?></textarea>
                <div class="error_description app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
            </div>
        </div>
    </div>
</div>
