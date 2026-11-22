<style>
    .manager-work-modal .modal-dialog { max-width: min(56rem, calc(100% - 2rem)) !important; }
    .manager-work-modal .modal-content { color: var(--app-text); background: var(--app-surface); border: 1px solid var(--app-border); border-radius: .75rem; }
    .manager-work-modal .modal-content { max-block-size: calc(100dvh - 2rem); overflow: hidden; }
    .manager-work-modal .modal-content > form {
        display: flex;
        flex-direction: column;
        min-block-size: 0;
        max-block-size: inherit;
    }
    .manager-work-modal .modal-body { min-block-size: 0; overflow-y: auto; scrollbar-gutter: stable; }
    .manager-work-modal .modal-footer { flex-wrap: nowrap; }
    body:has(.manager-work-modal.show) .scrollup { display: none; }
    .manager-work-modal [data-work-field-error] {
        box-sizing: border-box;
        block-size: calc(1.55em + .25rem);
        padding-block-start: .25rem;
        line-height: 1.55;
        overflow: auto;
        overflow-wrap: anywhere;
        scrollbar-width: thin;
        overscroll-behavior: contain;
    }
    .manager-work-modal [data-work-form-errors] {
        block-size: 3.1em;
        line-height: 1.55;
        overflow: auto;
        scrollbar-width: thin;
        overscroll-behavior: contain;
    }
    .manager-work-modal .transfer-intro {
        block-size: 3.2em;
        line-height: 1.6;
        overflow: auto;
        scrollbar-width: thin;
        overscroll-behavior: contain;
    }
    @media (max-width: 575.98px) {
        .manager-work-modal [data-work-field-error] { block-size: calc(3.1em + .25rem); }
        .manager-work-modal .transfer-intro { block-size: 4.8em; }
    }
</style>
