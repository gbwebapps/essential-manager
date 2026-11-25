<?php $titleInputId = ($fieldPrefix ?? '') !== '' ? $fieldPrefix . '-title' : 'title'; ?>
<div class="mb-2">
    <label for="<?= esc($titleInputId, 'attr'); ?>" class="form-label required-field"><i class="fa-solid fa-tag fa-fw me-1" aria-hidden="true"></i><?= ui_text('backend/tasks.labels.title', [], false); ?></label>
    <input <?= ui_binding('backend/tasks.placeholders.title', 'placeholder', [], true); ?> type="text" name="title" id="<?= esc($titleInputId, 'attr'); ?>" class="form-control" value="<?= esc($task->title ?? '', 'attr'); ?>" placeholder="<?= esc(lang('backend/tasks.placeholders.title'), 'attr'); ?>" aria-required="true">
    <div class="error_title app-text-danger fw-bold small pt-1" data-work-field-error="title" aria-live="polite">&nbsp;</div>
</div>
