<div class="task-modal-note-group" data-modal-note-group>
    <div class="d-flex align-items-center justify-content-between gap-2">
        <span class="small fw-bold"><?= esc($noteGroupLabel); ?></span>
        <?php if ($canAddNotes): ?>
            <button type="button" class="btn btn-link task-modal-note-add" data-modal-note-add aria-label="<?= esc(lang('backend/tasks.actions.addNote'), 'attr'); ?>" title="<?= esc(lang('backend/tasks.actions.addNote'), 'attr'); ?>"><i class="fa-solid fa-note-sticky" aria-hidden="true"></i></button>
        <?php endif; ?>
    </div>
    <div data-modal-note-list></div>
    <p class="small app-text-muted mb-0" data-modal-note-empty><?= ui_text('backend/tasks.modal.noNotes', [], false); ?></p>
</div>
