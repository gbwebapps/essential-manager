import { renderDraftItems } from './DraftItems.js';
import { askConfirm, markUnsavedClean, setUnsavedExternalState, showAlert } from './backend.js';

/** Bozza privata della pagina: nessuna sessione, storage o scrittura sul database. */
export class ApplicationDraft {
    constructor({ replacePanel, resetModals }) {
        this.replacePanel = replacePanel;
        this.resetModals = resetModals;
        this.items = { task: [], note: [] };
        this.nextId = 0;
        this.generation = 0;
        this.renderQueue = Promise.resolve();
        this.labels = JSON.parse(this.root?.dataset.draftMessages || '{}');

        document.addEventListener('click', async event => {
            const button = event.target.closest('[data-draft-remove], [data-related-reload]');
            const panel = button?.closest('[data-application-draft-panel]');
            if ( ! panel || ! this.root?.contains(panel)) return;
            const kind = panel.dataset.applicationRelatedPanel;
            if ( ! Object.hasOwn(this.items, kind)) return;
            if (button.hasAttribute('data-draft-remove')) {
                if ( ! await askConfirm(this.text('removeConfirm'))) return;
                this.items[kind] = this.items[kind].filter(item => item.draftId !== button.dataset.draftRemove);
                this.updateDirty();
            }
            await this.render(kind);
        });

        // pagehide avviene solo dopo l'eventuale conferma di uscita.
        window.addEventListener('pagehide', () => this.reset(true));
        window.addEventListener('pageshow', event => {
            if (event.persisted) {
                this.reset(true);
                // Una pagina ripristinata dalla cache riparte con tutti i gestori di uscita inizializzati.
                window.location.reload();
            }
        });
    }

    get root() { return document.querySelector('[data-application-draft-root]'); }
    get form() { return document.querySelector('#applications-add'); }
    text(key) { return this.labels[key] || ''; }
    applicationName() { return this.form?.elements.namedItem('name')?.value.trim() || this.text('unnamedApplication'); }

    find(kind, id) {
        const item = this.items[kind]?.find(candidate => candidate.draftId === id);
        return item ? structuredClone(item) : null;
    }

    /** Confronta la bozza con dati già normalizzati dal server, senza coinvolgere record persistiti. */
    hasChanges(kind, values, id = '') {
        const original = this.find(kind, id);
        if ( ! original) return true;
        const fields = kind === 'task' ? ['title', 'description', 'status'] : ['content'];
        if (fields.some(field => original[field] !== values[field])) return true;
        return kind === 'task' && (JSON.stringify(original.notes) !== JSON.stringify(values.notes || []) || JSON.stringify(original.steps) !== JSON.stringify(values.steps.map(step => ({ title: step.title, uuid: '', notes: step.notes || [] }))));
    }

    upsert(kind, values, id = '', previewOutput) {
        if ( ! Object.hasOwn(this.items, kind)) throw new Error('Invalid draft kind');
        if (typeof previewOutput !== 'string' || !previewOutput.trim()) throw new Error('Missing validated draft preview');
        const index = this.items[kind].findIndex(item => item.draftId === id);
        const draftId = index >= 0 ? this.items[kind][index].draftId : `draft-${kind}-${++this.nextId}`;
        const item = kind === 'task' ? {
            draftId, title: values.title.trim(), description: values.description.trim(), status: values.status,
            notes: (values.notes || []).map(note => ({ content: note.content })),
            steps: values.steps.map(step => ({ title: step.title.trim(), uuid: '', notes: (step.notes || []).map(note => ({ content: note.content })) }))
        } : { draftId, content: values.content.trim() };
        item.previewOutput = previewOutput;
        if (index >= 0) this.items[kind][index] = item;
        else this.items[kind].push(item);
        this.updateDirty();
        this.render(kind);
        return draftId;
    }

    updateDirty() {
        setUnsavedExternalState(this.form, this.items.task.length > 0 || this.items.note.length > 0);
    }

    /** L'unico invio comprende applicazione, task, passi e note; gli ID locali non vengono inviati. */
    appendTo(formData) {
        this.items.task.forEach((task, index) => {
            for (const field of ['title', 'description', 'status']) formData.set(`tasks[${index}][${field}]`, task[field]);
            task.notes.forEach((note, noteIndex) => formData.set(`tasks[${index}][notes][${noteIndex}][content]`, note.content));
            task.steps.forEach((step, position) => {
                formData.set(`tasks[${index}][steps][${position}][title]`, step.title);
                step.notes.forEach((note, noteIndex) => formData.set(`tasks[${index}][steps][${position}][notes][${noteIndex}][content]`, note.content));
            });
        });
        this.items.note.forEach((note, index) => formData.set(`notes[${index}][content]`, note.content));
    }

    reset(resetApplicationForm = false) {
        this.generation++;
        this.items = { task: [], note: [] };
        for (const kind of ['task', 'note']) {
            const container = this.root?.querySelector(`[data-application-related-panel="${kind}"] [data-related-items]`);
            if ( ! container) continue;
            container.getAnimations().forEach(animation => animation.cancel());
            container.style.height = container.style.overflow = container.style.opacity = '';
            container.replaceChildren(this.buildItems(kind));
        }
        if (resetApplicationForm) this.form?.reset();
        this.resetModals();
        markUnsavedClean(this.form);
    }

    render(kind) {
        const generation = this.generation;
        this.renderQueue = this.renderQueue.catch(() => {}).then(async () => {
            if (generation !== this.generation) return;
            const container = this.root?.querySelector(`[data-application-related-panel="${kind}"] [data-related-items]`);
            if ( ! container) return;
            const holder = document.createElement('div');
            holder.append(this.buildItems(kind));
            await this.replacePanel(container, holder.innerHTML);
        }).catch(() => {
            if (generation === this.generation && this.root?.isConnected) showAlert('danger', this.text('reloadError'));
        });
        return this.renderQueue;
    }

    buildItems(kind) {
        return renderDraftItems(this.items[kind], this.text(kind === 'task' ? 'noTasks' : 'noNotes'));
    }

}
