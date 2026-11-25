import './tasks.js';
import { ListLightManager } from './modules/Crud.js';
import { urlbase, apiFetch, askConfirm, showAlert, smoothReplace } from './backend.js';
import { updateNavigationCounts, showModalMessages } from './ManagerFeedback.js';

const root = document.getElementById('studio-workspace');
const endpoint = name => urlbase + 'backend/studio/' + name;
const lists = new Map();

/** @param {Object} data Risposta server. @returns {void} Aggiorna solo i conteggi autorizzati. */
function counts(data) {
    updateNavigationCounts(data);
    if (Number.isSafeInteger(data.navigationCounts?.studio)) document.querySelectorAll('[data-studio-task-count]').forEach(n => { n.textContent = String(Number.isSafeInteger(data.studioList?.taskCount) ? data.studioList.taskCount : data.navigationCounts.studio); });
    if (Number.isSafeInteger(data.noteCount)) document.querySelectorAll('[data-studio-note-count]').forEach(n => { n.textContent = String(data.noteCount); });
}

/** @param {Object} data Risposta della lettura o della mutazione. @param {Object} context Token locale. @returns {Promise<void>} Usa i partial già ricevuti, senza altre richieste nel flusso ordinario. */
async function applyStudio(data, context) {
    const list = lists.get(context?.key || data.studioList?.kind);
    if (!list || !data.studioList) { counts(data); return; }
    const applied = await list.apply(data.studioList, context);
    if (!applied) {
        // Una ricerca inviata durante un salvataggio deve rileggere i dati dopo il commit.
        if (context.mutation) await list.load();
        return;
    }
    counts(data);
    if (typeof data.studioOverview === 'string') {
        const container = document.getElementById('studio-overview-container');
        if (container && container.innerHTML.trim() !== data.studioOverview.trim()) smoothReplace(container, data.studioOverview);
    }
}

root?.querySelectorAll('[data-studio-list]').forEach(section => {
    const kind = section.dataset.studioList;
    const list = new ListLightManager({
        root: section, key: kind, url: endpoint(kind === 'tasks' ? 'getTasks' : 'getNotes'),
        storageKey: root.dataset.archiveList === 'archived' ? `ListLightManager:${location.pathname}:${kind}` : '',
        requestField: 'studioList', errorMessage: root.dataset.saveError,
        selectors: {
            results: '[data-studio-results]', search: '[data-studio-search]',
            clear: '.reset-search-field', form: '[data-studio-search-form]',
            refresh: '[data-studio-refresh]', sort: '[data-studio-sort]',
            pagination: '[data-studio-pagination] a[data-page]', error: '[data-studio-search-error]'
        },
        sanitizeSearch: value => value.replace(/[<>"%'`\\\u0000-\u001f\u007f]/g, ''),
        getState: (state, instance) => ({
            ...state, archive: kind === 'tasks' ? root.dataset.archiveList : 'active',
            opened: kind === 'tasks' ? [...instance.results.querySelectorAll('[data-studio-task]')]
                .filter(panel => panel.querySelector('[data-studio-toggle]')?.getAttribute('aria-expanded') === 'true')
                .map(panel => panel.dataset.studioTask) : []
        })
    }, {
        onResponse: applyStudio,
        onStateChange: (state, instance) => {
            const sort = instance.sort;
            const title = sort.dataset[state.direction === 'desc' ? 'oldest' : 'newest'];
            sort.title = title; sort.setAttribute('aria-label', title);
            sort.querySelector('i').className = 'fa-solid ' + (state.direction === 'desc' ? 'fa-arrow-down-wide-short' : 'fa-arrow-up-wide-short');
        }
    });
    lists.set(kind, list);
    if (section.classList.contains('active')) list.load();
});

/** Le azioni condivise ricevono lo stato della lista e restituiscono direttamente i suoi partial. */
document.addEventListener('essential:task-request', event => {
    if (!root || !lists.has('tasks')) return;
    event.detail.context = lists.get('tasks').attach(event.detail.body);
});
document.addEventListener('essential:task-response', event => {
    if (!root || !event.detail.data.studioList) return;
    event.detail.handled = true;
    event.detail.pending.push(applyStudio(event.detail.data, event.detail.context));
});

document.addEventListener('shown.bs.tab', event => {
    if (!root || !event.target.matches('.studio-tab')) return;
    const kind = event.target.id === 'studio-tasks-tab' ? 'tasks' : 'notes';
    history.replaceState(history.state, '', urlbase + 'backend/studio' + (kind === 'notes' ? '/notes' : (root.dataset.archiveList === 'archived' ? '/archived' : '')));
    if (!lists.get(kind)?.loaded) lists.get(kind)?.load();
});

/** @param {HTMLElement} previous Pannello chiuso. @param {HTMLElement} next Pannello caricato. @returns {Promise<void>} Apertura morbida del contenuto richiesto esplicitamente. */
async function replaceTask(previous, next) {
    const height = previous.getBoundingClientRect().height;
    previous.replaceWith(next);
    if (matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    const animation = next.animate([{ height: `${height}px` }, { height: `${next.getBoundingClientRect().height}px` }], { duration: 220, easing: 'ease-in-out' });
    try { await animation.finished; } finally { animation.cancel(); }
}

document.addEventListener('click', async event => {
    const noteLink = event.target.closest('a[href$="#add-note"]');
    if (noteLink && root) {
        event.preventDefault(); bootstrap.Modal.getOrCreateInstance(document.getElementById('studio-note-modal')).show(noteLink); return;
    }
    const noteHeading = event.target.closest('[data-studio-note-heading]');
    if (noteHeading && !event.target.closest('.studio-panel-actions')) {
        const button = noteHeading.querySelector('[data-studio-note-toggle]');
        const content = document.getElementById(button.getAttribute('aria-controls'));
        if (content.classList.contains('collapsing')) return;
        const opened = content.classList.contains('show'); button.setAttribute('aria-expanded', String(!opened));
        bootstrap.Collapse.getOrCreateInstance(content, { toggle: false })[opened ? 'hide' : 'show'](); return;
    }
    const toggle = event.target.closest('[data-studio-toggle]');
    if (!toggle) return;
    const panel = toggle.closest('[data-studio-task]');
    const content = panel.querySelector('[data-studio-task-content]');
    if (panel.hasAttribute('aria-busy') || content.classList.contains('collapsing')) return;
    if (content.dataset.loaded === 'true') {
        const opened = content.classList.contains('show'); toggle.setAttribute('aria-expanded', String(!opened));
        bootstrap.Collapse.getOrCreateInstance(content, { toggle: false })[opened ? 'hide' : 'show'](); return;
    }
    panel.setAttribute('aria-busy', 'true');
    const body = new FormData(); body.set('uuid', panel.dataset.studioTask);
    try {
        const response = await apiFetch(endpoint('getTask'), { method: 'POST', body });
        const data = await response.json();
        if (data.result !== true) { showAlert('danger', data.message); return; }
        if (!panel.isConnected) return;
        const template = document.createElement('template'); template.innerHTML = data.output;
        await replaceTask(panel, template.content.firstElementChild);
    } catch (_) { showAlert('danger', root.dataset.saveError); }
    finally { panel.removeAttribute('aria-busy'); }
});

/** Modale delle note autonome: reset e feedback condivisi; nessun campo Studio ridondante. */
document.querySelectorAll('[data-studio-modal]').forEach(modal => {
    const form = modal.querySelector('form'); const feedback = modal.querySelector('[data-studio-feedback]');
    let record = null;
    const fill = value => {
        form.reset(); form.elements.namedItem('uuid').value = value?.uuid || ''; form.elements.namedItem('content').value = value?.content || '';
        modal.querySelector('.modal-title').textContent = value ? modal.dataset.editTitle : modal.dataset.addTitle;
    };
    const reset = () => { if (form.dataset.saving !== 'true') { fill(record); showModalMessages(form, feedback, modal.dataset.saveNotice); } };
    modal.querySelector('[data-modal-reset]').addEventListener('click', reset);
    modal.addEventListener('show.bs.modal', event => {
        if (form.dataset.saving === 'true') { event.preventDefault(); return; }
        document.querySelectorAll('#alert-container .app-alert-success').forEach(alert => bootstrap.Alert.getOrCreateInstance(alert).close());
        record = event.relatedTarget?.dataset.studioNoteRecord ? JSON.parse(event.relatedTarget.dataset.studioNoteRecord) : null; reset();
    });
    modal.addEventListener('hidden.bs.modal', () => { record = null; reset(); });
    form.addEventListener('submit', async event => {
        event.preventDefault(); if (form.dataset.saving === 'true') return;
        const body = new FormData(form); const context = lists.get('notes')?.attach(body);
        const controls = [...form.querySelectorAll('input,textarea,button')].map(node => [node, node.disabled]);
        controls.forEach(([node]) => { node.disabled = true; }); form.dataset.saving = 'true';
        try {
            const response = await apiFetch(endpoint('saveNote'), { method: 'POST', body }); const data = await response.json();
            if (data.result !== true) { showModalMessages(form, feedback, data.message || root.dataset.saveError, data.changed === false ? 'neutral' : 'error', data.errors || {}); return; }
            record = structuredClone(data.record); fill(record); showModalMessages(form, feedback, data.message, 'success');
            await applyStudio(data, context);
        } catch (_) { showModalMessages(form, feedback, root.dataset.saveError, 'error'); }
        finally { controls.forEach(([node, disabled]) => { node.disabled = disabled; }); delete form.dataset.saving; }
    });
});

/** Eliminazioni definitive tramite form, conferma e parametri nascosti validati dal server. */
document.addEventListener('submit', async event => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement) || !form.matches('[data-studio-delete-task],[data-studio-delete-note]')) return;
    event.preventDefault(); if (form.dataset.busy === 'true') return;
    const kind = form.matches('[data-studio-delete-task]') ? 'tasks' : 'notes';
    const body = new FormData(form);
    form.dataset.busy = 'true'; const controls = [...form.querySelectorAll('button')].map(node => [node, node.disabled]);
    controls.forEach(([node]) => { node.disabled = true; });
    try {
        if (!await askConfirm(form.dataset.confirm)) return;
        const context = lists.get(kind).attach(body);
        const response = await apiFetch(endpoint(kind === 'tasks' ? 'deleteTask' : 'deleteNote'), { method: 'POST', body }); const data = await response.json();
        if (data.result !== true) { showAlert('danger', data.message); return; }
        await applyStudio(data, context); if (data.message) showAlert('success', data.message);
    } catch (_) { showAlert('danger', root.dataset.saveError); }
    finally { controls.forEach(([node, disabled]) => { node.disabled = disabled; }); delete form.dataset.busy; }
});

if (root && location.hash === '#add-note') {
    bootstrap.Modal.getOrCreateInstance(document.getElementById('studio-note-modal')).show();
    history.replaceState(null, '', location.pathname + location.search);
}
