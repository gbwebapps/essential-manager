<div class="card-body">
    <div class="row">
        <div class="col-12">
            <div class="mb-2">
                <label for="name" class="form-label required-field"><i class="fa-solid fa-tag fa-fw me-1" aria-hidden="true"></i><?= ui_text('backend/checklists.labels.name', [], false); ?></label>
                <input type="text" <?= ui_binding('backend/checklists.placeholders.name', 'placeholder', [], true); ?> name="name" id="name" class="form-control" placeholder="<?= esc(lang('backend/checklists.placeholders.name'), 'attr'); ?>" value="<?= esc($checklist->name ?? '', 'attr'); ?>" aria-required="true">
                <div class="error_name app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
            </div>
        </div>
        <div class="col-12">
            <div class="mb-2">
                <label for="description" class="form-label"><i class="fa-solid fa-align-left fa-fw me-1" aria-hidden="true"></i><?= ui_text('backend/checklists.labels.description', [], false); ?></label>
                <textarea <?= ui_binding('backend/checklists.placeholders.description', 'placeholder', [], true); ?> name="description" id="description" class="form-control" rows="3" placeholder="<?= esc(lang('backend/checklists.placeholders.description'), 'attr'); ?>"><?= esc($checklist->description ?? ''); ?></textarea>
                <div class="error_description app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
            </div>
        </div>
        <div class="col-12">
            <fieldset class="mb-0" data-task-steps data-standalone="true" data-field-prefix="checklists-main" data-step-label="<?= esc(lang('backend/tasks.labels.step'), 'attr'); ?>">
                <legend class="form-label fs-6 float-none"><i class="fa-solid fa-list-check fa-fw me-1" aria-hidden="true"></i><?= ui_text('backend/checklists.labels.steps', [], false); ?></legend>
                <div data-task-step-list>
                    <?php foreach ($checklist->steps ?? [(object) ['title' => '']] as $stepIndex => $step): ?>
                        <?= view(\Modules\Backend\Checklists\Config\Views::STEP_PARTIAL, ['stepIndex' => $stepIndex, 'stepUuid' => $step->uuid ?? '', 'stepTitle' => $step->title], ['saveData' => false]); ?>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="btn btn-sm app-btn-secondary mt-1" data-task-step-add><i class="fa-solid fa-plus me-1" aria-hidden="true"></i><?= ui_text('backend/tasks.actions.addStep', [], false); ?></button>
                <template data-task-step-template><?= view(\Modules\Backend\Checklists\Config\Views::STEP_PARTIAL, ['stepIndex' => '__new__', 'stepUuid' => '', 'stepTitle' => ''], ['saveData' => false]); ?></template>
                <div class="error_steps step-editor-feedback app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
            </fieldset>
            <div class="error_payload step-editor-feedback app-text-danger fw-bold small pt-1" aria-live="polite">&nbsp;</div>
        </div>
    </div>
</div>
