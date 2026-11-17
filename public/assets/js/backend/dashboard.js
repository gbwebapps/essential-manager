import { apiFetch, getAjaxErrorContext, smoothReplace } from './backend.js';

document.getElementById('index-dashboard-container')?.addEventListener('click', async event => {
    const button = event.target.closest('[data-dashboard-refresh]');
    if (!button || button.disabled) return;

    const card = button.closest('[data-dashboard-summary]');
    const content = card.querySelector('[data-dashboard-summary-content]');
    const error = card.querySelector('[data-dashboard-refresh-error]');
    content.setAttribute('aria-busy', 'true');
    error.classList.add('d-none');

    try {
        const response = await apiFetch(button.dataset.dashboardRefresh, { method: 'POST', loader: button });
        const data = await response.json();

        if (data.result === true && typeof data.output === 'string' && data.output.trim()) {
            smoothReplace(content, data.output);
        } else {
            error.classList.remove('d-none');
        }
    } catch (requestError) {
        if (!getAjaxErrorContext(requestError)?.handled) error.classList.remove('d-none');
    } finally {
        content.removeAttribute('aria-busy');
    }
});