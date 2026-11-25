import { disposeNoteCollapses } from './NoteCollapse.js';
import { setControlValue } from '../components/TomSelect.js';
import { initTaskSteps, fillTaskSteps } from './TaskSteps.js';
import { apiFetch, setUnsavedExternalState } from './backend.js';

const normalized = value => String(value || '').replace(/\r\n?/g, '\n').trim();
const excerpt = value => { const chars = Array.from(String(value || '').replace(/\s+/g, ' ').trim()); return chars.length > 50 ? chars.slice(0, 49).join('') + '…' : chars.join(''); };

/**
 * Bozze locali delle note del task e dei passi: nessuna scrittura prima del submit.
 * Lo stesso editor serve modali, Tasks e Studio; le righe conservano la loro identità nel riordino.
 */
export class TaskModalEditor {
    /** @param {HTMLFormElement} form Form del task, dedicato o nel modale del proprietario. */
    constructor(form) {
        this.form = form;
        this.steps = form.querySelector('[data-task-steps]');
        this.root = form.querySelector('[data-task-modal-notes]');
        this.groups = new Map();
        this.request = null;
        this.generation = 0;
        this.ready = true;
        this.sequence = 0;
        this.events = new AbortController();
        const signal = this.events.signal;
        initTaskSteps(form);
        form.addEventListener('change', event => {
            if (event.target.matches('[data-modal-status-select]')) form.elements.namedItem('status').value = event.target.value;
        }, { signal });
        this.steps?.addEventListener('tasksteps:change', () => this.syncSteps(), { signal });
        this.form.addEventListener('input', event => {
            if (event.target.closest('[data-modal-note-row]')) this.updateDraftState();
        }, { signal });
        this.form.addEventListener('click', event => {
            if (!this.root || this.form.dataset.saving === 'true' || !this.ready) return;
            const add = event.target.closest('[data-modal-note-add]');
            if (add) {
                const node = add.closest('[data-modal-note-group]') || add.closest('[data-task-step-row]')?.querySelector('[data-modal-note-group]') || this.root.querySelector('[data-modal-note-group]');
                const group = this.groups.get(node);
                if (group) this.addNote(group, {}, true);
            }
            const remove = event.target.closest('[data-modal-note-remove]');
            if (remove) {
                event.preventDefault();
                const row = remove.closest('[data-modal-note-row]');
                const group = this.groups.get(row.closest('[data-modal-note-group]'));
                const note = group?.items.find(item => item.row === row);
                if (note) {
                    if (note.uuid) group.deleted.push(note);
                    group.items = group.items.filter(item => item !== note);
                    disposeNoteCollapses(row); row.remove(); this.updateDraftState();
                }
            }
        }, { signal });
        if (this.root) this.mount(this.root.querySelector('[data-modal-note-group]'), []);
        this.syncSteps();
        if (this.steps?.dataset.standalone === 'true') {
            this.errorsObserver = new MutationObserver(changes => {
                if (changes.some(change => (change.target.nodeType === Node.ELEMENT_NODE ? change.target : change.target.parentElement)?.closest('[data-work-field-error]'))) this.showErrors();
            });
            this.errorsObserver.observe(this.form, { childList: true, characterData: true, subtree: true });
        }
    }

    /** Libera listener e osservatori quando la vista del form viene ricaricata. */
    destroy() {
        this.cancel();
        disposeNoteCollapses(this.form);
        this.events.abort();
        this.errorsObserver?.disconnect();
    }

    /** Ricarica campi e note dai dati iniziali o dall'ultimo salvataggio riuscito. */
    reset(record) {
        this.resetStatus(record);
        disposeNoteCollapses(this.form);
        this.groups.clear();
        fillTaskSteps(this.steps, record?.steps);
        if (!this.root) return;
        this.mount(this.root.querySelector('[data-modal-note-group]'), record?.notes || []);
        [...this.steps.querySelector('[data-task-step-list]').children].forEach((row, index) => {
            this.mount(row.querySelector('[data-modal-note-group]'), record?.steps?.[index]?.notes || []);
        });
    }

    /**
     * Mostra lo stato come in Tasks Add/Edit, mantenendo un solo valore nel payload.
     * @param {object|null} record Ultimo task salvato oppure nuova bozza.
     * @returns {void}
     */
    resetStatus(record) {
        const select = this.form.querySelector('[data-modal-status-select]');
        if (!select) return;
        const status = record?.status || 'todo';
        this.form.elements.namedItem('status').value = status;
        setControlValue(select, status);
        const revision = this.statusRevision = (this.statusRevision || 0) + 1;
        // Il reset nativo viene applicato da TomSelect nel ciclo successivo.
        setTimeout(() => {
            if (this.statusRevision === revision && select.isConnected) setControlValue(select, status);
        }, 0);
        const editing = Boolean(record?.uuid);
        this.form.querySelector('[data-modal-status-choice]').hidden = editing;
        this.form.querySelector('[data-modal-status-display]').hidden = !editing;
        this.form.querySelectorAll('[data-modal-status-value]').forEach(node => { node.hidden = node.dataset.modalStatusValue !== status; });
    }

    /** Aggancia solo i nuovi passi e scarta le bozze dei passi rimossi. */
    syncSteps() {
        if (!this.root) return;
        for (const [node] of this.groups) if (!this.form.contains(node)) { disposeNoteCollapses(node); this.groups.delete(node); }
        for (const row of this.steps.querySelector('[data-task-step-list]').children) {
            const node = row.querySelector('[data-modal-note-group]');
            if (node && !this.groups.has(node)) this.mount(node, []);
        }
        this.updateDraftState();
    }

    /** @param {HTMLElement|null} node Gruppo nel form. @param {object[]} notes Note persistite. @returns {void} */
    mount(node, notes) {
        if (!node) return;
        disposeNoteCollapses(node);
        node.querySelector('[data-modal-note-list]').replaceChildren();
        const group = { node, items: [], deleted: [] };
        this.groups.set(node, group);
        notes.forEach(note => this.addNote(group, note));
        this.updateDraftState();
    }

    /** @param {object} group Gruppo del task o passo. @param {object} record Nota iniziale. @param {boolean} opened Apre la nuova bozza. @returns {void} */
    addNote(group, record, opened = false) {
        const row = this.root.querySelector('[data-modal-note-template]').content.firstElementChild.cloneNode(true);
        const input = row.querySelector('textarea');
        const title = row.querySelector('[data-modal-note-preview]');
        const panel = row.querySelector('[data-modal-note-panel]');
        const note = { row, input, uuid: record.uuid || '', version: record.version || '', original: normalized(record.content) };
        input.value = record.content || '';
        input.id = `${this.steps.dataset.fieldPrefix}-note-${++this.sequence}`;
        row.querySelector('label').htmlFor = input.id;
        input.disabled = Boolean(note.uuid) && this.root.dataset.canEdit !== 'true';
        const remove = row.querySelector('[data-modal-note-remove]');
        remove.hidden = Boolean(note.uuid) && this.root.dataset.canDelete !== 'true';
        const preview = () => { title.textContent = excerpt(input.value) || this.root.dataset.newNote; };
        input.addEventListener('input', preview); preview();
        panel.id = `${input.id}-panel`;
        panel.setAttribute('role', 'region');
        panel.setAttribute('aria-labelledby', `${input.id}-toggle`);
        row.querySelector('[data-modal-note-toggle]').id = `${input.id}-toggle`;
        row.querySelectorAll('[data-modal-note-toggle], [data-modal-note-read]').forEach(toggle => {
            toggle.dataset.bsToggle = 'collapse'; toggle.dataset.bsTarget = `#${panel.id}`;
            toggle.setAttribute('aria-controls', panel.id); toggle.setAttribute('aria-expanded', String(opened));
            toggle.classList.toggle('collapsed', !opened);
        });
        panel.classList.toggle('show', opened);
        group.node.querySelector('[data-modal-note-list]').append(row);
        group.items.push(note);
        this.updateDraftState();
        if (opened) {
            input.focus({ preventScroll: true });
            const body = this.form.querySelector('.modal-body');
            if (!body) { input.scrollIntoView({ block: 'nearest', behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth' }); return; }
            const area = body.getBoundingClientRect();
            const field = input.getBoundingClientRect();
            if (field.bottom > area.bottom || field.top < area.top) {
                const offset = field.bottom > area.bottom ? field.bottom - area.bottom + 12 : field.top - area.top - 12;
                body.scrollBy({ top: offset, behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth' });
            }
        }
    }

    /** Registra nel controllo globale anche le note locali, prive di campi POST propri. */
    updateDraftState() {
        if (this.steps?.dataset.standalone !== 'true') return;
        const changed = [...this.groups.values()].some(group => group.deleted.length > 0 || group.items.some(note => !note.uuid || normalized(note.input.value) !== note.original));
        setUnsavedExternalState(this.form, changed);
    }

    /** Aggiunge al form soltanto inserimenti, modifiche ed eliminazioni espliciti. */
    appendTo(body) {
        if (!this.root) return;
        this.syncSteps();
        for (const [node, group] of this.groups) {
            const step = node.closest('[data-task-step-row]');
            const index = step ? [...this.steps.querySelector('[data-task-step-list]').children].indexOf(step) : null;
            const prefix = index === null ? 'notes' : `steps[${index}][notes]`;
            const path = index === null ? 'notes' : `steps.${index}.notes`;
            let operation = 0;
            for (const note of group.items) {
                const error = note.row.querySelector('[data-work-field-error]');
                error.textContent = ''; error.dataset.workFieldError = '';
                [...error.classList].filter(name => name.startsWith('error_')).forEach(name => error.classList.remove(name));
                const content = normalized(note.input.value);
                if (note.uuid && content === note.original) continue;
                error.dataset.workFieldError = `${path}.${operation}.content`;
                if (this.steps.dataset.standalone === 'true') error.classList.add(`error_${path.replaceAll('.', '_')}_${operation}_content`);
                body.set(`${prefix}[${operation}][content]`, content);
                if (note.uuid) {
                    body.set(`${prefix}[${operation}][uuid]`, note.uuid);
                    body.set(`${prefix}[${operation}][version]`, note.version);
                }
                operation++;
            }
            for (const note of group.deleted) {
                body.set(`${prefix}[${operation}][uuid]`, note.uuid);
                body.set(`${prefix}[${operation}][version]`, note.version);
                body.set(`${prefix}[${operation}][deleted]`, '1');
                operation++;
            }
        }
    }

    /** Rende visibili gli errori delle note senza cambiare le dimensioni del modale. */
    showErrors() {
        this.form.querySelectorAll('[data-modal-note-row]').forEach(row => {
            if (row.querySelector('[data-work-field-error]')?.textContent.trim()) bootstrap.Collapse.getOrCreateInstance(row.querySelector('[data-modal-note-panel]'), { toggle: false }).show();
        });
    }

    /** Carica le note esistenti evitando risposte tardive dopo chiusura o cambio task. */
    async load(url, body, onRecord, onError) {
        this.cancel();
        const generation = this.generation;
        const request = new AbortController(); this.request = request; this.ready = false;
        const controls = [...this.form.querySelectorAll('input,textarea,select,button')].filter(control => !control.hasAttribute('data-bs-dismiss')).map(control => [control, control.disabled]);
        controls.forEach(([control]) => { control.disabled = true; });
        this.restore = () => controls.forEach(([control, disabled]) => { if (control.isConnected) control.disabled = disabled; });
        try {
            const response = await apiFetch(url, { method: 'POST', body, signal: request.signal });
            const data = await response.json();
            if (generation !== this.generation) return;
            if (data.result !== true || !data.record) throw new Error('Task unavailable');
            this.restore(); this.restore = null;
            this.ready = true;
            onRecord(data.record);
        } catch (error) {
            if (generation === this.generation && !request.signal.aborted) onError();
        } finally {
            if (generation === this.generation) this.request = null;
        }
    }

    /** Interrompe il caricamento e restituisce i controlli al loro stato iniziale. */
    cancel() {
        this.generation++;
        this.request?.abort(); this.request = null;
        this.restore?.(); this.restore = null;
        this.ready = true;
    }
}
