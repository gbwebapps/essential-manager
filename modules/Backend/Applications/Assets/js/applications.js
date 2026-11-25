import { disposeNoteCollapses } from './NoteCollapse.js';
import { initOwnerStepActions } from './OwnerStepActions.js';
import { fillTaskSteps } from './TaskSteps.js';
import { TaskModalEditor } from './TaskModalEditor.js';
import { updateNavigationCounts, showModalMessages } from './ManagerFeedback.js';
import { urlbase, action, apiFetch, markUnsavedClean, showAlert, initOffcanvasAutoClose, initRangeDatePicker, askConfirm } from './backend.js';
import { ApplicationDraft } from './ApplicationDraft.js';
import { setControlValue } from '../components/TomSelect.js';
import { ListManager, AddManager, EditManager, DeleteManager, GeneralDataManager, MetaDataManager } from './modules/Crud.js';

let applicationDraft = null;

const actions = {
    showAll: function() {
        const listManager = new ListManager({
            controller: 'applications',
            url: urlbase + 'backend/applications/showAll',
            rootId: 'applications-list',
            containerId: 'showAll-applications-container',
            searchFields: ['name', 'project_uuid'],
            searchDates: ['created_at', 'updated_at']
        }, {
            onShowAfter: async data => {
                updateNavigationCounts(data);
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
            controller: 'applications',
            urls: {
                softDelete: urlbase + 'backend/applications/softDelete',
                restoreDelete: urlbase + 'backend/applications/restoreDelete',
                hardDelete: urlbase + 'backend/applications/hardDelete'
            },
            listManager: listManager
        }).init();

        initRangeDatePicker('#wrapper-applications-created_at-from', '#wrapper-applications-created_at-to');
        initRangeDatePicker('#wrapper-applications-updated_at-from', '#wrapper-applications-updated_at-to');
        initOffcanvasAutoClose('applications-actionsOffcanvas');
    },

    add: function() {
        new AddManager({
            url: urlbase + 'backend/applications/add',
            formSelector: '#applications-add',
            resetSelector: '#add-reset',
            containerId: 'add-applications-container'
        }, {
            onAddBefore: formData => applicationDraft.appendTo(formData),
            onResetAfter: data => { applicationDraft.reset(); updateNavigationCounts(data); },
            onAddAfter: updateNavigationCounts
        }).init();
    },

    edit: function() {
        new EditManager({
            url: urlbase + 'backend/applications/edit',
            formSelector: '#applications-edit',
            refreshSelector: '#edit-refresh',
            containerId: 'edit-applications-container'
        }, {
            onRefreshAfter: () => markUnsavedClean(document.querySelector('#applications-edit'))
        }).init();
        initPanels();
    },

    show: function() {
        initPanels();
    }
};

function initPanels() {
    new GeneralDataManager({ url: urlbase + 'backend/applications/getGeneralData', formSelector: '#getGeneralData' }).init();
    new MetaDataManager({ url: urlbase + 'backend/applications/getMetaData', formSelector: '#getMetaData' }).init();
}

function initApplicationActions() {
    document.querySelectorAll('[data-application-action-modal]').forEach(modal => {
        const form = modal.querySelector('[data-application-action-form]');
        const taskEditor = new TaskModalEditor(form);
        let resetRecord = null;
        let contextUuid = '';
        let contextName = '';

        /** Ripristina i campi iniziali mantenendo UUID, contesto e identità della bozza. */
        const resetForm = () => {
            if (form.dataset.saving === 'true') return;
            const record = resetRecord;
            form.reset();
            form.dataset.draftId = record?.draftId || '';
            taskEditor.reset(record);
            showModalMessages(form, modal.querySelector('[data-application-action-feedback]'), applicationDraft ? applicationDraft.text('notice') : modal.dataset.saveNotice);
            modal.querySelector('[data-application-action-submit-label]').textContent = applicationDraft ? applicationDraft.text(record ? 'updateButton' : 'addButton') : modal.dataset.saveLabel;
            modal.querySelector('.modal-title').textContent = record ? modal.dataset.editTitle : modal.dataset.addTitle;
            form.elements.namedItem('uuid').value = record?.uuid || '';
            for (const name of ['title', 'description', 'status', 'content']) {
                const field = form.elements.namedItem(name);
                if (field) field.value = record?.[name] ?? (name === 'status' ? 'todo' : '');
            }
            const status = form.elements.namedItem('status');
            // TomSelect applica il reset nativo nel ciclo successivo.
            if (status) setTimeout(() => setControlValue(status, record?.status || 'todo'), 0);
            form.elements.namedItem('application_uuid').value = contextUuid;
            const contextField = form.querySelector('[data-task-owner-name], [data-application-action-name]');
            if (contextField instanceof HTMLInputElement) contextField.value = contextName;
            else if (contextField) contextField.textContent = contextName;
        };
        modal.querySelector('[data-modal-reset]').addEventListener('click', resetForm);
        modal.addEventListener('show.bs.modal', event => {
            if (form.dataset.saving === 'true') { event.preventDefault(); return; }
            taskEditor.cancel();
            const trigger = event.relatedTarget;
            const panel = trigger?.closest('[data-application-related-panel]');
            const kind = modal.dataset.applicationActionKind;
            const record = applicationDraft ? applicationDraft.find(kind, trigger?.dataset.draftId) : (trigger?.dataset.applicationRecord ? JSON.parse(trigger.dataset.applicationRecord) : null);
            resetRecord = record ? structuredClone(record) : null;
            contextUuid = trigger?.dataset.applicationUuid || panel?.dataset.applicationUuid || modal.dataset.applicationUuid || '';
            contextName = applicationDraft ? applicationDraft.applicationName() : (trigger?.dataset.applicationName || panel?.dataset.applicationName || modal.dataset.applicationName || '');
            resetForm();
            if (kind === 'task' && record?.uuid && ! applicationDraft) {
                const body = new FormData();
                body.set('uuid', record.uuid); body.set('application_uuid', contextUuid);
                taskEditor.load(urlbase + 'backend/applications/getTaskForModal', body,
                    loaded => { resetRecord = structuredClone(loaded); resetForm(); },
                    () => showModalMessages(form, modal.querySelector('[data-application-action-feedback]'), modal.dataset.saveError, 'error'));
            }
        });
        modal.addEventListener('hidden.bs.modal', () => {
            taskEditor.cancel(); resetRecord = null; resetForm();
        });
        form.addEventListener('submit', async event => {
            event.preventDefault();
            const feedback = modal.querySelector('[data-application-action-feedback]');
            if (form.dataset.saving === 'true' || !taskEditor.ready || taskEditor.steps?.dataset.busy === 'true') return;
            const draftMode = applicationDraft && modal.dataset.draftMode === 'true';
            const kind = modal.dataset.applicationActionKind;
            const body = new FormData(form);
            if (kind === 'task') taskEditor.appendTo(body);
            if (draftMode) {
                body.delete('uuid');
                body.delete('application_uuid');
            }
            const applicationUuid = form.elements.namedItem('application_uuid').value;
            const draftId = form.dataset.draftId || '';
            const generation = applicationDraft?.generation;
            form.dataset.saving = 'true';
            const controls = [...form.querySelectorAll('input, textarea, select, button')].map(control => [control, control.disabled]);
            controls.forEach(([control]) => { control.disabled = true; });
            const endpoint = draftMode ? (kind === 'task' ? 'validateTaskDraft' : 'validateNoteDraft') : (kind === 'task' ? 'saveTask' : 'saveNote');
            try {
                const response = await apiFetch(urlbase + 'backend/applications/' + endpoint, { method: 'POST', body, loader: form.querySelector('[type="submit"]') });
                const data = await response.json();
                if (draftMode && generation !== applicationDraft.generation) return;
                if (data.result !== true) {
                    showModalMessages(form, feedback, data.message || modal.dataset.saveError, data.changed === false ? 'neutral' : 'error', data.errors || {});
                    taskEditor.showErrors();
                    return;
                }
                if (draftMode) {
                    if ( ! applicationDraft.hasChanges(kind, data.data, draftId)) {
                        showModalMessages(form, feedback, modal.dataset.noChanges);
                        return;
                    }
                    form.dataset.draftId = applicationDraft.upsert(kind, data.data, draftId, data.previewOutput);
                    resetRecord = { ...structuredClone(data.data), draftId: form.dataset.draftId };
                    modal.querySelector('[data-application-action-submit-label]').textContent = applicationDraft.text('updateButton');
                    showModalMessages(form, feedback, applicationDraft.text('updatedFeedback'), 'success');
                } else {
                    const record = data.record;
                    resetRecord = structuredClone(record);
                    form.elements.namedItem('uuid').value = record.uuid;
                    modal.querySelector('.modal-title').textContent = modal.dataset.editTitle;
                    showModalMessages(form, feedback, data.message, 'success');
                    updateNavigationCounts(data);
                    if (kind === 'task') {
                        taskEditor.reset(record);
                        setControlValue(form.elements.namedItem('status'), record.status);
                        form.querySelectorAll('input, textarea, select, button').forEach(control => {
                            if ( ! controls.some(([existing]) => existing === control)) controls.push([control, control.disabled]);
                            control.disabled = true;
                        });
                    }
                    const panel = document.querySelector(`[data-application-related-panel="${kind}"][data-application-uuid="${applicationUuid}"]`);
                    if (panel) await reloadRelatedPanel(panel, panel.querySelector('[data-related-reload]'));
                    const list = ListManager.instances['showAll-applications-container'];
                    if (list) await list.showAll();
                }
            } catch (error) {
                showModalMessages(form, feedback, modal.dataset.saveError, 'error');
            } finally {
                controls.forEach(([control, disabled]) => { if (control.isConnected) control.disabled = disabled; });
                delete form.dataset.saving;
            }
        });
    });
}

function initRelatedPanels() {
    document.addEventListener('click', async event => {
        const button = event.target.closest('[data-related-reload]');
        const panel = button?.closest('[data-application-related-panel]');
        if ( ! panel || panel.hasAttribute('data-application-draft-panel')) return;
        await reloadRelatedPanel(panel, button);
    });
}

async function reloadRelatedPanel(panel, button) {
    if (panel.dataset.loading === 'true') return;
    const container = panel.querySelector('[data-related-items]');
    const kind = panel.dataset.applicationRelatedPanel;
    if ( ! ['task', 'note'].includes(kind)) return;

    panel.dataset.loading = 'true';
    container.setAttribute('aria-busy', 'true');
    try {
        let output = container.innerHTML;
        if (panel.dataset.applicationUuid) {
            const body = new FormData();
            body.set('uuid', panel.dataset.applicationUuid);
            body.set('context', panel.dataset.readOnly === 'true' ? 'show' : 'edit');
            const response = await apiFetch(urlbase + 'backend/applications/' + (kind === 'task' ? 'getTasks' : 'getNotes'), { method: 'POST', body, loader: button });
            const data = await response.json();
            if (data.result !== true || typeof data.output !== 'string') throw new Error('Invalid related panel response');
            updateNavigationCounts(data);
            output = data.output;
        }
        if ( ! container.isConnected) return;
        button.disabled = true;
        await replaceRelatedPanel(container, output);
    } catch (error) {
        if (panel.isConnected) showAlert('danger', panel.dataset.reloadError);
    } finally {
        delete panel.dataset.loading;
        container.removeAttribute('aria-busy');
        button.disabled = false;
    }
}

async function replaceRelatedPanel(container, output) {
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

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        disposeNoteCollapses(container);
        container.replaceChildren(template.content);
        return;
    }

    const height = container.getBoundingClientRect().height;
    await container.animate([{ opacity: 1 }, { opacity: 0 }], { duration: 120, easing: 'ease-in', fill: 'forwards' }).finished;
    if ( ! container.isConnected) return;
    const focusId = container.contains(document.activeElement) ? document.activeElement.id : null;
    disposeNoteCollapses(container);
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

if (action === 'add') {
    applicationDraft = new ApplicationDraft({
        replacePanel: replaceRelatedPanel,
        resetModals: () => {
            document.querySelectorAll('[data-application-action-modal]').forEach(modal => {
                const form = modal.querySelector('form');
                form.reset();
                form.dataset.draftId = '';
                fillTaskSteps(form.querySelector('[data-task-steps]'));
            });
        }
    });
}

/** Cancella solo l'elemento del pannello corrente, senza toccare il form principale. */
document.addEventListener('submit', async event => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement) || !form.matches('[data-related-delete]')) return;
    const panel = form.closest('[data-application-related-panel]');
    if (!panel) return;
    event.preventDefault();
    if (form.dataset.busy === 'true') return;
    form.dataset.busy = 'true';
    const button = form.querySelector('[type="submit"]');
    button.disabled = true;
    try {
        if (!await askConfirm(form.dataset.confirm)) return;
        const endpoint = form.dataset.relatedDelete === 'task' ? 'deleteTask' : 'deleteNote';
        const response = await apiFetch(urlbase + 'backend/applications/' + endpoint, { method: 'POST', body: new FormData(form) });
        const data = await response.json();
        if (data.result !== true) { showAlert('danger', data.message); return; }
        updateNavigationCounts(data);
        await reloadRelatedPanel(panel, panel.querySelector('[data-related-reload]'));
        if (data.message) showAlert('success', data.message);
    } catch (error) {
        showAlert('danger', panel.dataset.reloadError);
    } finally {
        delete form.dataset.busy;
        if (button.isConnected) button.disabled = false;
    }
});

initRelatedPanels();
initApplicationActions();
initOwnerStepActions('[data-application-related-panel="task"]', reloadRelatedPanel);

if (actions[action]) {
    actions[action]();
}
