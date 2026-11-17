/* Import delle utility risalendo di un livello */
import { urlbase, apiFetch, showAlert, smoothReplace, changeLanguageManager, getAjaxErrorContext } from '../backend.js';

export class ImportCsvManager {
    constructor(config = {}, hooks = {}) {

        if (ImportCsvManager.instance) {
            return ImportCsvManager.instance;
        }
        ImportCsvManager.instance = this;

        this.config = Object.assign({
            urlModal: urlbase + 'backend/import/showModal',
            urlProcess: urlbase + 'backend/import/processCsv',
            urlExecute: urlbase + 'backend/import/executeImport',
            modalContainerId: 'import-modal-container', 
            modalId: 'importModal', 
            linkId: '#import-entity', 
            removeId: '#btnCancelImport, #btnCancelImportTop',
            urlDelete: urlbase + 'backend/import/deleteFile',
            maxNetworkRetries: 2,
            retryDelayMs: 500,
        }, config);

        this.hooks = Object.assign({
            onModalBefore: null,
            onModalAfter: null,
            onImportBefore: null,
            onImportAfter: null,
            onDeleteBefore: null,
            onDeleteAfter: null,
            onError: null
        }, hooks);

        this.eventsBound = false;
        this.isSubmitting = false;
        this.passwordError = '';
        this.activeImportId = null;
        this.recoveryRequired = false;
        this.sectionAnimations = new WeakMap();
        
    }

    init() {
        this.bindEvents();
    }

    bindEvents() {

        if (this.eventsBound) return;
        this.eventsBound = true;

        /* Anima anche i riepiloghi tecnici senza perdere click su icone o traduzioni. */
        document.addEventListener('click', event => {
            const summary = event.target.closest('summary');
            const section = summary?.parentElement;
            const modal = document.getElementById(this.config.modalId);
            if (!(section instanceof HTMLDetailsElement) || !modal?.contains(section)) return;

            event.preventDefault();
            const pending = this.sectionAnimations.get(section);
            const opening = pending ? !pending.opening : !section.open;

            if (opening && section.hasAttribute('data-import-section')) {
                const group = section.closest('[data-import-accordion]');
                group?.querySelectorAll(':scope > details[data-import-section][open]').forEach(other => {
                    if (other !== section) this.animateSection(other, false);
                });
            }
            this.animateSection(section, opening);
        });

        /* 1. Apertura Modale */
        document.addEventListener('click', async e => {
            const btn = e.target.closest(this.config.linkId);
            if ( ! btn) return;

            e.preventDefault();
            const entity = btn.dataset.importEntity;
            
            await this.showModal(entity);
        });

        /* 2. Gestione Submit del Form di Importazione */
        document.addEventListener('submit', async e => {
            if (e.target.id === 'importForm') {
                e.preventDefault();
                await this.processImport(e.target);
            }
        });

        /* Eliminazione file temporaneo se l'admin clicca su annulla nel modale di importazione */
        document.addEventListener('click', async e => {
            const btn = e.target.closest(this.config.removeId);
            if ( ! btn) return;

            e.preventDefault();
            
            await this.deleteTempFile();
        });

    }

    /** Altezza temporanea e clipping senza barre; i click rapidi invertono la transizione. */
    animateSection(section, opening) {
        const previous = this.sectionAnimations.get(section);
        if (previous?.opening === opening || (!previous && section.open === opening)) return;

        const startHeight = section.getBoundingClientRect().height;
        if (previous) {
            previous.animation.onfinish = null;
            previous.animation.oncancel = null;
            previous.animation.cancel();
            this.sectionAnimations.delete(section);
        }
        section.style.removeProperty('height');
        section.style.removeProperty('overflow');
        section.classList.remove('transfer-closing');

        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches || typeof section.animate !== 'function') {
            section.open = opening;
            return;
        }

        /* Misura senza chiusure temporanee, che farebbero saltare lo scorrimento della finestra. */
        section.open = true;
        const openHeight = section.getBoundingClientRect().height;
        const summary = section.querySelector(':scope > summary');
        const frame = getComputedStyle(section);
        const heading = getComputedStyle(summary);
        const edges = ['borderTopWidth', 'borderBottomWidth', 'paddingTop', 'paddingBottom']
            .reduce((total, property) => total + (parseFloat(frame[property]) || 0), 0);
        const separator = section.classList.contains('transfer-disclosure') ? parseFloat(heading.borderBottomWidth) || 0 : 0;
        const closedHeight = summary.getBoundingClientRect().height + edges - separator;
        section.classList.toggle('transfer-closing', !opening);
        section.style.height = `${startHeight}px`;
        section.style.overflow = 'clip';

        const animation = section.animate({ height: [`${startHeight}px`, `${opening ? openHeight : closedHeight}px`] }, {
            duration: 280,
            easing: 'cubic-bezier(.2, 0, .2, 1)'
        });
        const pending = { animation, opening };
        this.sectionAnimations.set(section, pending);
        const finish = () => {
            if (this.sectionAnimations.get(section) !== pending) return;
            this.sectionAnimations.delete(section);
            section.open = opening;
            section.style.removeProperty('height');
            section.style.removeProperty('overflow');
            section.classList.remove('transfer-closing');
        };
        animation.onfinish = finish;
        animation.oncancel = finish;
    }

    async showModal(entity) {

        if (this.isSubmitting) return;
        this.activeImportId = null;
        this.recoveryRequired = false;
        this.isSubmitting = true;

        if (typeof this.hooks.onModalBefore === 'function') {
            const stop = this.hooks.onModalBefore(entity);
            if (stop === false) {
                this.isSubmitting = false;
                return;
            }
        }

        try {
            const formData = new FormData();
            formData.append('entity', entity);

            const response = await apiFetch(this.config.urlModal, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            /* Controllo fallimento logico generico */
            if (data.result === false) {
                if (data.message && typeof showAlert === 'function') showAlert('danger', data.message);
                return;
            }

            /* Caso successo */
            if (data.result === true) {
                
                const container = document.getElementById(this.config.modalContainerId);
                
                /* ALLINEATO: Utilizzo di data.output inviato dal Controller PHP */
                if (container && data.output) {
                    smoothReplace(container, data.output);

                    /* Avvia il modale Bootstrap (cerca il modale dentro il container appena popolato) */
                    const modalEl = document.getElementById(this.config.modalId);
                    if (modalEl) {
                        /* Aggancio al backdrop statico globale */
                        const backdropEl = document.getElementById('customBackdrop');
                        
                        /* Prevenzione memory leak: recupera o crea l'istanza */
                        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl, {
                            backdrop: false,
                            keyboard: false
                        });

                        if (backdropEl) {
                            modalEl.addEventListener('show.bs.modal', () => backdropEl.classList.add('active'));
                            modalEl.addEventListener('hidden.bs.modal', () => backdropEl.classList.remove('active'));
                        }

                        modalInstance.show();
                    }
                }

                if (typeof this.hooks.onModalAfter === 'function') {
                    this.hooks.onModalAfter(data);
                }

                this.updateModalControls(Boolean(this.activeImportId), data.hasProcessableData !== false);
            }

        } catch (error) {
            if (typeof this.hooks.onError === 'function') {
                this.hooks.onError(error);
            }
            console.error("Errore ImportCsvManager (showModal):", error);
        } finally {
            this.isSubmitting = false;
        }
    }

    /* Allinea le etichette e la conferma alla fase corrente del modale. */
    updateModalControls(hasPreview, hasProcessableData = true) {
        const modal = document.getElementById(this.config.modalId);
        if ( ! modal) return;

        modal.querySelectorAll('button[form="importForm"]').forEach(button => {
            button.hidden = ! hasProcessableData;
            button.disabled = this.recoveryRequired;
        });

        modal.querySelectorAll('[data-import-submit-label]').forEach(label => {
            const key = hasPreview ? 'confirmImport' : 'analyzeFile';
            label.setAttribute('data-i18n-text', JSON.stringify({ key: 'backend/components/import.buttons.' + key }));
            label.textContent = hasPreview ? label.dataset.confirm : label.dataset.analyze;
        });

        modal.querySelectorAll('[data-import-close-label]').forEach(label => {
            label.setAttribute('data-i18n-text', JSON.stringify({ key: 'backend/components/import.buttons.' + (hasPreview && hasProcessableData ? 'cancel' : 'close') }));
            label.textContent = hasPreview && hasProcessableData ? label.dataset.cancel : label.dataset.close;
        });
    }

    async deleteTempFile() {

        const importIdEl = document.querySelector('input[name="importId"]');
        const importId = importIdEl?.value || this.activeImportId;

        if ( ! importId) {
            const modalEl = document.getElementById(this.config.modalId);
            if (modalEl) {
                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (modalInstance) modalInstance.hide();
            }
            return;
        }

        /* In recovery lo staging e il backup sono artefatti diagnostici: chiudiamo la UI senza cancellarli. */
        if (this.recoveryRequired) {
            const modalEl = document.getElementById(this.config.modalId);
            if (modalEl) {
                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (modalInstance) modalInstance.hide();
            }
            return;
        }

        if (this.isSubmitting) return;
        this.isSubmitting = true;

        if (typeof this.hooks.onDeleteBefore === 'function') {
            const stop = this.hooks.onDeleteBefore(importId);
            if (stop === false) {
                this.isSubmitting = false;
                return;
            }
        }

        try {
            const formData = new FormData();
            formData.append('importId', importId);

            const response = await apiFetch(this.config.urlDelete, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            /* Controllo fallimento logico generico */
            if (data.result === false) {
                if (data.message && typeof showAlert === 'function') showAlert('danger', data.message);
                return;
            }

            /* Caso successo */
            if (data.result === true) {
                this.activeImportId = null;
                this.recoveryRequired = false;

                if (typeof this.hooks.onDeleteAfter === 'function') {
                    this.hooks.onDeleteAfter(data);
                }
            }

        } catch (error) {
            if (typeof this.hooks.onError === 'function') {
                this.hooks.onError(error);
            }
            console.error("Errore ImportCsvManager (deleteTempFile):", error);
        } finally {
            this.isSubmitting = false;
        }
    }

    async fetchWithRetry(url, options) {
        let attempt = 0;

        while (true) {
            try {
                return await apiFetch(url, options);
            } catch (error) {
                if (getAjaxErrorContext(error)?.kind !== 'network' || attempt >= this.config.maxNetworkRetries) throw error;

                attempt++;
                await new Promise(resolve => setTimeout(resolve, this.config.retryDelayMs * attempt));
            }
        }
    }

    restoreRetryForm() {
        if ( ! this.activeImportId || document.getElementById('importForm')) return;

        const modalBody = document.getElementById('import-content-area');
        if ( ! modalBody) return;

        const form = document.createElement('form');
        form.id = 'importForm';
        form.hidden = true;

        const importIdInput = document.createElement('input');
        importIdInput.type = 'hidden';
        importIdInput.name = 'importId';
        importIdInput.value = this.activeImportId;

        const stepInput = document.createElement('input');
        stepInput.type = 'hidden';
        stepInput.name = 'step';
        stepInput.value = 'confirm';

        form.append(importIdInput, stepInput);
        modalBody.appendChild(form);
    }

    async processImport(formElement, isContinuation = false) {

        if (this.isSubmitting && ! isContinuation) return;
        if (!isContinuation) this.passwordError = '';
        this.isSubmitting = true;

        if ( ! isContinuation && typeof this.hooks.onImportBefore === 'function') {
            const stop = this.hooks.onImportBefore(formElement);
            if (stop === false) {
                this.isSubmitting = false;
                return;
            }
        }

        try {
            if ( ! isContinuation) {
                const alerts = document.getElementById('import-alert-container');
                if (alerts) smoothReplace(alerts, '');
                document.querySelector('#alert-container [data-import-validation-alert]')?.remove();
            }

            const formData = isContinuation && this.activeImportId
                ? new FormData()
                : new FormData(formElement);

            if (isContinuation && this.activeImportId) {
                formData.append('importId', this.activeImportId);
                formData.append('step', 'confirm');
            }

            /* Determina l'URL in base allo step (upload file o conferma finale) */
            const isConfirmStep = formData.get('step') === 'confirm';

            if (isConfirmStep && ! this.activeImportId) {
                this.activeImportId = formData.get('importId');
            }

            const processUrl = isConfirmStep ? this.config.urlExecute : this.config.urlProcess;

            const response = await this.fetchWithRetry(processUrl, {
                method: 'POST',
                passwordInModal: true,
                body: formData
            });

            const data = await response.json();
            this.passwordError = data.errors?.currentPassword || '';
            formData.delete('currentPassword');
            const passwordInput = formElement?.querySelector('[name="currentPassword"]');
            if (passwordInput) passwordInput.value = '';

            if (data.result === false) {
                if (data.errors?.currentPassword) {
                    const errorEl = formElement?.querySelector('.error_currentPassword');
                    if (errorEl) errorEl.textContent = data.errors.currentPassword;
                    passwordInput?.focus();
                }
                if (data.recoveryRequired === true) {
                    this.recoveryRequired = true;
                    this.activeImportId = data.importId || this.activeImportId;

                    /* Nessun retry applicativo è sicuro in uno stato ambiguo/parzialmente committed. */
                    document.querySelectorAll('#' + this.config.modalId + ' button[form="importForm"]').forEach(button => {
                        button.disabled = true;
                    });
                }

                /* 1. Caso errori massivi: inietta l'HTML pre-compilato nel contenitore del modale */
                if (data.errorOutput) {
                    const modalAlertContainer = document.getElementById('import-alert-container');
                    
                    if (modalAlertContainer) {
                        smoothReplace(modalAlertContainer, data.errorOutput);

                        const modal = modalAlertContainer.closest('.modal');
                        if (modal) modal.scrollTop = 0;

                        const currentAlert = modalAlertContainer.querySelector('.alert');
                        if (currentAlert) {
                            currentAlert.addEventListener('closed.bs.alert', function () {
                                smoothReplace(modalAlertContainer, '');
                            });
                        }
                    }

                    if (data.message && typeof showAlert === 'function') {
                        showAlert('danger', data.message);
                        document.querySelector('#alert-container .alert')?.setAttribute('data-import-validation-alert', '');
                    }
                } 
                /* 2. Caso errore generico: utilizza la funzione globale standard */
                else if (data.message && typeof showAlert === 'function') {
                    showAlert('danger', data.message);
                }
                
                this.isSubmitting = false;
                return;
            }

            if (data.result === true) {

                /* Se era il primo step (upload), mostriamo l'anteprima */
                if ( ! isConfirmStep && data.output) {
                    this.activeImportId = data.importId || null;

                    const modalBody = document.getElementById('import-content-area');
                    if (modalBody) {
                        smoothReplace(modalBody, data.output);
                    }

                    /* Nasconde la conferma quando il CSV è già allineato al database. */
                    this.updateModalControls(true, data.hasProcessableData !== false);

                    const scrollBody = document.getElementById(this.config.modalId);
                    if (scrollBody) scrollBody.scrollTop = 0;
                    
                    this.isSubmitting = false; // Sblocca perché attende il click dell'utente per il secondo step
                } 
                
                /* Esecuzione progressiva a chunk. */
                else if (isConfirmStep) {

                    const modalBody = document.getElementById('import-content-area');
                    
                    if (data.isFinished === false && data.progressOutput && modalBody) {
                        if ( ! isContinuation) {
                            smoothReplace(modalBody, data.progressOutput);
                        } else {
                            const progressText = document.getElementById('import-progress-text');
                            if (progressText && data.progressMessage) {
                                progressText.textContent = data.progressMessage;
                                changeLanguageManager.bindMessage(progressText, data.progressMessage, true);
                            }
                        }
                    }


                    if (data.isFinished === false) {
                        await this.processImport(formElement, true);
                        return;
                    }
                    
                    if (data.message && typeof showAlert === 'function') showAlert(data.emailFailures > 0 ? 'warning' : 'success', data.message);

                    this.activeImportId = null;
                    this.recoveryRequired = false;
                    
                    const modalEl = document.getElementById(this.config.modalId);
                    if (modalEl) {
                        const modalInstance = bootstrap.Modal.getInstance(modalEl);
                        if (modalInstance) modalInstance.hide();
                    }

                    if (typeof this.hooks.onImportAfter === 'function') {
                        this.hooks.onImportAfter(data);
                    }
                    
                    this.isSubmitting = false;
                }
            }

        } catch (error) {
            if (typeof this.hooks.onError === 'function') {
                this.hooks.onError(error);
            }

            this.restoreRetryForm();

            if (typeof showAlert === 'function') {
                showAlert('danger', error?.message || 'Errore di comunicazione con il server.');
            }

            console.error("Errore di comunicazione con il server:", error);
            this.isSubmitting = false;
        } finally {
            const passwordInput = formElement?.querySelector('[name="currentPassword"]');
            if (passwordInput) passwordInput.value = '';
        }
    }
}
