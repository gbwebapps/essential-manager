/** Aggiorna solo i badge ricevuti dal server per le sezioni autorizzate. */
export function updateNavigationCounts(data) {
    document.querySelectorAll('[data-navigation-count]').forEach(badge => {
        const count = data?.navigationCounts?.[badge.dataset.navigationCount];
        if (Number.isSafeInteger(count) && count >= 0) badge.textContent = String(count);
    });
}

/** Conferma evidente nel posto già occupato dal messaggio, senza modificare il contenuto del form. */
export function modalFeedback(element, message, state = 'neutral') {
    element.removeAttribute('data-i18n-text');
    element.classList.toggle('app-text-danger', state === 'error');
    element.classList.toggle('text-success', state === 'success');
    element.classList.toggle('fw-bold', state === 'success');
    element.replaceChildren();
    if (state === 'success') {
        const icon = document.createElement('i');
        icon.className = 'fa-solid fa-circle-check me-2';
        icon.setAttribute('aria-hidden', 'true');
        element.append(icon);
    }
    element.append(document.createTextNode(String(message || '')));
}

/**
 * Aggiorna feedback ed errori server negli spazi fissi della vista, senza animazioni.
 * @param {HTMLFormElement} form Form del modale.
 * @param {HTMLElement} feedback Area del messaggio generale.
 * @param {string} message Messaggio già tradotto.
 * @param {'neutral'|'success'|'error'} state Stato della risposta.
 * @param {Object<string,string>} errors Errori indicizzati per nome del campo.
 * @returns {void}
 */
export function showModalMessages(form, feedback, message, state = 'neutral', errors = {}) {
    const fields = [...form.querySelectorAll('[data-work-field-error]')];
    fields.forEach(node => { node.textContent = ''; });
    const remaining = [];
    Object.entries(errors).forEach(([field, error]) => {
        const node = fields.find(candidate => candidate.dataset.workFieldError === field);
        if (node) node.textContent = String(error);
        else remaining.push(String(error));
    });
    const summary = form.querySelector('[data-work-form-errors]');
    if (summary) summary.textContent = remaining.join(' ');
    modalFeedback(feedback, message, state);
}
