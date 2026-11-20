import { urlbase, action, apiFetch, markUnsavedClean, showAlert, initOffcanvasAutoClose, initRangeDatePicker } from './backend.js';
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
                if (data.navigationCounts) {
                    document.querySelectorAll('[data-navigation-count]').forEach(badge => {
                        const count = data.navigationCounts[badge.dataset.navigationCount];
                        if (Number.isSafeInteger(count)) badge.textContent = String(count);
                    });
                }
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
            onResetAfter: () => applicationDraft.reset()
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

function focusStep(input) {
    if ( ! input) return;
    input.focus({ preventScroll: true });
    const rect = input.getBoundingClientRect();
    if (rect.top < 0 || rect.bottom > window.innerHeight) {
        input.scrollIntoView({ block: 'nearest', behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth' });
    }
}

function updateStepRows(editor) {
    const rows = [...editor.querySelectorAll('[data-task-step-list] > [data-task-step-row]')];
    rows.forEach((row, index) => {
        const input = row.querySelector('[data-step-title]');
        const id = `${editor.dataset.fieldPrefix}-step-${row.dataset.stepKey}`;
        input.id = id;
        input.name = `steps[${index}][title]`;
        row.querySelector('[data-step-uuid]').name = `steps[${index}][uuid]`;
        row.querySelector('[data-step-number]').textContent = String(index + 1);
        row.querySelector('[data-work-field-error]').dataset.workFieldError = `steps.${index}.title`;
        const label = row.querySelector('[data-step-label]');
        label.htmlFor = id;
        label.textContent = `${editor.dataset.stepLabel} ${index + 1}`;
        row.querySelector('[data-task-step-remove]').disabled = editor.dataset.busy === 'true' || rows.length === 1;
    });
    editor.querySelector('[data-task-step-add]').disabled = editor.dataset.busy === 'true';
}

function createStepRow(editor, step = {}) {
    const row = editor.querySelector('[data-task-step-template]').content.firstElementChild.cloneNode(true);
    row.dataset.stepKey = editor.dataset.nextStepKey || '0';
    editor.dataset.nextStepKey = String(Number(row.dataset.stepKey) + 1);
    row.querySelector('[data-step-title]').value = step.title || '';
    row.querySelector('[data-step-uuid]').value = step.uuid || '';
    return row;
}

function fillTaskSteps(editor, steps) {
    if ( ! editor) return;
    editor.dataset.nextStepKey = '0';
    delete editor.dataset.busy;
    const items = Array.isArray(steps) && steps.length ? steps : [{}];
    editor.querySelector('[data-task-step-list]').replaceChildren(...items.map(step => createStepRow(editor, step)));
    updateStepRows(editor);
}

async function animateStepRow(row, removing = false) {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    const height = row.getBoundingClientRect().height;
    row.style.overflow = 'hidden';
    const animation = row.animate(removing ? [
        { height: `${height}px`, opacity: 1 }, { height: '0px', opacity: 0 }
    ] : [
        { height: '0px', opacity: 0 }, { height: `${height}px`, opacity: 1 }
    ], { duration: 180, easing: 'ease-in-out' });
    await animation.finished;
    row.style.overflow = '';
}

function initTaskSteps(form) {
    const editor = form.querySelector('[data-task-steps]');
    if ( ! editor) return;
    editor.addEventListener('click', async event => {
        const add = event.target.closest('[data-task-step-add]');
        const remove = event.target.closest('[data-task-step-remove]');
        if (( ! add && ! remove) || editor.dataset.busy === 'true') return;
        const list = editor.querySelector('[data-task-step-list]');
        if (remove && list.children.length <= 1) return;
        editor.dataset.busy = 'true';
        updateStepRows(editor);
        try {
            if (add) {
                const row = createStepRow(editor);
                list.append(row);
                updateStepRows(editor);
                await animateStepRow(row);
                focusStep(row.querySelector('[data-step-title]'));
            } else {
                const row = remove.closest('[data-task-step-row]');
                const next = row.nextElementSibling || row.previousElementSibling;
                await animateStepRow(row, true);
                row.remove();
                focusStep(next?.querySelector('[data-step-title]'));
            }
        } finally {
            delete editor.dataset.busy;
            updateStepRows(editor);
        }
    });
}

/** Svuota solo gli errori del modale corrente, mantenendo i messaggi degli altri form. */
function clearRelatedErrors(form) {
    form.querySelectorAll('[data-work-field-error], [data-work-form-errors]').forEach(box => { box.textContent = ''; });
}

/** Mostra ogni errore server sotto il relativo campo; i parametri non visibili restano nel riepilogo del form. */
function showRelatedErrors(form, errors) {
    const boxes = [...form.querySelectorAll('[data-work-field-error]')];
    const remaining = [];
    Object.entries(errors).forEach(([field, message]) => {
        const box = boxes.find(candidate => candidate.dataset.workFieldError === field);
        if (box) box.textContent = String(message);
        else remaining.push(String(message));
    });
    form.querySelector('[data-work-form-errors]').textContent = remaining.join(' ');
}

function initApplicationActions() {
    document.querySelectorAll('[data-application-action-modal]').forEach(modal => {
        const form = modal.querySelector('[data-application-action-form]');
        initTaskSteps(form);
        modal.addEventListener('show.bs.modal', event => {
            const trigger = event.relatedTarget;
            const panel = trigger?.closest('[data-application-related-panel]');
            const kind = modal.dataset.applicationActionKind;
            const record = applicationDraft ? applicationDraft.find(kind, trigger?.dataset.draftId) : (trigger?.dataset.applicationRecord ? JSON.parse(trigger.dataset.applicationRecord) : null);
            form.dataset.draftId = record?.draftId || '';
            form.reset();
            fillTaskSteps(form.querySelector('[data-task-steps]'), record?.steps);
            clearRelatedErrors(form);
            modal.querySelector('[data-application-action-feedback]').classList.remove('app-text-danger');
            modal.querySelector('[data-application-action-feedback]').textContent = applicationDraft ? applicationDraft.text('notice') : modal.dataset.saveNotice;
            modal.querySelector('[data-application-action-submit-label]').textContent = applicationDraft ? applicationDraft.text(record ? 'updateButton' : 'addButton') : '';
            if ( ! applicationDraft) modal.querySelector('[data-application-action-submit-label]').textContent = modal.dataset.saveLabel;
            modal.querySelector('.modal-title').textContent = record ? modal.dataset.editTitle : modal.dataset.addTitle;
            form.querySelector('[name="uuid"]').value = record?.uuid || '';
            for (const name of ['title', 'description', 'status', 'content']) {
                const field = form.elements.namedItem(name);
                if (field && record && record[name] !== undefined) field.value = record[name];
            }
            const status = form.elements.namedItem('status');
            // Il reset condiviso di TomSelect termina nel ciclo successivo.
            if (status) setTimeout(() => setControlValue(status, record?.status || 'todo'), 0);
            form.querySelector('[name="application_uuid"]').value = trigger?.dataset.applicationUuid || panel?.dataset.applicationUuid || modal.dataset.applicationUuid || '';
            form.querySelector('[data-application-action-name]').textContent = applicationDraft ? applicationDraft.applicationName() : (trigger?.dataset.applicationName || panel?.dataset.applicationName || modal.dataset.applicationName || '');
        });
        form.addEventListener('submit', async event => {
            event.preventDefault();
            const feedback = modal.querySelector('[data-application-action-feedback]');
            clearRelatedErrors(form);
            if (form.dataset.saving === 'true') return;
            const draftMode = applicationDraft && modal.dataset.draftMode === 'true';
            const kind = modal.dataset.applicationActionKind;
            const body = new FormData(form);
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
                feedback.classList.remove('app-text-danger');
                if (data.result !== true) {
                    feedback.classList.add('app-text-danger');
                    if (data.errors) showRelatedErrors(form, data.errors);
                    feedback.textContent = data.message || modal.dataset.saveError;
                    return;
                }
                if (draftMode) {
                    if ( ! applicationDraft.hasChanges(kind, data.data, draftId)) {
                        feedback.textContent = modal.dataset.noChanges;
                        return;
                    }
                    form.dataset.draftId = applicationDraft.upsert(kind, data.data, draftId);
                    modal.querySelector('[data-application-action-submit-label]').textContent = applicationDraft.text('updateButton');
                    feedback.textContent = applicationDraft.text('updatedFeedback');
                } else {
                    const record = data.record;
                    form.elements.namedItem('uuid').value = record.uuid;
                    modal.querySelector('.modal-title').textContent = modal.dataset.editTitle;
                    feedback.textContent = data.message;
                    if (kind === 'task') {
                        fillTaskSteps(form.querySelector('[data-task-steps]'), record.steps);
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
                feedback.classList.add('app-text-danger');
                feedback.textContent = modal.dataset.saveError;
            } finally {
                controls.forEach(([control, disabled]) => { if (control.isConnected) control.disabled = disabled; });
                delete form.dataset.saving;
            }
            if ( ! window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                feedback.animate([{ opacity: .35 }, { opacity: 1 }], { duration: 220, easing: 'ease-out' });
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
        template.content.querySelector(`[aria-controls="${element.id}"]`)?.setAttribute('aria-expanded', 'true');
    });

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

initRelatedPanels();
initApplicationActions();

if (actions[action]) {
    actions[action]();
}
