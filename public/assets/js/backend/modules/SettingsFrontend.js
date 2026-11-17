import { apiFetch, showAlert, askConfirm, smoothReplace, handleValidationErrors, initTomSelects, markUnsavedClean, confirmDiscardChanges } from '../backend.js';

/** Gestisce i pannelli dei Settings Frontend e le operazioni sul loro form. */
export class SettingsFrontendManager {

    constructor(root) {
        this.root = root;
        this.panel = root.querySelector('#main_collapse_general');
        this.container = root.querySelector('#general-settings-container');
        this.busy = false;
    }

    /** Collega i controlli anche ai frammenti inseriti dopo il caricamento. */
    init() {
        this.root.addEventListener('click', event => {
            const panelButton = event.target.closest('[data-settings-panel]');
            const refreshButton = event.target.closest('.btn-refresh-general');
            const deleteButton = event.target.closest('.btn-delete-general');

            if (panelButton) {
                this.run(() => this.togglePanel(panelButton));
            } else if (refreshButton) {
                this.run(async () => {
                    if (await askConfirm(refreshButton.dataset.message)) await this.loadPanel();
                });
            } else if (deleteButton) {
                this.run(() => this.deleteSettings(deleteButton));
            }
        });

        this.root.addEventListener('submit', event => {
            if (!event.target.matches('#general-settings')) return;

            event.preventDefault();
            this.run(() => this.save(event.target));
        });

        ['show', 'hide'].forEach(action => {
            this.root.addEventListener(action + '.bs.collapse', event => {
                const button = event.target.parentElement.querySelector(':scope > .accordion-header > [data-settings-panel]');
                if (!button || button.getAttribute('aria-controls') !== event.target.id) return;

                button.classList.toggle('collapsed', action === 'hide');
                button.setAttribute('aria-expanded', String(action === 'show'));
            });
        });

        this.panel.addEventListener('hidden.bs.collapse', event => {
            if (event.target === this.panel) this.clearContainer();
        });
    }

    /** Evita richieste e conferme concorrenti durante il cambio di pannello. */
    async run(operation) {
        if (this.busy || this.root.querySelector('.collapsing')) return;
        this.busy = true;
        try {
            await operation();
        } catch (error) {
            console.error('Operazione Settings Frontend non completata:', error);
        } finally {
            this.busy = false;
        }
    }

    /** Carica Generali all'apertura e conferma l'eventuale abbandono del pannello attivo. */
    async togglePanel(button) {
        const panel = document.getElementById(button.getAttribute('aria-controls'));
        const active = panel.closest('.accordion').querySelector(':scope > .accordion-item > .accordion-collapse.show');

        if (active && !(await this.confirmClose(active))) return;
        const collapse = bootstrap.Collapse.getOrCreateInstance(panel, {toggle: false});
        if (active === panel) {
            collapse.hide();
            return;
        }

        if (panel === this.panel && !(await this.loadPanel())) return;
        collapse.show();
    }

    /** Scarta solo i campi del pannello lingua, oppure tutto il form se si chiude Generali. */
    async confirmClose(panel) {
        if (panel === this.panel) return confirmDiscardChanges(panel);

        const changes = [...panel.querySelectorAll('input, textarea')].filter(field => field.value !== field.defaultValue);
        if (changes.length === 0) return true;
        if (!(await confirmDiscardChanges(panel.closest('form')))) return false;

        changes.forEach(field => { field.value = field.defaultValue; });
        panel.querySelectorAll('[class^="error_"]').forEach(error => { error.textContent = '\u00A0'; });
        return true;
    }

    /** Carica il frammento di Generali con i valori correnti dal server. */
    async loadPanel() {
        const response = await apiFetch(this.root.dataset.openUrl, {method: 'POST', body: new FormData()});
        const data = await response.json();
        if (data.result !== true || !data.output) {
            if (data.message) showAlert('danger', data.message);
            return false;
        }

        this.replaceForm(data.output);
        return true;
    }

    /** Salva i dati con la stessa verifica password e la stessa gestione errori del Backend. */
    async save(form) {
        form.querySelectorAll('[class^="error_"]').forEach(error => { error.textContent = '\u00A0'; });
        const response = await apiFetch(this.root.dataset.saveUrl, {method: 'POST', body: new FormData(form)});
        const data = await response.json();
        if (data.cancelled) return;

        if (data.errors) {
            const errors = Object.fromEntries(Object.entries(data.errors).map(([key, value]) => [key.replaceAll('.', '_'), value]));
            handleValidationErrors(errors);
            const firstTranslation = Object.keys(data.errors).find(key => key.includes('.'));
            if (firstTranslation) {
                const panel = document.getElementById('frontend-copy-' + firstTranslation.split('.')[1]);
                if (panel) bootstrap.Collapse.getOrCreateInstance(panel, {toggle: false}).show();
            }
        }
        if (data.result === true) {
            markUnsavedClean(form);
            if (data.output) this.replaceForm(data.output);
        }
        if (data.message) showAlert(data.result === true ? 'success' : 'danger', data.message);
    }

    /** Verifica se ci sono impostazioni salvate prima di chiedere conferma della cancellazione. */
    async deleteSettings(button) {
        const body = new FormData();
        const check = await apiFetch(this.root.dataset.checkDeleteUrl, {method: 'POST', body});
        const result = await check.json();
        if (result.result !== true) {
            if (result.message) showAlert('info', result.message);
            return;
        }
        if (!(await askConfirm(button.dataset.message, {password: true, formData: body}))) return;

        const response = await apiFetch(this.root.dataset.deleteUrl, {method: 'POST', body});
        const data = await response.json();
        if (data.cancelled) return;
        if (data.message) showAlert(data.result === true ? 'success' : 'danger', data.message);
        if (data.result === true) {
            markUnsavedClean(this.container);
            await this.loadPanel();
        }
    }

    /** Libera i widget prima di svuotare il pannello o sostituirne il contenuto. */
    clearContainer() {
        this.container.querySelectorAll('select.tomselected').forEach(select => select.tomselect.destroy());
        this.container.querySelectorAll('.accordion-collapse').forEach(panel => bootstrap.Collapse.getInstance(panel)?.dispose());
        this.container.replaceChildren();
    }

    /** Inizializza Tom Select dopo il caricamento e ripristina il pannello lingua già visibile. */
    replaceForm(html) {
        const open = this.container.querySelector('.accordion-collapse.show')?.id;
        this.clearContainer();
        smoothReplace(this.container, html);
        initTomSelects(this.container);
        markUnsavedClean(this.container);

        const panel = open ? document.getElementById(open) : null;
        if (panel) bootstrap.Collapse.getOrCreateInstance(panel, {toggle: false}).show();
    }
}
