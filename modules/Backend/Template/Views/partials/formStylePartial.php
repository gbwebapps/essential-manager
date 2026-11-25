<style>
    /* Il tema sovrascrive il fondo Bootstrap anche sui controlli disabled. */
    .form-control:disabled,
    .form-select:disabled,
    .ts-wrapper.disabled .ts-control {
        background-color: var(--bs-secondary-bg) !important;
        color: var(--app-text-muted) !important;
        opacity: 1;
    }
</style>
