<style>
    .manager-work-modal.task-modal-complete .modal-dialog { margin-block: 1rem !important; min-block-size: calc(100% - 2rem); }
    .manager-work-modal.task-modal-complete .modal-content { block-size: min(58rem, calc(100dvh - 2rem)); }
    .manager-work-modal.task-modal-complete .modal-content > form { block-size: 100%; }
    .manager-work-modal.task-modal-complete .modal-body { flex: 1 1 auto; }
    .task-modal-complete [data-task-step-list] { display: grid; gap: .75rem; }
    .task-modal-step { min-inline-size: 0; padding: .6rem; border: 1px solid var(--app-border); border-radius: .25rem; }
    .task-modal-number { display: inline-flex; align-items: center; justify-content: center; flex: 0 0 1.75rem; block-size: 1.75rem; background: var(--bs-primary); color: white; font-size: .85rem; font-weight: 700; border-radius: 50%; }
    .task-modal-step [data-step-title] { min-inline-size: 0; }
    .task-modal-step-actions .btn-link { color: var(--app-text); padding: .2rem .4rem; font-size: 1rem; box-shadow: none !important; }
    .task-modal-step-error { margin-inline-start: 2.25rem; padding-inline-start: .35rem; }
    .task-modal-note-group { margin-block-start: .4rem; }
    .task-modal-note-add.btn-link { display: inline-flex; align-items: center; justify-content: center; inline-size: 2rem; block-size: 2rem; padding: 0; border: 0; background: none; color: var(--app-text); font-size: 1.05rem; box-shadow: none !important; }
    .task-modal-note-add.btn-link:hover { color: var(--app-surface-inverse); }
    .task-modal-note-add.btn-link:focus:not(:focus-visible) { outline: none; }
    .task-modal-note-group > p { padding-block: .3rem; }
    .task-modal-note { margin-block-start: .5rem; padding: .45rem .6rem; border: 1px solid var(--app-border); border-radius: .25rem; }
    .task-modal-note summary { display: flex; align-items: center; justify-content: space-between; gap: .5rem; min-block-size: 1.8rem; cursor: pointer; overflow-wrap: anywhere; }
    .task-modal-note summary::-webkit-details-marker { display: none; }
    .task-modal-note-title { min-inline-size: 0; }
    .task-modal-note details[open] > summary .fa-chevron-right { transform: rotate(90deg); }
    .task-modal-note summary:hover { background: color-mix(in srgb, var(--app-text) 8%, transparent); }
    .task-modal-note-actions { display: flex; gap: .35rem; align-items: center; justify-content: flex-end; flex-wrap: wrap; flex-shrink: 0; max-inline-size: 100%; white-space: nowrap; }
    .task-modal-note .btn-link { color: var(--app-text); text-decoration: none; box-shadow: none !important; }
    .task-modal-note [data-work-field-error] { overflow-y: auto; }
    @media (max-width: 575.98px) {
        .task-modal-note summary { flex-wrap: wrap; }
        .task-modal-note-title { flex-basis: 100%; }
        .task-modal-note-actions { margin-inline-start: auto; }
        .task-modal-step-actions .btn-link { padding-inline: .3rem; }
    }
</style>
