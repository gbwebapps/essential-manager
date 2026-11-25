<?php $descriptionInputId = ($fieldPrefix ?? '') !== '' ? $fieldPrefix . '-description' : 'description'; ?>
<div class="mb-2">
    <label for="<?= esc($descriptionInputId, 'attr'); ?>" class="form-label"><i class="fa-solid fa-align-left fa-fw me-1" aria-hidden="true"></i><?= ui_text('backend/tasks.labels.description', [], false); ?></label>
    <textarea <?= ui_binding('backend/tasks.placeholders.description', 'placeholder', [], true); ?> name="description" id="<?= esc($descriptionInputId, 'attr'); ?>" rows="5" class="form-control" placeholder="<?= esc(lang('backend/tasks.placeholders.description'), 'attr'); ?>"><?= esc($task->description ?? ''); ?></textarea>
    <div class="error_description app-text-danger fw-bold small pt-1" data-work-field-error="description" aria-live="polite">&nbsp;</div>
</div>
