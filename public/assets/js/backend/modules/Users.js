/* Import delle utility risalendo di un livello */
import { urlbase, apiFetch, showAlert, askConfirm, smoothReplace } from '../backend.js';

export class ResetPasswordManager {

    constructor(hooks = {}) {
        if (ResetPasswordManager.instance) {
            return ResetPasswordManager.instance;
        }

        ResetPasswordManager.instance = this;

        this.formSelector = '.resetUser';
        this.url = `${urlbase}backend/users/resetPassword`;

        this.hooks = Object.assign({
            onResetBefore: null,
            onResetAfter: null,
            onResetError: null
        }, hooks);

        this.eventsBound = false;
        this.isSubmitting = false;
        this.destroyed = false;

        this.eventsController = new AbortController();
    }

    init() {
        if (this.destroyed) return;

        this.bindEvents();
    }

    bindEvents() {
        if (this.eventsBound || this.destroyed) return;

        this.eventsBound = true;

        document.addEventListener('submit', async e => {
            const formEl = e.target;

            if (!(formEl instanceof HTMLFormElement)) return;
            if (!formEl.matches(this.formSelector)) return;

            e.preventDefault();

            if (this.isSubmitting || this.destroyed) return;

            this.isSubmitting = true;

            try {
                const message = formEl.dataset.message;

                if (message) {
                    const ok = await askConfirm(message);

                    if (!ok || this.destroyed) return;
                }

                await this.resetPassword(new FormData(formEl), formEl, true);

            } catch (error) {
                await this.handleError(error);
            } finally {
                this.isSubmitting = false;
            }
        }, {
            signal: this.eventsController.signal
        });
    }

    async resetPassword(formData, formEl, alreadySubmitting = false) {
        if (this.destroyed || (this.isSubmitting && !alreadySubmitting)) return;

        if (!alreadySubmitting) {
            this.isSubmitting = true;
        }

        try {
            if (typeof this.hooks.onResetBefore === 'function') {
                const stop = await this.hooks.onResetBefore(formData);

                if (stop === false) return;
            }

            if (this.destroyed) return;

            const response = await apiFetch(this.url, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (this.destroyed) return;

            if (data.result === false) {
                if (data.message && typeof showAlert === 'function') {
                    showAlert('danger', data.message);
                }

                return;
            }

            if (data.result === true) {
                if (typeof this.hooks.onResetAfter === 'function') {
                    await this.hooks.onResetAfter(data);
                }

                if (this.destroyed) return;

                if (data.message && typeof showAlert === 'function') {
                    showAlert('success', data.message);
                }
            }

        } catch (error) {
            await this.handleError(error);
        } finally {
            if (!alreadySubmitting) {
                this.isSubmitting = false;
            }
        }
    }

    async handleError(error) {
        if (typeof this.hooks.onResetError === 'function') {
            try {
                await this.hooks.onResetError(error);
            } catch (hookError) {
                console.error(
                    'Errore hook ResetPasswordManager:',
                    hookError
                );
            }
        }

        console.error('Errore ResetPasswordManager:', error);
    }

    destroy() {
        if (this.destroyed) return;

        this.destroyed = true;
        this.eventsController.abort();
        this.eventsBound = false;

        if (ResetPasswordManager.instance === this) {
            ResetPasswordManager.instance = null;
        }
    }
}



export class GetTokensManager {

    constructor(hooks = {}) {
        if (GetTokensManager.instance) {
            return GetTokensManager.instance;
        }

        GetTokensManager.instance = this;

        this.url = urlbase + 'backend/users/getTokens';
        this.formSelector = '#getTokens';

        this.hooks = Object.assign({
            onTokensBefore: null,
            onTokensAfter: null,
            onTokensError: null
        }, hooks);

        this.eventsBound = false;
        this.isSubmitting = false;
        this.destroyed = false;

        this.eventsController = new AbortController();
    }

    init() {
        if (this.destroyed) return;

        this.bindEvents();
    }

    bindEvents() {
        if (this.eventsBound || this.destroyed) return;

        this.eventsBound = true;

        document.addEventListener('submit', async e => {
            const formEl = e.target;

            if (!(formEl instanceof HTMLFormElement)) return;
            if (!formEl.matches(this.formSelector)) return;

            e.preventDefault();

            if (this.isSubmitting || this.destroyed) return;

            await this.getTokens(new FormData(formEl), e.submitter);
        }, {
            signal: this.eventsController.signal
        });
    }

    async getTokens(formData, loader) {
        if (this.destroyed || this.isSubmitting) return;

        this.isSubmitting = true;

        try {
            if (typeof this.hooks.onTokensBefore === 'function') {
                const stop = await this.hooks.onTokensBefore(formData);

                if (stop === false) return;
            }

            if (this.destroyed) return;

            const response = await apiFetch(this.url, {
                method: 'POST',
                body: formData,
                loader
            });

            const data = await response.json();

            if (this.destroyed) return;

            if (data.result === false) {
                if (data.message && typeof showAlert === 'function') {
                    showAlert('danger', data.message);
                }

                return;
            }

            if (data.result === true) {
                const tokensEl = document.getElementById('tokens');

                if (tokensEl && data.output) {
                    smoothReplace(tokensEl, data.output);
                }

                if (typeof this.hooks.onTokensAfter === 'function') {
                    await this.hooks.onTokensAfter(data);
                }
            }

        } catch (error) {
            if (typeof this.hooks.onTokensError === 'function') {
                try {
                    await this.hooks.onTokensError(error);
                } catch (hookError) {
                    console.error(
                        'Errore hook GetTokensManager:',
                        hookError
                    );
                }
            }

            console.error('Errore GetTokensManager:', error);
        } finally {
            this.isSubmitting = false;
        }
    }

    destroy() {
        if (this.destroyed) return;

        this.destroyed = true;
        this.eventsController.abort();
        this.eventsBound = false;

        if (GetTokensManager.instance === this) {
            GetTokensManager.instance = null;
        }
    }
}



export class DeleteTokenManager {

    constructor(hooks = {}) {
        if (DeleteTokenManager.instance) {
            return DeleteTokenManager.instance;
        }

        DeleteTokenManager.instance = this;

        this.url = urlbase + 'backend/users/deleteToken';

        this.hooks = Object.assign({
            onDeleteTokenBefore: null,
            onDeleteTokenAfter: null,
            onDeleteTokenError: null
        }, hooks);

        this.eventsBound = false;
        this.isSubmitting = false;
        this.destroyed = false;

        this.eventsController = new AbortController();
    }

    init() {
        if (this.destroyed) return;

        this.bindEvents();
    }

    bindEvents() {
        if (this.eventsBound || this.destroyed) return;

        this.eventsBound = true;

        document.addEventListener('submit', async e => {
            const formEl = e.target;

            if (!(formEl instanceof HTMLFormElement)) return;
            if (!formEl.matches('.deleteToken')) return;

            e.preventDefault();

            if (this.isSubmitting || this.destroyed) return;

            this.isSubmitting = true;

            try {
                const message = formEl.dataset.message;
                const formData = new FormData(formEl);
                const ok = await askConfirm(message, { password: true, formData });

                if (!ok || this.destroyed) return;

                await this.deleteToken(formData, true);

            } catch (error) {
                await this.handleError(error);
            } finally {
                this.isSubmitting = false;
            }
        }, {
            signal: this.eventsController.signal
        });
    }

    async deleteToken(formData, alreadySubmitting = false) {
        if (this.destroyed || (this.isSubmitting && !alreadySubmitting)) return;

        if (!alreadySubmitting) {
            this.isSubmitting = true;
        }

        try {
            if (typeof this.hooks.onDeleteTokenBefore === 'function') {
                const stop = await this.hooks.onDeleteTokenBefore(formData);

                if (stop === false) return;
            }

            if (this.destroyed) return;

            const response = await apiFetch(this.url, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (this.destroyed) return;

            if (data.result === false) {
                if (data.message && typeof showAlert === 'function') {
                    showAlert('danger', data.message);
                }

                return;
            }

            if (data.result === true) {
                const tokensEl = document.getElementById('tokens');

                if (tokensEl && data.tokensView) {
                    smoothReplace(tokensEl, data.tokensView);
                }

                if (typeof this.hooks.onDeleteTokenAfter === 'function') {
                    await this.hooks.onDeleteTokenAfter(data);
                }

                if (this.destroyed) return;

                if (data.message && typeof showAlert === 'function') {
                    showAlert('success', data.message);
                }
            }

        } catch (error) {
            await this.handleError(error);
        } finally {
            if (!alreadySubmitting) {
                this.isSubmitting = false;
            }
        }
    }

    async handleError(error) {
        if (typeof this.hooks.onDeleteTokenError === 'function') {
            try {
                await this.hooks.onDeleteTokenError(error);
            } catch (hookError) {
                console.error(
                    'Errore hook DeleteTokenManager:',
                    hookError
                );
            }
        }

        console.error('Errore DeleteTokenManager:', error);
    }

    destroy() {
        if (this.destroyed) return;

        this.destroyed = true;
        this.eventsController.abort();
        this.eventsBound = false;

        if (DeleteTokenManager.instance === this) {
            DeleteTokenManager.instance = null;
        }
    }
}

/** Ripristina una richiesta Removed usando il dialogo password comune del backend. */
export class RestoreRemovedManager {
    /**
     * @param {import('./Crud.js').ListManager} listManager Lista Users da riallineare dopo il ripristino.
     */
    constructor(listManager) {
        this.listManager = listManager;
        this.eventsBound = false;
        this.isSubmitting = false;
    }

    /** Collega i form di ripristino anche dopo gli aggiornamenti asincroni della lista. */
    init() {
        if (this.eventsBound) return;
        this.eventsBound = true;
        document.addEventListener('submit', event => {
            const form = event.target;
            if (!(form instanceof HTMLFormElement) || !form.matches('.restoreRemovedUser')) return;
            event.preventDefault();
            this.restore(form);
        });
    }

    /**
     * Conferma la password dello staff e ricarica la lista dopo il controllo server della scadenza.
     * @param {HTMLFormElement} form Form contenente l'UUID del cliente da ripristinare.
     * @returns {Promise<void>}
     */
    async restore(form) {
        if (this.isSubmitting) return;
        this.isSubmitting = true;
        const formData = new FormData(form);

        try {
            const response = await apiFetch(urlbase + 'backend/users/restoreDelete', {
                method: 'POST',
                body: formData,
                passwordConfirmation: { message: form.dataset.message, force: true }
            });
            const data = await response.json();
            if (data.cancelled) return;
            if (data.message) showAlert(data.result === true ? 'success' : 'danger', data.message);
            if (data.result !== true) return;

            /* Nel filtro Removed l'ultimo record ripristinato puo svuotare una pagina successiva. */
            const list = this.listManager;
            const lastItem = Number(list.root.querySelector('.lastItemPage')?.dataset.lastitempage);
            const currentPage = Number(list.state.page);
            if (list.state.trash_filter === 'trashed' && lastItem === 1 && currentPage > 1) {
                list.updateState('page', currentPage - 1);
            }
            await list.showAll();
        } catch (error) {
            console.error('Ripristino utente non completato:', error);
        } finally {
            formData.delete('currentPassword');
            this.isSubmitting = false;
        }
    }
}

