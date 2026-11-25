import { apiFetch, urlbase, showAlert } from './backend.js';

/**
 * Usa le azioni Tasks autorizzate per completare passi nelle schede dei proprietari.
 * @param {string} panelSelector Selettore interno del pannello Projects oppure Applications.
 * @param {Function} reloadPanel Renderer esistente, mantiene aperti i dettagli durante il refresh.
 * @returns {void}
 */
export function initOwnerStepActions(panelSelector, reloadPanel) {
    let busy = false;
    document.addEventListener('change', event => {
        const checkbox = event.target.closest('[data-owner-step-completion]');
        if (checkbox?.closest(panelSelector)) checkbox.form.requestSubmit();
    });
    document.addEventListener('submit', async event => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || !form.matches('[data-owner-step-form]')) return;
        const panel = form.closest(panelSelector);
        if (!panel) return;
        event.preventDefault();
        const checkbox = form.querySelector('[data-owner-step-completion]');
        if (busy || panel.dataset.loading === 'true') { checkbox.checked = !checkbox.checked; return; }
        const body = new FormData(form);
        const completed = checkbox.checked;
        body.set('completed', completed ? '1' : '0');
        const controls = [...panel.querySelectorAll('[data-owner-step-completion]')].map(node => [node, node.disabled]);
        controls.forEach(([node]) => { node.disabled = true; });
        busy = true;
        try {
            const response = await apiFetch(urlbase + 'backend/tasks/' + (completed ? 'completeStep' : 'reopenStep'), { method: 'POST', body });
            const data = await response.json();
            if (data.result !== true) {
                checkbox.checked = !completed;
                showAlert('danger', data.message || panel.dataset.reloadError);
                return;
            }
            await reloadPanel(panel, panel.querySelector('[data-related-reload]'));
        } catch (error) {
            if (checkbox.isConnected) checkbox.checked = !completed;
            showAlert('danger', panel.dataset.reloadError);
        } finally {
            controls.forEach(([node, disabled]) => { if (node.isConnected) node.disabled = disabled; });
            busy = false;
        }
    });
}
