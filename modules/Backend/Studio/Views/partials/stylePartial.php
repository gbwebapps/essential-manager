<style>
    #studio-workspace { margin-bottom: 1rem; }
    .studio-tabs { border-bottom: 1px solid var(--app-border); margin-bottom: 1rem; gap: 0; }
    .studio-tab.nav-link { display: flex; align-items: center; gap: .35rem; padding: .65rem 1.3rem; color: var(--app-text) !important; border: 1px solid transparent; border-bottom: 0; border-radius: .25rem .25rem 0 0; }
    .studio-tab.nav-link.active { color: var(--app-text); background: var(--app-surface); border-color: var(--app-border); position: relative; bottom: -1px; }
    .studio-panel { margin-bottom: .75rem; overflow: hidden; }
    .studio-panel-heading { display: flex; justify-content: space-between; align-items: center; gap: 1rem; padding: .8rem 1rem; }
    .studio-task-toggle { display: flex; align-items: flex-start; gap: .65rem; flex: 1; min-width: 0; padding: 0; border: 0; background: transparent; color: inherit; text-align: left; }
    .studio-task-toggle > i { margin-top: .35rem; font-size: .75rem; transition: transform .2s ease; }
    .studio-task-toggle[aria-expanded="true"] > i { transform: rotate(90deg); }
    .studio-task-title { display: block; overflow-wrap: anywhere; }
    .studio-task-heading-text { min-width: 0; }
    .studio-task-meta { display: flex; flex-wrap: wrap; align-items: center; gap: .5rem .85rem; font-size: .75rem; margin-top: .3rem; }
    .studio-panel-actions { display: flex; align-items: center; justify-content: flex-end; gap: .5rem; flex-shrink: 0; }
    .studio-panel-actions form { margin: 0; }
    .studio-panel-actions .btn-link { padding: .2rem; text-decoration: none; box-shadow: none !important; }
    :is(.studio-task-panel, .studio-note-panel) > .studio-panel-heading { border-bottom: 0 !important; }
    .studio-task-body { border-top: 1px solid var(--app-border); padding: 1rem; }
    .studio-task-description { white-space: pre-wrap; margin: 0 0 1rem; }
    .studio-task-body > .card-header { padding: 0; border: 0 !important; background: none !important; margin-bottom: .65rem; }
    .studio-task-body > .card-header h2 { background: none !important; }
    .studio-task-body > .card-header h2 { font-size: .9rem; font-weight: 700; }
    .studio-task-body > .card-body { padding: 0; }
    .studio-task-body [data-task-work-steps] { gap: .65rem; }
    .studio-task-notes { margin-top: .9rem; padding-top: .75rem; border-top: 1px solid var(--app-border); }
    .studio-task-notes h3 { margin: 0; font-size: .95rem; }
    .studio-note-title { min-width: 0; overflow-wrap: anywhere; }
    .studio-note-body { border-top: 1px solid var(--app-border); }
    .studio-list-controls { margin-inline: 0; align-items: end; margin-bottom: 0; }
    .studio-list-controls .form-label { font-size: .875rem; }
    .studio-list-controls > :first-child { padding-inline-start: 0; }
    .studio-list-controls > :last-child { padding-inline-end: 0; }
    .studio-pagination > .row { margin-inline: 0; align-items: center; }
    .studio-pagination { padding: 0; }
    .studio-pagination-top { margin-bottom: 1rem; }
    .studio-pagination-bottom { margin-top: 1rem; }
    .studio-records { display: grid; gap: .75rem; }
    .studio-records > .studio-panel { margin-bottom: 0; }
    .studio-pagination .pagination.fw-bold { font-weight: var(--bs-body-font-weight, 400) !important; }
    .studio-pagination .pagination-left, .studio-pagination .pagination-right { padding: .2rem .75rem; }
    .studio-pagination .pagination-center { padding: 0; margin: 0; }
    .studio-results[aria-busy="true"] { opacity: .65; pointer-events: none; }
    .studio-note-content { white-space: pre-wrap; margin: 0; }
    #studio-workspace .task-notes-count.badge { color: #146c43; background: #dceee3; border: 1px solid #a3cfbb; font-size: .8rem; padding: .3em .55em; font-weight: 700; }
    .studio-empty { margin: 0; padding: .75rem; font-size: .875rem; }
    .studio-current-task > .studio-panel-heading { background: #cbd8df !important; }
    .studio-current-task .studio-task-toggle > i { color: var(--app-text); }
    .studio-panel-actions { flex-wrap: wrap; }
    @media (max-width: 767.98px) {
        .studio-panel-heading { display: grid; grid-template-columns: minmax(0, 1fr); gap: .65rem; padding: .75rem; }
        .studio-panel-actions { margin-inline-start: 0; justify-self: end; }
        .studio-task-toggle { width: 100%; }
        .studio-task-body { padding: .75rem; }
        .studio-task-meta { gap: .35rem .65rem; }
    }
    @media (prefers-reduced-motion: reduce) { .studio-task-toggle > i { transition: none; } }
</style>
