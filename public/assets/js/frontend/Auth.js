/* Import delle costanti e utility da backend.js */
import { action, urlbase, apiFetch, showAlert, handleValidationErrors } from './frontend.js';

/* Import dei componenti dalla sottocartella */
import { LoginManager, ResetPasswordManager, SetPasswordManager, VerifyManager } from './modules/Auth.js';

const actions = {
    register: function() {
        const form = document.getElementById('register_form');
        if (!form) return;
        let submitting = false;
        form.addEventListener('submit', async event => {
            event.preventDefault();
            if (submitting) return;
            submitting = true;
            try {
                const response = await apiFetch(urlbase + 'auth/register', { method: 'POST', body: new FormData(form) });
                const data = await response.json();
                if (data.errors) handleValidationErrors(data.errors);
                if (data.message) showAlert(data.result === true ? 'success' : 'danger', data.message);
                if (data.result === true) form.reset();
            } catch (error) {
                console.error('Registrazione non completata:', error);
            } finally {
                submitting = false;
            }
        });
    },
    login: function() {
        
        const loginManager = new LoginManager();
        loginManager.init();
    }, 

    resetPassword: function() {
        
        const resetPasswordManager = new ResetPasswordManager();
        resetPasswordManager.init();
    }, 

    setPassword: function() {
        
        const setPasswordManager = new SetPasswordManager();
        setPasswordManager.init();
    },

    restore: function() {
        const form = document.getElementById('restore-account-form');
        let submitting = false;
        form?.addEventListener('submit', async event => {
            event.preventDefault();
            if (submitting) return;
            submitting = true;

            try {
                const response = await apiFetch(form.action, { method: 'POST', body: new FormData(form) });
                const data = await response.json();
                if (data.result === true) {
                    window.location.href = data.redirect;
                } else if (data.message) {
                    showAlert('danger', data.message);
                }
            } catch (error) {
                console.error('Ripristino account non completato:', error);
            } finally {
                submitting = false;
            }
        });
    },

    verify: function() {
        
        const verifyManager = new VerifyManager();
        verifyManager.init();
    },
}

/* Se esiste una funzione per l'azione corrente, eseguila */
if (actions[action]) {
    actions[action]();
} else {
    console.error("Azione non definita:", action);
}
