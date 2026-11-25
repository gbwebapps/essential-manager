<?= view(\Modules\Backend\Tasks\Config\Views::ACTION_LINKS_STYLE_PARTIAL, [], ['saveData' => false]); ?>
<?php if (($draftMode ?? false) === true): ?>
    <?= view(\Modules\Backend\Projects\Config\Views::SECTION_STYLE_PARTIAL, [], ['saveData' => false]); ?>
<?php endif; ?>
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
    .project-related-panel .collapsing { transition: height .3s ease-in-out; }
    @media (prefers-reduced-motion: reduce) {
        [data-project-draft-root] .project-related-toggle > .fa-chevron-right, .project-related-panel .collapsing { transition: none; }
    }
</style>
<?php foreach ($relatedPanels as $relatedKind => $relatedPanel): ?>
    <?php if ( ! \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, $relatedPanel['permission'])): continue; endif; ?>
    <section class="project-related-panel task-related-panel" data-project-related-panel="<?= $relatedKind; ?>" <?= $isDraft ? 'data-project-draft-panel' : ''; ?> data-read-only="<?= $relatedReadOnly ? 'true' : 'false'; ?>" data-project-uuid="<?= esc($project->uuid ?? '', 'attr'); ?>" data-project-name="<?= esc($project->name ?? '', 'attr'); ?>" data-reload-error="<?= esc(lang('backend/projects.related.reloadError'), 'attr'); ?>">
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
