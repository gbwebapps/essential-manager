<?php if (\App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, 'checklists_apply')): ?>
    <?php $pickerId = $pickerId ?? 'task-checklist'; ?>
    <?php $availableChecklists = model(\Modules\Backend\Checklists\Models\ChecklistsModel::class)->options(); ?>
    <div class="mb-3" data-checklist-picker data-endpoint="<?= esc(base_url('backend/checklists/getItems'), 'attr'); ?>" data-error="<?= esc(lang('backend/checklists.messages.saveError'), 'attr'); ?>" data-limit="<?= esc(lang('backend/checklists.messages.stepLimit'), 'attr'); ?>">
        <label for="<?= esc($pickerId, 'attr'); ?>" class="form-label" data-checklist-label><?= ui_text('backend/checklists.labels.template', [], false); ?></label>
        <div class="d-flex flex-nowrap align-items-center gap-2">
            <div class="flex-grow-1" style="min-width: 0; flex-basis: 0;">
                <select id="<?= esc($pickerId, 'attr'); ?>" class="form-select" data-checklist-choice <?= $availableChecklists === [] ? 'disabled' : ''; ?>>
                    <option <?= ui_binding('backend/checklists.labels.choose', 'text', [], false); ?> value=""><?= esc(lang('backend/checklists.labels.choose')); ?></option>
                    <?php foreach ($availableChecklists as $available): ?><option value="<?= esc($available->uuid, 'attr'); ?>"><?= esc($available->name); ?></option><?php endforeach; ?>
                </select>
            </div>
            <button type="button" class="btn btn-sm app-btn-secondary flex-shrink-0 text-nowrap" data-checklist-apply <?= $availableChecklists === [] ? 'disabled' : ''; ?>><i class="fa-solid fa-clipboard-check me-1" aria-hidden="true"></i><?= ui_text('backend/checklists.actions.apply', [], false); ?></button>
        </div>
    </div>
<?php endif; ?>
