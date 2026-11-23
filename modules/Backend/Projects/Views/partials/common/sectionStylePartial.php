<?= view(\Modules\Backend\Tasks\Config\Views::SECTION_STYLE_PARTIAL, [], ['saveData' => false]); ?>
<style>
    #projects-list .project-count.badge {
        color: var(--app-text);
        background: #ffffff;
        border: 1px solid color-mix(in srgb, var(--app-text) 35%, transparent);
        font-size: .8rem;
        font-weight: 700;
        padding: .3em .55em;
    }
    #projects-list .project-count[data-count-kind="tasks"] { color: #79520c; background: #fff0c2; }
    #projects-list .project-count[data-count-kind="notes"] { color: #146c43; background: #dceee3; border-color: #a3cfbb; }
    :is(#edit-projects-container, #show-projects-container) .card-header > .card-title { font-size: 1.4rem; font-weight: 700; line-height: 1.4; }
    :is(#edit-projects-container, #show-projects-container) .card-header { min-block-size: 3.25rem; padding-block: .625rem; }
    .project-related-panel:not([data-project-draft-panel]) [data-related-items]:has(.task-related-item) { padding: .75rem; }
    .project-related-panel .task-related-item { border: 1px solid color-mix(in srgb, var(--app-border) 30%, transparent); border-radius: .25rem; }
    .project-related-panel .task-related-item + .task-related-item { margin-block-start: .5rem; }
    .project-related-detail .project-related-text { white-space: pre-wrap; margin: 0; }
    .project-related-detail .project-related-steps { margin: .75rem 0 0; padding: 0; list-style: none; }
    .project-related-detail .project-related-steps li { display: flex; align-items: baseline; gap: .5rem; padding: .15rem 0; }
    .project-related-detail .project-related-steps .fa-square-check { color: var(--bs-success); }
</style>
