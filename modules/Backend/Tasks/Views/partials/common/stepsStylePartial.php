<style>
    /* Ogni passo contiene visivamente anche le proprie note. */
    [data-task-work-steps], [data-task-steps][data-standalone] [data-task-step-list] {
        display: grid;
        gap: 1.25rem;
        align-content: start;
        margin: 0;
    }
    [data-task-work-steps] > [data-work-step],
    [data-task-steps][data-standalone] [data-task-step-row] {
        min-inline-size: 0;
        padding: .75rem;
        border: 1px solid color-mix(in srgb, var(--app-border) 35%, transparent);
        border-radius: .25rem;
        background: color-mix(in srgb, var(--app-page-bg) 35%, var(--app-surface));
    }
    [data-task-work-steps] { gap: .75rem; }
    [data-task-work-steps] label { line-height: 1.6; font-weight: 700; }
    [data-task-steps][data-standalone] [data-step-title] { min-inline-size: 0; }
    [data-task-steps][data-standalone] [data-task-step-list] + [data-task-step-add] { margin-block-start: .75rem !important; }
    [data-step-note-items] { margin-block-start: .5rem; margin-inline-start: 1.5rem; }
    [data-task-step-row] > [data-step-note-items] { margin-block-start: .5rem; margin-inline-start: 2.25rem; }
    [data-step-note-items]:not(:has(.task-related-item)) { margin: 0; }
    [data-step-note-items] .task-related-item {
        border: 1px solid color-mix(in srgb, var(--app-border) 30%, transparent);
        border-radius: .25rem;
    }
    [data-step-note-items] .task-related-item + .task-related-item { margin-block-start: .5rem; }
    [data-step-note-items] .task-note-header { background: var(--app-surface-muted); border-radius: .2rem; }
    [data-step-note-items] .task-note-header:hover {
        background: color-mix(in srgb, var(--app-surface-inverse) 10%, var(--app-surface-muted));
    }
    [data-step-note-items] .task-related-toggle { padding: .5rem .75rem; line-height: 1.5; }
    [data-step-note-items] .task-related-toggle:hover,
    [data-step-note-items] .task-related-toggle:focus-visible { background: transparent; }
    [data-step-note-items] .task-related-detail {
        padding: .75rem .875rem .875rem 2.15rem;
        line-height: 1.65;
        background: color-mix(in srgb, var(--app-page-bg) 60%, var(--app-surface));
    }
    [data-step-note-items] .task-related-text { margin: 0; }
    .task-step-field-row { display: grid; grid-template-columns: 1.75rem minmax(0, 1fr) auto; align-items: center; gap: .5rem; }
    .task-step-work-row { display: grid; grid-template-columns: 1.75rem auto minmax(0, 1fr) auto; align-items: start; gap: .5rem; }
    .task-step-number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        inline-size: 1.75rem;
        block-size: 1.75rem;
        color: #ffffff;
        background: var(--bs-primary);
        border: 0;
        border-radius: 50%;
        font-size: .8rem;
        font-weight: 700;
        font-variant-numeric: tabular-nums;
        line-height: 1;
    }
    .task-step-actions { display: flex; align-items: center; gap: .25rem; }
    .task-step-action.btn-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        inline-size: 2rem;
        block-size: 2rem;
        padding: 0;
        border: 0;
        background: none;
        box-shadow: none;
        font-size: 1.125rem;
        text-decoration: none;
    }
    .task-step-action.btn-link:hover { color: var(--app-surface-inverse); }
    .task-step-action.btn-link:disabled { opacity: .35; }
    .task-step-action.btn-link:focus:not(:focus-visible) { outline: none; }
    .task-step-action.btn-link:focus-visible { outline: 2px solid currentColor; outline-offset: 2px; }
    [data-task-step-row] > [data-work-field-error] {
        margin-inline-start: 2.25rem;
        padding-inline-start: .375rem;
        padding-block-start: .125rem !important;
        block-size: calc(1.4em + .125rem);
        line-height: 1.4;
        overflow: auto;
    }
    @media (max-width: 575.98px) {
        [data-task-work-steps] > [data-work-step],
        [data-task-steps][data-standalone] [data-task-step-row] { padding: .5rem; }
        .task-step-field-row { grid-template-columns: 1.75rem minmax(0, 1fr); }
        .task-step-field-row .task-step-actions { grid-column: 2; justify-content: flex-end; }
        [data-task-step-row] > [data-work-field-error] { block-size: calc(2.8em + .125rem); }
        [data-task-step-row] > [data-step-note-items] { margin-inline-start: .75rem; }
        [data-step-note-items] { margin-inline-start: .75rem; }
        [data-step-note-items] .task-related-detail { padding-inline: .75rem; }
    }
</style>
