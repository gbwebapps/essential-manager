<style>
    .owner-task-description { white-space: pre-wrap; line-height: 1.65; margin: 0 0 .75rem; }
    .owner-task-steps { list-style: none; padding: 0; margin: .75rem 0 0; display: grid; gap: .65rem; }
    .owner-task-step { padding: .65rem; border: 1px solid color-mix(in srgb, var(--app-border) 30%, transparent); border-radius: .25rem; background: color-mix(in srgb, var(--app-surface) 80%, var(--app-page-bg)); }
    .owner-step-row { display: grid; grid-template-columns: 1.75rem auto minmax(0,1fr); align-items: center; gap: .5rem; }
    .owner-step-label { cursor: pointer; overflow-wrap: anywhere; }
    .owner-step-row .form-check-input { cursor: pointer; margin: 0; }
    .owner-step-notes { margin: .6rem 0 0 2.25rem; }
    .owner-note { border: 1px solid color-mix(in srgb, var(--app-border) 30%, transparent); border-radius: .25rem; overflow: hidden; }
    .owner-note + .owner-note { margin-top: .4rem; }
    .owner-note-title { min-width: 0; display: flex; align-items: center; flex: 1; padding: .5rem .65rem; }
    .owner-note-content { white-space: pre-wrap; overflow-wrap: anywhere; line-height: 1.65; padding: .65rem .8rem; background: color-mix(in srgb, var(--app-page-bg) 60%, var(--app-surface)); }
    .owner-task-notes { margin-bottom: .75rem; }
    @media(max-width:575.98px) {
        .owner-step-notes { margin-inline-start: .5rem; }
        .owner-note > .task-note-header { flex-wrap: wrap; }
        .owner-note > .task-note-header > .btn-link { margin-inline-start: auto; }
    }
</style>
