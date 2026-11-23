<style>
    .task-related-panel .card-header { gap: .5rem; flex-wrap: wrap; border-top: 1px solid var(--app-border) !important; }
    .task-related-item + .task-related-item { border-top: 1px solid color-mix(in srgb, var(--app-border) 30%, transparent); }
    .task-related-toggle { display: flex; align-items: center; gap: .65rem; width: 100%; padding: .6rem .8rem; border: 0; text-align: left; color: inherit; background: transparent; }
    .task-note-header { background: var(--app-surface-muted); }
    .task-note-header:hover { background: color-mix(in srgb, var(--app-surface-inverse) 10%, var(--app-surface-muted)); }
    .task-related-toggle:focus-visible { outline: 2px solid currentColor; outline-offset: -2px; }
    .task-related-toggle > .fa-chevron-right { font-size: .75rem; transition: transform .3s ease; }
    .task-related-toggle[aria-expanded="true"] > .fa-chevron-right { transform: rotate(90deg); }
    .task-related-summary { min-width: 0; flex: 1; }
    .task-related-title { display: block; overflow-wrap: anywhere; }
    .task-related-meta { display: flex; align-items: center; flex-wrap: wrap; gap: .35rem .7rem; margin-top: .15rem; font-size: .8rem; }
    .task-related-detail { padding: .75rem .875rem .875rem 2.15rem; font-size: .9rem; line-height: 1.65; background: color-mix(in srgb, var(--app-page-bg) 60%, var(--app-surface)); overflow-wrap: anywhere; }
    .task-related-text { white-space: pre-wrap; margin: 0; }
    .task-note-header .task-related-toggle { min-width: 0; }
    @media (max-width: 575.98px) {
        .task-note-header { flex-wrap: wrap; }
        .task-note-header > .task-related-toggle { flex-basis: 100%; }
        .task-note-actions { margin-inline-start: auto; padding-block-end: .25rem; }
    }
    .task-related-steps { margin: .4rem 0 .65rem; padding: 0; list-style: none; }
    .task-related-steps li { display: flex; align-items: baseline; gap: .5rem; padding: .15rem 0; }
    .task-related-panel .collapsing { transition: height .3s ease-in-out; }
    @media (prefers-reduced-motion: reduce) {
        .task-related-toggle > .fa-chevron-right, .task-related-panel .collapsing { transition: none; }
    }
    :is(#add-tasks-container, #edit-tasks-container, #show-tasks-container) .card-header > .card-title {
        font-size: 1.4rem;
        font-weight: 700;
        line-height: 1.4;
    }
    :is(#add-tasks-container, #edit-tasks-container, #show-tasks-container) .card-header {
        min-block-size: 3.25rem;
        padding-block: .625rem;
    }
    [data-task-related-panel] [data-related-items]:has(.task-related-item) { padding: .75rem; }
    .task-related-item[data-task-note] {
        border: 1px solid color-mix(in srgb, var(--app-border) 30%, transparent);
        border-radius: .25rem;
    }
    .task-related-item[data-task-note] + .task-related-item { margin-block-start: .5rem; }
    .task-status.badge {
        --task-status-color: var(--app-text);
        --task-status-background: var(--app-surface-muted);
        color: var(--task-status-color);
        background: var(--task-status-background);
        border: 1px solid color-mix(in srgb, var(--task-status-color) 35%, transparent);
        padding: .3em .55em;
        font-size: .8rem;
        font-weight: 700;
    }
    .task-status[data-task-status="in_progress"] { --task-status-color: #79520c; --task-status-background: #fff0c2; }
    .task-status[data-task-status="completed"] { --task-status-color: #146c43; --task-status-background: #dceee3; }
    .task-status[data-task-status="todo"] { --task-status-background: #ffffff; }
    .task-current-pin { color: var(--bs-primary); font-size: 1rem; line-height: 1; }
    #tasks-list .task-list-status { display: inline-grid; grid-template-columns: 1rem 7.5rem; align-items: center; gap: .5rem; vertical-align: middle; }
    #tasks-list .task-list-status > .task-status { inline-size: 100%; }
    #tasks-list .task-list-pin-slot { display: inline-flex; align-items: center; justify-content: center; }
    #tasks-list .task-notes-count.badge {
        color: #146c43;
        background: #dceee3;
        border: 1px solid #a3cfbb;
        font-size: .8rem;
        padding: .3em .55em;
    }
    .task-steps-progress { display: inline-flex; align-items: center; gap: .5rem; inline-size: 8rem; vertical-align: middle; }
    .task-steps-progress .progress { flex: 1; min-inline-size: 2.5rem; block-size: .4rem; background: #bfc6cd; }
    .task-steps-progress .progress-bar { background: var(--bs-primary); }
    .task-steps-progress[data-steps-complete="true"] .progress-bar { background: var(--bs-success); }
    .task-steps-count { flex-shrink: 0; white-space: nowrap; font-size: .8rem; }
    #tasks-list tr[data-task-current="true"] > td {
        --bs-table-bg: #cbd8df;
        --bs-table-color: #495057;
        --bs-table-accent-bg: transparent;
        --bs-table-striped-bg: #cbd8df;
        --bs-table-striped-color: #495057;
        --bs-table-hover-bg: #cbd8df;
        --bs-table-hover-color: #495057;
    }
    #tasks-list tr[data-task-current="true"] .task-current-pin { color: #405d6b !important; }
    #tasks-list tr[data-task-current="true"] .toggle-meta-btn { color: #495057 !important; }
    :is(#edit-tasks-container, #show-tasks-container) #metaData { font-size: .9rem; line-height: 1.6; }
</style>
