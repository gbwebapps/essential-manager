<?php
    $isDraft = ($draftMode ?? false) === true;
    $hasProject = ! empty($project->uuid);
    $relatedReadOnly = ! $isDraft && ($action ?? '') === 'show';
    $relatedPanels = [
        'task' => ['permission' => 'tasks_show', 'addPermission' => 'tasks_add', 'items' => $projectTasks ?? []],
        'note' => ['permission' => 'notes_show', 'addPermission' => 'notes_add', 'items' => $projectNotes ?? []],
    ];
?>
<style>
    .project-related-panel .card-header { gap: .5rem; flex-wrap: wrap; border-top: 1px solid var(--app-border) !important; }
    .project-related-item + .project-related-item { border-top: 1px solid var(--app-border); }
    .project-related-toggle { display: flex; align-items: center; gap: .65rem; width: 100%; padding: .6rem .8rem; border: 0; text-align: left; color: inherit; background: transparent; }
    .project-related-toggle:hover, .project-related-toggle:focus-visible { background: var(--app-surface-muted); }
    .project-related-toggle > .fa-chevron-right { font-size: .75rem; transition: transform .3s ease; }
    .project-related-toggle[aria-expanded="true"] > .fa-chevron-right { transform: rotate(90deg); }
    .project-related-summary { min-width: 0; flex: 1; }
    .project-related-title { display: block; overflow-wrap: anywhere; }
    .project-related-meta { display: flex; align-items: center; flex-wrap: wrap; gap: .35rem .7rem; margin-top: .15rem; font-size: .8rem; }
    .project-related-detail { padding: 0 .8rem .7rem 2.2rem; font-size: .9rem; overflow-wrap: anywhere; }
    .project-related-text { white-space: pre-wrap; margin-bottom: .5rem; }
    .project-related-steps { margin: .4rem 0 .65rem; padding: 0; list-style: none; }
    .project-related-steps li { display: flex; align-items: baseline; gap: .5rem; padding: .15rem 0; }
    .project-related-panel .collapsing { transition: height .3s ease-in-out; }
    @media (prefers-reduced-motion: reduce) {
        .project-related-toggle > .fa-chevron-right, .project-related-panel .collapsing { transition: none; }
    }
</style>
<?php foreach ($relatedPanels as $relatedKind => $relatedPanel): ?>
    <?php if ( ! \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, $relatedPanel['permission'])): continue; endif; ?>
    <section class="project-related-panel" data-project-related-panel="<?= $relatedKind; ?>" <?= $isDraft ? 'data-project-draft-panel' : ''; ?> data-read-only="<?= $relatedReadOnly ? 'true' : 'false'; ?>" data-project-uuid="<?= esc($project->uuid ?? '', 'attr'); ?>" data-project-name="<?= esc($project->name ?? '', 'attr'); ?>" data-reload-error="<?= esc(lang('backend/projects.related.reloadError'), 'attr'); ?>">
        <div class="card-header rounded-0 d-flex justify-content-between align-items-center">
            <h2 class="card-title text-start mb-0"><?= ui_text('backend/projects.related.' . $relatedKind . 's', [], false); ?></h2>
            <div class="d-flex flex-wrap gap-1">
                <?php if (($hasProject || $isDraft) && ! $relatedReadOnly && \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, $relatedPanel['addPermission'])): ?>
                    <button type="button" class="btn btn-sm app-btn-secondary" data-bs-toggle="modal" data-bs-target="#projects-add-<?= $relatedKind; ?>-modal">
                        <i class="fa-solid fa-plus me-1" aria-hidden="true"></i><?= ui_text('backend/projects.actions.' . ($relatedKind === 'task' ? 'addTask' : 'addNote'), [], false); ?>
                    </button>
                <?php endif; ?>
                <button type="button" class="btn btn-sm app-btn-secondary" data-related-reload aria-controls="project-<?= $relatedKind; ?>-items">
                    <i class="fa-solid fa-arrows-rotate me-1" aria-hidden="true"></i><?= ui_text('backend/projects.buttons.reload', [], false); ?>
                </button>
            </div>
        </div>
        <div class="<?= $isDraft ? 'error_' . ($relatedKind === 'task' ? 'tasks' : 'notes') . ' ' : ''; ?>app-text-danger fw-bold small px-3" data-draft-errors="<?= $relatedKind; ?>" role="status"></div>
        <div id="project-<?= $relatedKind; ?>-items" data-related-items>
            <?php if ($isDraft): ?>
                <p class="small app-text-muted px-3 py-2 mb-0"><?= ui_text('backend/projects.draft.' . ($relatedKind === 'task' ? 'noTasks' : 'noNotes'), [], false); ?></p>
            <?php else: ?>
            <?= view(\Modules\Backend\Projects\Config\Views::RELATED_ITEMS_PARTIAL, ['relatedKind' => $relatedKind, 'relatedReadOnly' => $relatedReadOnly, 'relatedItems' => $relatedPanel['items'], 'relatedProject' => $project ?? null, 'currentAdmin' => $currentAdmin ?? null], ['saveData' => false]); ?>
            <?php endif; ?>
        </div>
    </section>
<?php endforeach; ?>
