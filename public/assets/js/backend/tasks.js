import { showModalMessages } from './ManagerFeedback.js';
import { initTaskSteps } from './TaskSteps.js';
import { urlbase, action, apiFetch, markUnsavedClean, showAlert, initOffcanvasAutoClose, initRangeDatePicker, confirmDiscardChanges, askConfirm } from './backend.js';
import { ListManager, AddManager, EditManager, DeleteManager, GeneralDataManager, MetaDataManager } from './modules/Crud.js';


// Il percorso è definito dalla vista del contesto, mai da un parametro del form.
const taskRouteBase = document.querySelector('[data-task-route-base]')?.dataset.taskRouteBase || 'backend/tasks/';
const taskEndpoint = name => urlbase + taskRouteBase + name;

/** Cancella testo e associazioni di lingua degli errori del solo form Tasks. */
function clearTaskError(node) {
    node.textContent = '\u00A0';
    node.removeAttribute('data-i18n-text');
    delete node.dataset.validationAlertToken;
}
function taskErrorNodes(scope = document) {
    return [...scope.querySelectorAll('#tasks-add [class], #tasks-edit [class]')].filter(node => [...node.classList].some(name => name.startsWith('error_')));
}
document.addEventListener('submit', event => {
    if (event.target.matches('#tasks-add, #tasks-edit')) taskErrorNodes().forEach(clearTaskError);
}, true);
// Conserva il legame con l'alert chiuso: non tocca errori di richieste più recenti.
const closingTaskErrors = new WeakMap();
document.addEventListener('close.bs.alert', event => {
    if (!event.target.closest('#alert-container')) return;
    closingTaskErrors.set(event.target, taskErrorNodes().map(node => [node, node.dataset.validationAlertToken]));
});
document.addEventListener('closed.bs.alert', event => {
    for (const [node, token] of closingTaskErrors.get(event.target) || []) {
        if (!node.dataset.validationAlertToken || node.dataset.validationAlertToken === token) clearTaskError(node);
    }
    closingTaskErrors.delete(event.target);
});

const actions = {
    showAll: function() {
        const listManager = new ListManager({
            controller: 'tasks',
            url: taskEndpoint('showAll'),
            rootId: 'tasks-list',
            containerId: 'showAll-tasks-container',
            searchFields: ['title', 'project_uuid', 'application_uuid', 'status'],
            searchDates: ['created_at', 'updated_at']
        }, {
            onShowAfter: async data => {
                if (data.navigationCounts) {
                    document.querySelectorAll('[data-navigation-count]').forEach(badge => {
                        const count = data.navigationCounts[badge.dataset.navigationCount];
                        if (Number.isSafeInteger(count)) badge.textContent = String(count);
                    });
                }
                if (typeof data.overviewOutput === 'string') await replaceRelatedPanel(document.getElementById('tasks-overview-container'), data.overviewOutput);
                const counts = data.trashCounts;
                const filters = ['active', 'trashed', 'all'];
                if (!counts || !filters.every(filter => Number.isSafeInteger(counts[filter]) && counts[filter] >= 0)) return;
                listManager.root.querySelectorAll('[data-trash-count]').forEach(badge => {
                    const filter = badge.dataset.trashCount;
                    if (!filters.includes(filter)) return;
                    badge.textContent = String(counts[filter]);
                    badge.hidden = false;
                });
            }
        });
        const origin = new URL(window.location.href).searchParams.get('project_uuid');
        if (origin && /^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i.test(origin)) {
            listManager.updateNestedState('searchFields', 'project_uuid', origin);
            listManager.updateState('page', 1);
        }
        listManager.init();

        new DeleteManager({
            controller: 'tasks',
            urls: {
                softDelete: taskEndpoint('softDelete'),
                restoreDelete: taskEndpoint('restoreDelete'),
                hardDelete: taskEndpoint('hardDelete')
            },
            listManager: listManager
        }).init();

        initRangeDatePicker('#wrapper-tasks-created_at-from', '#wrapper-tasks-created_at-to');
        initRangeDatePicker('#wrapper-tasks-updated_at-from', '#wrapper-tasks-updated_at-to');
        initOffcanvasAutoClose('tasks-actionsOffcanvas');
    },

    add: function() {
        new AddManager({
            url: taskEndpoint('add'),
            formSelector: '#tasks-add',
            resetSelector: '#add-reset',
            containerId: 'add-tasks-container'
        }, {
            onResetAfter: data => { initTaskForm(); markUnsavedClean(document.querySelector('#tasks-add')); document.querySelectorAll('[data-navigation-count]').forEach(node => { const count = data.navigationCounts?.[node.dataset.navigationCount]; if (Number.isSafeInteger(count)) node.textContent = String(count); }); }
        }).init();
    },

    edit: function() {
        new EditManager({
            url: taskEndpoint('edit'),
            formSelector: '#tasks-edit',
            refreshSelector: '#edit-refresh',
            containerId: 'edit-tasks-container'
        }, {
            onRefreshAfter: () => { initTaskForm(); markUnsavedClean(document.querySelector('#tasks-edit')); }
        }).init();
        initPanels();
    },

    show: function() {
        initPanels();
    }
};

function initPanels() {
    let openedSteps = [];
    new GeneralDataManager({ url: taskEndpoint('getGeneralData'), formSelector: '#getGeneralData' }, {
        onGeneralDataBefore: () => {
            openedSteps = [...document.querySelectorAll('#generalData .collapse.show')].map(node => node.id);
            return action !== 'edit' || confirmDiscardChanges(document.querySelector('#tasks-edit'));
        },
        onGeneralDataAfter: () => {
            initTaskForm();
            openedSteps.forEach(id => {
                const panel = document.getElementById(id);
                panel?.classList.add('show');
                document.querySelectorAll(`[aria-controls="${id}"]`).forEach(toggle => toggle.setAttribute('aria-expanded', 'true'));
            });
            if (action === 'edit') markUnsavedClean(document.querySelector('#tasks-edit'));
        }
    }).init();
    new MetaDataManager({ url: taskEndpoint('getMetaData'), formSelector: '#getMetaData', containerId: 'task-meta-content' }, {
        onMetaDataBefore: () => {
            const panel = document.getElementById('metaData');
            panel.style.height = `${panel.getBoundingClientRect().height}px`;
            panel.style.overflow = 'hidden';
        },
        onMetaDataAfter: async () => {
            const panel = document.getElementById('metaData');
            const content = document.getElementById('task-meta-content');
            try {
                if (!matchMedia('(prefers-reduced-motion: reduce)').matches) {
                    await panel.animate([{height:panel.style.height},{height:`${content.getBoundingClientRect().height}px`}], {duration:250,easing:'ease-in-out'}).finished;
                }
            } finally { panel.style.height = ''; panel.style.overflow = ''; }
        },
        onMetaDataError: () => {
            const panel = document.getElementById('metaData');
            panel.style.height = ''; panel.style.overflow = '';
        }
    }).init();
}

export async function replaceRelatedPanel(container, output) {
    if (!container) return;
    const opened = new Set([...container.querySelectorAll('.collapse.show, .collapsing')].filter(element => {
        return container.querySelector(`[aria-controls="${element.id}"]`)?.getAttribute('aria-expanded') === 'true';
    }).map(element => element.id));
    const template = document.createElement('template');
    template.innerHTML = output;
    template.content.querySelectorAll('.collapse').forEach(element => {
        if ( ! opened.has(element.id)) return;
        element.classList.add('show');
        template.content.querySelectorAll(`[aria-controls="${element.id}"]`).forEach(toggle => toggle.setAttribute('aria-expanded', 'true'));
    });

    if (container.innerHTML.trim() === template.innerHTML.trim()) return;

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        container.replaceChildren(template.content);
        return;
    }

    const height = container.getBoundingClientRect().height;
    await container.animate([{ opacity: 1 }, { opacity: 0 }], { duration: 120, easing: 'ease-in', fill: 'forwards' }).finished;
    if ( ! container.isConnected) return;
    const focusId = container.contains(document.activeElement) ? document.activeElement.id : null;
    container.replaceChildren(template.content);
    const nextHeight = container.getBoundingClientRect().height;
    container.style.height = `${height}px`;
    container.style.overflow = 'hidden';
    const animation = container.animate([
        { height: `${height}px`, opacity: 0 },
        { height: `${nextHeight}px`, opacity: 1 }
    ], { duration: 220, easing: 'ease-in-out', fill: 'forwards' });
    await animation.finished;
    container.getAnimations().forEach(current => current.cancel());
    container.style.height = '';
    container.style.overflow = '';
    if (focusId && document.activeElement === document.body) document.getElementById(focusId)?.focus({ preventScroll: true });
}

/** Inizializza soltanto l'editor del form principale Tasks. */
function initTaskForm() {
    const form = document.querySelector('#tasks-add, #tasks-edit');
    if (form) initTaskSteps(form);
}

/** @param {FormData} body Payload del form. @returns {unknown} Contesto locale aggiunto dall'eventuale lista incorporata. */
function taskRequestContext(body) {
    const detail = { body, context: null };
    document.dispatchEvent(new CustomEvent('essential:task-request', { detail }));
    return detail.context;
}
/** @param {Object} data Risposta server. @param {unknown} context Stato locale della richiesta. @returns {Promise<boolean>} True se la lista ha consumato i partial senza altre chiamate. */
async function consumeTaskResponse(data, context) {
    const detail = { data, context, handled: false, pending: [] };
    document.dispatchEvent(new CustomEvent('essential:task-response', { detail }));
    await Promise.all(detail.pending);
    return detail.handled;
}

let taskActionBusy = false;
function lockTaskActions() {
    if (!taskActionBusy) return;
    document.querySelectorAll('.task-action button, .task-action input[type="checkbox"]').forEach(control => {
        if (!control.disabled) { control.dataset.taskBusy = 'true'; control.disabled = true; }
    });
}

/** Ricarica le note con la stessa transizione dei pannelli Applications. */
async function reloadNotes(panel = document.querySelector('[data-task-related-panel]'), button = null) {
    if (!panel || panel.dataset.loading === 'true') return;
    const container = panel.querySelector('[data-related-items]');
    panel.dataset.loading = 'true';
    container.setAttribute('aria-busy', 'true');
    try {
        const body = new FormData(); body.set('uuid', panel.dataset.taskUuid);
        const response = await apiFetch(taskEndpoint('getNotes'), { method: 'POST', body, loader: button });
        const data = await response.json();
        if (data.result !== true || typeof data.output !== 'string') throw new Error('Invalid notes response');
        const replacements = [];
        if (container.isConnected) replacements.push(replaceRelatedPanel(container, data.output));
        Object.entries(data.stepOutputs || {}).forEach(([uuid, output]) => {
            document.querySelectorAll('[data-step-note-items]').forEach(node => {
                if (node.dataset.stepNoteItems === uuid) replacements.push(replaceRelatedPanel(node, output));
            });
        });
        await Promise.all(replacements);
    } catch (error) {
        if (panel.isConnected) showAlert('danger', panel.dataset.reloadError);
    } finally {
        delete panel.dataset.loading; container.removeAttribute('aria-busy');
    }
}

document.addEventListener('click', event => {
    const button = event.target.closest('[data-related-reload]');
    const panel = button?.closest('[data-task-related-panel]');
    if (panel) reloadNotes(panel, button);
});

document.addEventListener('change', event => {
    const checkbox = event.target.closest('[data-step-completion]');
    if (checkbox) checkbox.form.requestSubmit();
});

/** Invia azioni su task e passi; il server decide identità, appartenenza e permessi. */
document.addEventListener('submit', async event => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement) || !form.matches('.task-action')) return;
    event.preventDefault();
    const checkbox = form.querySelector('[data-step-completion]');
    if (taskActionBusy) { if (checkbox) checkbox.checked = !checkbox.checked; return; }
    taskActionBusy = true; document.documentElement.dataset.taskActionBusy = 'true'; lockTaskActions();
    try {
        if (form.dataset.confirm && !await askConfirm(form.dataset.confirm)) { if (checkbox) checkbox.checked = !checkbox.checked; return; }
        const body = new FormData(form);
        if (form.id === 'tasks-delete-note' && event.submitter) {
            body.set('uuid', event.submitter.value);
            if (event.submitter.dataset.taskUuid) body.set('task_uuid', event.submitter.dataset.taskUuid);
        }
        if (checkbox) body.set('completed', checkbox.checked ? '1' : '0');
        const context = taskRequestContext(body);
        const response = await apiFetch(taskEndpoint(form.dataset.endpoint), { method: 'POST', body });
        const data = await response.json();
        if (data.result !== true) { if (checkbox) checkbox.checked = !checkbox.checked; showAlert('danger', data.message); return; }
        if (!await consumeTaskResponse(data, context)) {
            if (form.dataset.endpoint === 'deleteNote') {
                await reloadNotes();
            } else if (action === 'showAll' && form.dataset.endpoint === 'setCurrent') {
                await ListManager.instances['showAll-tasks-container']?.showAll();
                lockTaskActions();
            } else {
                const generalForm = document.getElementById('getGeneralData');
                const metaForm = document.getElementById('getMetaData');
                if (generalForm) await GeneralDataManager.instances.get('#getGeneralData')?.getGeneralData(new FormData(generalForm));
                lockTaskActions();
                if (metaForm) await MetaDataManager.instances.get('#getMetaData')?.getMetaData(new FormData(metaForm));
            }
        }
        if (data.message) showAlert('success', data.message);
    } catch (error) {
        if (checkbox && checkbox.isConnected) checkbox.checked = !checkbox.checked;
    } finally {
        taskActionBusy = false; delete document.documentElement.dataset.taskActionBusy;
        document.querySelectorAll('[data-task-busy]').forEach(control => { control.disabled = false; delete control.dataset.taskBusy; });
    }
});

/** Usa lo stesso modale di Applications, limitato alle note del task o del passo. */
const noteModal = document.getElementById('tasks-note-modal');
if (noteModal) {
    const form = noteModal.querySelector('form');
    let resetRecord = null;
    let taskUuid = '';
    let taskTitle = '';
    let stepTitle = '';
    let stepUuid = '';
    /** Ripristina nota e contesto iniziali; una nota modificata conserva il proprio UUID. */
    const resetForm = () => {
        if (form.dataset.saving === 'true') return;
        form.reset();
        form.elements.namedItem('task_uuid').value = taskUuid;
        form.elements.namedItem('step_uuid').value = resetRecord?.step_uuid || stepUuid;
        form.elements.namedItem('uuid').value = resetRecord?.uuid || '';
        form.elements.namedItem('content').value = resetRecord?.content || '';
        noteModal.querySelector('[data-note-task-title]').textContent = taskTitle;
        noteModal.querySelector('[data-note-step-context]').hidden = !form.elements.namedItem('step_uuid').value;
        noteModal.querySelector('[data-note-step-title]').textContent = stepTitle;
        const feedback = noteModal.querySelector('[data-note-feedback]');
        showModalMessages(form, feedback, feedback.dataset.defaultFeedback);
    };
    noteModal.querySelector('[data-modal-reset]').addEventListener('click', resetForm);
    noteModal.addEventListener('show.bs.modal', event => {
        const trigger = event.relatedTarget;
        const record = trigger?.dataset.taskNoteRecord ? JSON.parse(trigger.dataset.taskNoteRecord) : null;
        resetRecord = record ? structuredClone(record) : null;
        taskUuid = trigger?.dataset.taskUuid || '';
        taskTitle = trigger?.dataset.taskTitle || '';
        stepUuid = trigger?.dataset.taskNoteStep || '';
        stepTitle = trigger?.dataset.taskStepTitle || '';
        resetForm();
    });
    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (form.dataset.saving === 'true') return;
        const body = new FormData(form);
        const context = taskRequestContext(body);
        const controls = [...form.querySelectorAll('input, textarea, button')].map(node => [node, node.disabled]);
        controls.forEach(([node]) => node.disabled = true); form.dataset.saving = 'true';
        const feedback = noteModal.querySelector('[data-note-feedback]');
        try {
            const response = await apiFetch(taskEndpoint('saveNote'), { method: 'POST', body });
            const data = await response.json();
            showModalMessages(form, feedback, data.message, data.result === true ? 'success' : (data.changed === false ? 'neutral' : 'error'), data.errors || {});
            if (data.result === true) {
                form.elements.namedItem('uuid').value = data.uuid;
                resetRecord = { uuid: data.uuid, content: form.elements.namedItem('content').value, step_uuid: form.elements.namedItem('step_uuid').value };
                if (!await consumeTaskResponse(data, context)) await reloadNotes();
            }
        } catch (error) {
            showModalMessages(form, feedback, noteModal.dataset.saveError, 'error');
        } finally {
            controls.forEach(([node, disabled]) => node.disabled = disabled); delete form.dataset.saving;
        }
    });
}

if (actions[action]) actions[action]();
initTaskForm();
