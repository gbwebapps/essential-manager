<?= view(\Modules\Backend\Template\Config\Views::MODAL_STYLE_PARTIAL, [], ['saveData' => false]); ?>
<div class="modal fade transfer-modal manager-work-modal" id="studio-note-modal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="studio-note-title" aria-hidden="true" data-studio-modal="note" data-save-notice="<?= esc(lang('backend/studio.saveNotice'), 'attr'); ?>" data-add-title="<?= esc(lang('backend/notes.titles.add'), 'attr'); ?>" data-edit-title="<?= esc(lang('backend/notes.titles.edit'), 'attr'); ?>">
    <div class="modal-dialog modal-dialog-centered transfer-export-dialog"><div class="modal-content shadow">
        <form novalidate method="post" data-studio-form="note">
            <div class="modal-header"><div><h2 class="modal-title" id="studio-note-title"><?= ui_text('backend/studio.newNote', [], false); ?></h2><p class="transfer-intro mb-0" data-studio-feedback role="status"><?= ui_text('backend/studio.saveNotice', [], false); ?></p></div><button type="button" class="btn btn-sm app-btn-danger transfer-modal-close" data-bs-dismiss="modal"><i class="fa-solid fa-xmark me-2" aria-hidden="true"></i><?= ui_text('backend/tasks.buttons.close', [], false); ?></button></div>
            <div class="modal-body"><input type="hidden" name="uuid" value="">
                <?= view(\Modules\Backend\Notes\Config\Views::FORM_FIELDS_PARTIAL, ['fieldPrefix' => 'studio-note', 'note' => null], ['saveData' => false]); ?>
                <div class="app-text-danger fw-bold small" data-work-form-errors role="status"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm app-btn-secondary" data-modal-reset><i class="fa-solid fa-arrow-rotate-left me-1" aria-hidden="true"></i><?= ui_text('backend/tasks.buttons.resetData', [], false); ?></button><button type="submit" class="btn btn-sm app-btn-success"><i class="fa-solid fa-floppy-disk me-1" aria-hidden="true"></i><?= ui_text('backend/tasks.buttons.sendData', [], false); ?></button>
            </div>
        </form>
    </div></div>
</div>
