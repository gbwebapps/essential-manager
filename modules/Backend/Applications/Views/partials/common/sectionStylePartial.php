<?= view(\Modules\Backend\Tasks\Config\Views::SECTION_STYLE_PARTIAL, [], ['saveData' => false]); ?>
<style>
    #applications-list .application-count.badge {
        color: var(--app-text);
        background: #ffffff;
        border: 1px solid color-mix(in srgb, var(--app-text) 35%, transparent);
        font-size: .8rem;
        font-weight: 700;
        padding: .3em .55em;
    }
    #applications-list .application-count[data-count-kind="tasks"] { color: #79520c; background: #fff0c2; }
    #applications-list .application-count[data-count-kind="notes"] { color: #146c43; background: #dceee3; border-color: #a3cfbb; }
    :is(#edit-applications-container, #show-applications-container) .card-header > .card-title { font-size: 1.4rem; font-weight: 700; line-height: 1.4; }
    :is(#edit-applications-container, #show-applications-container) .card-header { min-block-size: 3.25rem; padding-block: .625rem; }
    .application-related-panel:not([data-application-draft-panel]) [data-related-items]:has(.task-related-item) { padding: .75rem; }
    .application-related-panel .task-related-item { border: 1px solid color-mix(in srgb, var(--app-border) 30%, transparent); border-radius: .25rem; }
    .application-related-panel .task-related-item + .task-related-item { margin-block-start: .5rem; }
    .application-related-detail .project-related-text { white-space: pre-wrap; margin: 0; }
    .application-related-detail .project-related-steps { margin: .75rem 0 0; padding: 0; list-style: none; }
    .application-related-detail .project-related-steps li { display: flex; align-items: baseline; gap: .5rem; padding: .15rem 0; }
    .application-related-detail .project-related-steps .fa-square-check { color: var(--bs-success); }
</style>
