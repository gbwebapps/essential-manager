import { apiFetch, initOffcanvasAutoClose, initRangeDatePicker, showAlert, urlbase } from './backend.js';
import { GeneralDataManager, ListManager } from './modules/Crud.js';
import { ExportCsvManager } from './components/ExportCsv.js';

if (document.getElementById('messages-list')) {
    const list = new ListManager({
        controller: 'messages',
        url: urlbase + 'backend/messages/showAll',
        rootId: 'messages-list',
        containerId: 'showAll-messages-container',
        searchFields: ['name', 'email'],
        searchDates: ['created_at']
    }, {
        onShowAfter: data => {
            list.root.querySelectorAll('[data-trash-count]').forEach(badge => {
                const count = data.trashCounts?.[badge.dataset.trashCount];
                if (Number.isSafeInteger(count) && count >= 0) {
                    badge.textContent = String(count);
                    badge.hidden = false;
                }
            });
        }
    });

    const status = new URLSearchParams(window.location.search).get('status');
    const statuses = ['all', 'unread', 'read', 'archived'];
    if (statuses.includes(status)) {
        list.updateState('trash_filter', status);
        list.resetSortingAndPagination();
    } else if (!statuses.includes(list.state.trash_filter)) {
        list.updateState('trash_filter', 'all');
    }

    if (list.state.column === 'subject') list.resetSortingAndPagination();
    list.init();
    initRangeDatePicker('#wrapper-messages-created_at-from', '#wrapper-messages-created_at-to');
    new ExportCsvManager({ controller: 'messages' }).init();
    initOffcanvasAutoClose('messages-actionsOffcanvas');
}

const detail = document.getElementById('show-messages-container');
if (detail) {
    let submitting = false;
    const generalData = new GeneralDataManager({
        url: urlbase + 'backend/messages/getGeneralData',
        formSelector: '#getMessageGeneralData',
        containerId: 'messageGeneralData'
    }, {
        onGeneralDataBefore: () => !submitting
    });
    generalData.init();

    detail.addEventListener('submit', async event => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || form.id !== 'message-status-form') return;
        event.preventDefault();
        if (submitting || generalData.isSubmitting || !event.submitter) return;

        const buttons = form.querySelectorAll('button[name="status"]');
        submitting = true;
        const body = new FormData(form, event.submitter);
        // apiFetch sends the current CSRF token, including on password retry.
        body.delete(form.dataset.csrfName);
        buttons.forEach(button => { button.disabled = true; });

        try {
            const response = await apiFetch(form.action, { method: 'POST', body });
            const data = await response.json();
            if (data.result === true) {
                const currentForm = detail.querySelector('#message-status-form');
                currentForm.elements.previousStatus.value = data.status;
                detail.querySelectorAll('[data-message-status]').forEach(label => {
                    label.hidden = label.dataset.messageStatus !== data.status;
                });
                currentForm.querySelectorAll('button[name="status"]').forEach(button => {
                    button.hidden = button.value === data.status;
                });
            }
            if (data.message) showAlert(data.result === true ? 'success' : 'danger', data.message);
        } catch (error) {
            console.error('Message status update failed:', error);
        } finally {
            buttons.forEach(button => { button.disabled = false; });
            submitting = false;
        }
    });
}
