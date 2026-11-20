<?php $fieldPrefix = $fieldPrefix ?? 'note'; ?>
<div>
    <label for="<?= esc($fieldPrefix, 'attr'); ?>-content" class="form-label required-field"><?= ui_text('backend/notes.labels.content', [], false); ?></label>
    <textarea id="<?= esc($fieldPrefix, 'attr'); ?>-content" name="content" class="form-control" rows="5"><?= esc($note->content ?? ''); ?></textarea>
    <div class="app-text-danger fw-bold small pt-1" data-work-field-error="content" aria-live="polite"></div>
</div>
