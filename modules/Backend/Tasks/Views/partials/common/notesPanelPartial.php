<style>
    .task-related-panel .card-header { gap: .5rem; flex-wrap: wrap; border-top: 1px solid var(--app-border) !important; }
    .task-related-item + .task-related-item { border-top: 1px solid var(--app-border); }
    .task-related-toggle { display: flex; align-items: center; gap: .65rem; width: 100%; padding: .6rem .8rem; border: 0; text-align: left; color: inherit; background: transparent; }
    .task-related-toggle:hover, .task-related-toggle:focus-visible { background: var(--app-surface-muted); }
    .task-related-toggle > .fa-chevron-right { font-size: .75rem; transition: transform .3s ease; }
    .task-related-toggle[aria-expanded="true"] > .fa-chevron-right { transform: rotate(90deg); }
    .task-related-summary { min-width: 0; flex: 1; }
    .task-related-title { display: block; overflow-wrap: anywhere; }
    .task-related-meta { display: flex; align-items: center; flex-wrap: wrap; gap: .35rem .7rem; margin-top: .15rem; font-size: .8rem; }
    .task-related-detail { padding: 0 .8rem .7rem 2.2rem; font-size: .9rem; overflow-wrap: anywhere; }
    .task-related-text { white-space: pre-wrap; margin-bottom: .5rem; }
    .task-note-header .task-related-toggle { min-width: 0; }
    .task-related-steps { margin: .4rem 0 .65rem; padding: 0; list-style: none; }
    .task-related-steps li { display: flex; align-items: baseline; gap: .5rem; padding: .15rem 0; }
    .task-related-panel .collapsing { transition: height .3s ease-in-out; }
    @media (prefers-reduced-motion: reduce) {
        .task-related-toggle > .fa-chevron-right, .task-related-panel .collapsing { transition: none; }
    }
</style>
<?php if (\App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, 'notes_show')): ?>
    <section class="task-related-panel" data-task-related-panel="note" data-task-uuid="<?= esc($task->uuid, 'attr'); ?>" data-reload-error="<?= esc(lang('backend/tasks.related.reloadError'), 'attr'); ?>">
        <div class="card-header rounded-0 d-flex justify-content-between align-items-center">
            <h2 class="card-title text-start mb-0"><?= ui_text('backend/tasks.panels.taskNotes', [], false); ?></h2>
            <div class="d-flex flex-wrap gap-1">
                <?php if ($task->deleted_at === null && $task->owner_deleted_at === null && \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin, 'notes_add')): ?>
                    <button type="button" class="btn btn-sm app-btn-secondary" data-bs-toggle="modal" data-bs-target="#tasks-note-modal" data-task-uuid="<?= esc($task->uuid, 'attr'); ?>" data-task-title="<?= esc($task->title, 'attr'); ?>">
                        <i class="fa-solid fa-plus me-1" aria-hidden="true"></i><?= ui_text('backend/tasks.actions.addNote', [], false); ?>
                    </button>
                <?php endif; ?>
                <button type="button" class="btn btn-sm app-btn-secondary" data-related-reload aria-controls="task-note-items">
                    <i class="fa-solid fa-arrows-rotate me-1" aria-hidden="true"></i><?= ui_text('backend/tasks.buttons.reload', [], false); ?>
                </button>
            </div>
        </div>
        <div id="task-note-items" data-related-items>
            <?= view(\Modules\Backend\Tasks\Config\Views::NOTES_PARTIAL, ['task'=>$task, 'currentAdmin'=>$currentAdmin, 'taskNotes'=>$taskNotes, 'notesForStep'=>false], ['saveData'=>false]); ?>
        </div>
    </section>
<?php endif; ?>
