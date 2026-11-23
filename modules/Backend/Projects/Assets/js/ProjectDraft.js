import { askConfirm, markUnsavedClean, setUnsavedExternalState, showAlert } from './backend.js';

function draftElement(tag, className = '', text = null) {
    const element = document.createElement(tag);
    element.className = className;
    if (text !== null) element.textContent = String(text);
    return element;
}

/** Bozza privata della pagina: nessuna sessione, storage o scrittura sul database. */
export class ProjectDraft {
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
            const panel = button?.closest('[data-project-draft-panel]');
            if ( ! panel || ! this.root?.contains(panel)) return;
            const kind = panel.dataset.projectRelatedPanel;
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

    get root() { return document.querySelector('[data-project-draft-root]'); }
    get form() { return document.querySelector('#projects-add'); }
    text(key) { return this.labels[key] || ''; }
    projectName() { return this.form?.elements.namedItem('name')?.value.trim() || this.text('unnamedProject'); }

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
        return kind === 'task' && (original.steps.length !== values.steps.length || original.steps.some((step, index) => step.title !== values.steps[index].title));
    }

    upsert(kind, values, id = '') {
        if ( ! Object.hasOwn(this.items, kind)) throw new Error('Invalid draft kind');
        const index = this.items[kind].findIndex(item => item.draftId === id);
        const draftId = index >= 0 ? this.items[kind][index].draftId : `draft-${kind}-${++this.nextId}`;
        const item = kind === 'task' ? {
            draftId, title: values.title.trim(), description: values.description.trim(), status: values.status,
            steps: values.steps.map(step => ({ title: step.title.trim(), uuid: '' }))
        } : { draftId, content: values.content.trim() };
        if (index >= 0) this.items[kind][index] = item;
        else this.items[kind].push(item);
        this.updateDirty();
        this.render(kind);
        return draftId;
    }

    updateDirty() {
        setUnsavedExternalState(this.form, this.items.task.length > 0 || this.items.note.length > 0);
    }

    /** L'unico invio comprende progetto, task, passi e note; gli ID locali non vengono inviati. */
    appendTo(formData) {
        this.items.task.forEach((task, index) => {
            for (const field of ['title', 'description', 'status']) formData.set(`tasks[${index}][${field}]`, task[field]);
            task.steps.forEach((step, position) => formData.set(`tasks[${index}][steps][${position}][title]`, step.title));
        });
        this.items.note.forEach((note, index) => formData.set(`notes[${index}][content]`, note.content));
    }

    reset(resetProjectForm = false) {
        this.generation++;
        this.items = { task: [], note: [] };
        for (const kind of ['task', 'note']) {
            const container = this.root?.querySelector(`[data-project-related-panel="${kind}"] [data-related-items]`);
            if ( ! container) continue;
            container.getAnimations().forEach(animation => animation.cancel());
            container.style.height = container.style.overflow = container.style.opacity = '';
            container.replaceChildren(this.buildItems(kind));
        }
        if (resetProjectForm) this.form?.reset();
        this.resetModals();
        markUnsavedClean(this.form);
    }

    render(kind) {
        const generation = this.generation;
        this.renderQueue = this.renderQueue.catch(() => {}).then(async () => {
            if (generation !== this.generation) return;
            const container = this.root?.querySelector(`[data-project-related-panel="${kind}"] [data-related-items]`);
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
        const fragment = document.createDocumentFragment();
        if (this.items[kind].length === 0) {
            fragment.append(draftElement('p', 'small app-text-muted px-3 py-2 mb-0', this.text(kind === 'task' ? 'noTasks' : 'noNotes')));
            return fragment;
        }
        for (const item of this.items[kind]) {
            const article = draftElement('article', 'project-related-item');
            article.dataset.draftItem = item.draftId;
            const collapseId = 'project-' + item.draftId;
            const toggle = draftElement('button', 'project-related-toggle');
            toggle.type = 'button';
            toggle.id = collapseId + '-toggle';
            toggle.dataset.bsToggle = 'collapse';
            toggle.dataset.bsTarget = '#' + collapseId;
            toggle.setAttribute('aria-controls', collapseId);
            toggle.setAttribute('aria-expanded', 'false');
            const chevron = draftElement('i', 'fa-solid fa-chevron-right');
            chevron.setAttribute('aria-hidden', 'true');
            const summary = draftElement('span', 'project-related-summary');
            const summaryText = (kind === 'task' ? item.title : item.content).replace(/\s+/gu, ' ').trim();
            const summaryCharacters = Array.from(summaryText);
            const preview = summaryCharacters.length > 50 ? summaryCharacters.slice(0, 49).join('') + '…' : summaryText;
            summary.append(draftElement('span', 'project-related-title fw-bold text-truncate', preview));
            if (kind === 'task') {
                const metadata = draftElement('span', 'project-related-meta app-text-muted');
                metadata.append(draftElement('span', 'badge app-bg-muted-subtle app-text-muted-emphasis border', this.text('status_' + item.status)));
                metadata.append(draftElement('span', '', this.text('stepsProgress').replace('%s', '0').replace('%s', String(item.steps.length))));
                summary.append(metadata);
            }
            toggle.append(chevron, summary);
            const collapse = draftElement('div', 'collapse');
            collapse.id = collapseId;
            collapse.setAttribute('role', 'region');
            collapse.setAttribute('aria-labelledby', toggle.id);
            const detail = draftElement('div', 'project-related-detail');
            if (kind === 'task' && summaryCharacters.length > 50) detail.append(draftElement('p', 'project-related-text fw-bold mb-2', item.title));
            if (kind === 'note' || item.description) detail.append(draftElement('p', 'project-related-text', kind === 'note' ? item.content : item.description));
            if (kind === 'task') {
                const steps = draftElement('ol', 'ps-3 mb-2');
                item.steps.forEach(step => steps.append(draftElement('li', 'py-1', step.title)));
                detail.append(steps);
            }
            const actions = draftElement('div', 'd-flex flex-wrap gap-1');
            const edit = draftElement('button', 'btn btn-sm app-btn-secondary', this.text('edit'));
            edit.type = 'button';
            edit.dataset.bsToggle = 'modal';
            edit.dataset.bsTarget = `#projects-add-${kind}-modal`;
            edit.dataset.draftId = item.draftId;
            const remove = draftElement('button', 'btn btn-sm app-btn-secondary', this.text('remove'));
            remove.type = 'button';
            remove.dataset.draftRemove = item.draftId;
            actions.append(edit, remove);
            detail.append(actions);
            collapse.append(detail);
            article.append(toggle, collapse);
            fragment.append(article);
        }
        return fragment;
    }
}
