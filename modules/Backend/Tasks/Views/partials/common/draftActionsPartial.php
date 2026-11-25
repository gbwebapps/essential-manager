<button type="button" class="btn btn-link btn-sm p-1 border-0 shadow-none task-record-link" data-bs-toggle="modal" data-bs-target="#<?= esc($ownerModule, 'attr'); ?>-add-<?= esc($relatedKind, 'attr'); ?>-modal" data-draft-id="preview">
    <i class="fa-solid fa-pen fa-fw" aria-hidden="true"></i><span class="d-none d-sm-inline"> <?= ui_text('backend/' . $ownerModule . '.actions.edit', [], false); ?></span>
</button>
<button type="button" class="btn btn-link btn-sm p-1 border-0 shadow-none task-record-link app-text-danger text-nowrap" data-draft-remove="preview">
    <i class="fa-solid fa-trash-can fa-fw" aria-hidden="true"></i><span class="d-none d-sm-inline"> <?= ui_text('backend/' . $ownerModule . '.actions.softDelete', [], false); ?></span>
</button>
