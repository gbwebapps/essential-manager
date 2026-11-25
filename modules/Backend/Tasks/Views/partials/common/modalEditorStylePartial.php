<style>
    .manager-work-modal.task-modal-complete .modal-dialog { margin-block: 1rem !important; min-block-size: calc(100% - 2rem); }
    .manager-work-modal.task-modal-complete .modal-content { block-size: auto; }
    .manager-work-modal.task-modal-complete .modal-content > form { block-size: auto; }
    .manager-work-modal.task-modal-complete .modal-body { flex: 0 1 auto; }
    .manager-work-modal [data-modal-status-choice] .ts-dropdown { top: auto !important; bottom: 100%; margin: 0 0 .25rem; }
    .task-modal-note-group { margin: 0; }
    .task-modal-note { margin-block-start: .5rem; padding: 0; border: 1px solid color-mix(in srgb, var(--app-border) 30%, transparent); border-radius: .25rem; }
    .task-modal-note > .task-note-header { display: flex; align-items: center; justify-content: space-between; gap: .5rem; min-block-size: 1.8rem; padding: 0; cursor: pointer; overflow-wrap: anywhere; }
    .task-modal-note-title { border: 0; background: transparent; padding: .5rem .75rem; display: flex; align-items: center; min-inline-size: 0; flex: 1; }
    .task-modal-note > .task-note-header:hover { background: color-mix(in srgb, var(--app-surface-inverse) 10%, var(--app-surface-muted)); }
    .task-modal-note-actions { padding-inline-end: .5rem; display: flex; gap: .35rem; align-items: center; justify-content: flex-end; flex-wrap: wrap; flex-shrink: 0; max-inline-size: 100%; white-space: nowrap; }
    .task-modal-note .btn-link { color: var(--app-text); text-decoration: none; box-shadow: none !important; }
    .task-modal-note [data-work-field-error] { overflow-y: auto; }
    @media (max-width: 575.98px) {
        .task-modal-note > .task-note-header { flex-wrap: wrap; }
        .task-modal-note-title { flex-basis: 100%; }
        .task-modal-note-actions { margin-inline-start: auto; }
    }
    [data-checklist-picker] .ts-control { flex-wrap: nowrap; }
    [data-checklist-picker] .ts-control > .item { min-width: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .task-editor-field > legend { margin-bottom: .5rem; }
    .task-editor-field > [data-task-editor-actions], .task-editor-field [data-task-editor-actions] { margin-top: .25rem !important; }
    .task-editor-field .task-note-editor, .task-editor-field .task-editor-notes-panel { display: contents; }
    .task-editor-field [data-related-items] > p { display: none; }
</style>
