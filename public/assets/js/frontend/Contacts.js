import { apiFetch, getAjaxErrorContext, handleValidationErrors } from './frontend.js';

const form = document.getElementById('contacts-form');
if (form) {
    const submit = form.querySelector('button[type="submit"]');
    const feedback = document.getElementById('contact-feedback');
    const feedbackMessage = feedback.querySelector('[data-contact-feedback-message]');
    const ready = submit.querySelector('[data-contact-ready]');
    const sending = submit.querySelector('[data-contact-sending]');

    let feedbackTimeout;

    const hideFeedback = () => {
        window.clearTimeout(feedbackTimeout);
        feedback.hidden = true;
    };
    feedback.querySelector('.contact-feedback-close').addEventListener('click', hideFeedback);

    /**
     * Displays a dismissible submission result at the top of the window.
     * @param {string} message Localized message.
     * @param {boolean} success Whether the submission was saved.
     * @returns {void}
     */
    const showFeedback = (message, success) => {
        hideFeedback();
        feedbackMessage.textContent = message;
        feedback.classList.toggle('contact-feedback-success', success);
        feedback.classList.toggle('contact-feedback-error', !success);
        feedback.setAttribute('role', success ? 'status' : 'alert');
        feedback.hidden = false;
        feedbackTimeout = window.setTimeout(hideFeedback, 6000);
    };

    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (submit.disabled) return;

        submit.disabled = true;
        submit.setAttribute('aria-busy', 'true');
        hideFeedback();
        ready.hidden = true;
        sending.hidden = false;
        form.querySelectorAll('[id$="-error"]').forEach(error => { error.textContent = ''; });

        try {
            const body = new FormData(form);
            // The AJAX header carries the latest token after each attempt.
            body.delete(form.dataset.csrfName);
            const response = await apiFetch(form.action, { method: 'POST', body, showLoader: false, notifyOnError: false });
            const data = await response.json();
            if (data.errors) handleValidationErrors(data.errors);
            if (data.message) showFeedback(data.message, data.result === true);
            if (data.result === true) form.reset();
        } catch (error) {
            showFeedback(getAjaxErrorContext(error)?.message || form.dataset.failure, false);
        } finally {
            submit.disabled = false;
            submit.removeAttribute('aria-busy');
            ready.hidden = false;
            sending.hidden = true;
        }
    });
}
