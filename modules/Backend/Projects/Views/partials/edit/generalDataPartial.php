<div class="card-body pb-0">
    <div class="row">
        <div class="col-12 col-lg-6">
            <div class="mb-2">
                <label for="name" class="form-label required-field"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/projects.labels.name', [], false); ?></label>
                <input <?= ui_binding('backend/projects.placeholders.name', 'placeholder', [], true); ?> type="text" name="name" id="name" value="<?= esc($project->name ?? '', 'attr'); ?>" class="form-control" placeholder="<?= esc(lang('backend/projects.placeholders.name'), 'attr'); ?>" aria-required="true">
                <div class="error_name app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="mb-2">
                <label for="parent_project_uuid" class="form-label"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/projects.labels.parentProject', [], false); ?></label>
                <select name="parent_project_uuid" id="parent_project_uuid" class="form-select">
                    <option <?= ui_binding('backend/projects.labels.noParent', 'text', [], false); ?> value=""><?= lang('backend/projects.labels.noParent'); ?></option>
                    <?php foreach($parentProjectOptions as $parentProject): ?>
                        <?php if($parentProject->uuid !== $project->uuid): ?>
                        <option value="<?= esc($parentProject->uuid, 'attr'); ?>"<?= ($project->parent_project_uuid === $parentProject->uuid) ? ' selected' : ''; ?>><?= esc($parentProject->name); ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                <div class="error_parent_project_uuid app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
            </div>
        </div>
        <div class="col-12 ">
            <div class="mb-2">
                <label for="description" class="form-label"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/projects.labels.description', [], false); ?></label>
                <textarea <?= ui_binding('backend/projects.placeholders.description', 'placeholder', [], true); ?> name="description" id="description" rows="5" class="form-control" placeholder="<?= esc(lang('backend/projects.placeholders.description'), 'attr'); ?>"><?= esc($project->description ?? ''); ?></textarea>
                <div class="error_description app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="mb-2">
                <label for="local_path" class="form-label"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/projects.labels.localPath', [], false); ?></label>
                <input <?= ui_binding('backend/projects.placeholders.localPath', 'placeholder', [], true); ?> type="text" name="local_path" id="local_path" value="<?= esc($project->local_path ?? '', 'attr'); ?>" class="form-control" placeholder="<?= esc(lang('backend/projects.placeholders.localPath'), 'attr'); ?>">
                <div class="error_local_path app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="mb-2">
                <label for="local_url" class="form-label"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/projects.labels.localUrl', [], false); ?></label>
                <input <?= ui_binding('backend/projects.placeholders.localUrl', 'placeholder', [], true); ?> type="text" inputmode="url" name="local_url" id="local_url" value="<?= esc($project->local_url ?? '', 'attr'); ?>" class="form-control" placeholder="<?= esc(lang('backend/projects.placeholders.localUrl'), 'attr'); ?>">
                <div class="error_local_url app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="mb-2">
                <label for="production_url" class="form-label"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/projects.labels.productionUrl', [], false); ?></label>
                <input <?= ui_binding('backend/projects.placeholders.productionUrl', 'placeholder', [], true); ?> type="text" inputmode="url" name="production_url" id="production_url" value="<?= esc($project->production_url ?? '', 'attr'); ?>" class="form-control" placeholder="<?= esc(lang('backend/projects.placeholders.productionUrl'), 'attr'); ?>">
                <div class="error_production_url app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="mb-2">
                <label for="execute_name" class="form-label"><i class="fa-solid fa-circle-arrow-down"></i><?= ui_text('backend/projects.labels.executeName', [], false); ?></label>
                <input <?= ui_binding('backend/projects.placeholders.executeName', 'placeholder', [], true); ?> type="text" name="execute_name" id="execute_name" value="<?= esc($project->execute_name ?? '', 'attr'); ?>" class="form-control" placeholder="<?= esc(lang('backend/projects.placeholders.executeName'), 'attr'); ?>">
                <div class="error_execute_name app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
            </div>
        </div>
    </div>
</div>
