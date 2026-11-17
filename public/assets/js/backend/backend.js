/* HTML references */
export const controller = document.getElementById('controller').dataset.controller;
export const action = document.getElementById('action').dataset.action;
export const urlbase = document.getElementById('hidden-urlbase').dataset.urlbase;

export const langDefinitions = document.getElementById('lang-definitions');

import { ExportPdfManager } from './modules/ExportPdf.js';
import { PasswordVisibility } from './modules/PasswordVisibility.js';
import { ChangeThemeManager } from './modules/ChangeThemeManager.js';
import { ChangeLanguageManager } from './modules/ChangeLanguageManager.js';
import { ChangeFontManager } from './modules/ChangeFontManager.js';
import { initTomSelects as enhanceTomSelects, observeTomSelects } from '../components/TomSelect.js';
import { startPanelLoader } from '../components/PanelLoader.js';

export const changeThemeManager = new ChangeThemeManager();
export const changeLanguageManager = new ChangeLanguageManager();
export const changeFontManager = new ChangeFontManager();

/* Mantiene accessibile il dropdown senza disattivare il focus trap del dialogo. */
document.addEventListener('focusin', event => {
    if (event.target.closest('#interface-preferences, #navbar-top-view .navbar-toggler') && document.querySelector('.modal.show, .offcanvas.show')) {
        event.stopImmediatePropagation();
    }
}, true);

/* Gestione modalità schermo intero */
const fullscreenToggle = document.getElementById('fullscreen-toggle');

if (fullscreenToggle) {
    const fullscreenIcon = fullscreenToggle.querySelector('i');
    const fullscreenText = fullscreenToggle.querySelector('.fullscreen-toggle-text');
    let fullscreenTransitionPending = false;

    const updateFullscreenToggle = () => {
        const isFullscreen = document.fullscreenElement !== null;
        const currentText = isFullscreen ? fullscreenToggle.dataset.reduceText : fullscreenToggle.dataset.expandText;

        fullscreenIcon?.classList.toggle('fa-expand', ! isFullscreen);
        fullscreenIcon?.classList.toggle('fa-compress', isFullscreen);

        if (fullscreenText) fullscreenText.textContent = currentText;

        fullscreenToggle.setAttribute('aria-label', currentText);
        fullscreenToggle.setAttribute('aria-pressed', String(isFullscreen));
        fullscreenToggle.setAttribute('title', currentText);
    };

    if (document.fullscreenEnabled) {
        fullscreenToggle.addEventListener('click', async (e) => {
            e.preventDefault();

            if (fullscreenTransitionPending) return;

            fullscreenTransitionPending = true;

            try {
                if (document.fullscreenElement) {
                    await document.exitFullscreen();
                } else {
                    await document.documentElement.requestFullscreen();
                }
            } catch (error) {
                showAlert('danger', fullscreenToggle.dataset.errorText);
            } finally {
                fullscreenTransitionPending = false;
                updateFullscreenToggle();
            }
        });

        document.addEventListener('fullscreenchange', updateFullscreenToggle);
        document.addEventListener('interface:language', updateFullscreenToggle);
        updateFullscreenToggle();
    } else {
        fullscreenToggle.hidden = true;
    }
}

/* Scrollup */
window.addEventListener('scroll', function() {
    const scrollupElements = document.querySelectorAll('.scrollup');
    scrollupElements.forEach(el => {
        if (window.scrollY > 120) {
            el.classList.remove('fade-out');
            el.classList.add('fade-in');
        } else {
            el.classList.remove('fade-in');
            el.classList.add('fade-out');
        }
    });
});

document.addEventListener('click', function(e) {
    const el = e.target.closest('.scrollup');
    if ( ! el) return;
    e.preventDefault();
    window.scrollTo({ top: 0, behavior: 'smooth' });
});

/* Avvio del gestore PDF al caricamento della pagina */
new ExportPdfManager();

/* Avvio del gestore Mostra/Nascondi password al caricamento della pagina */
new PasswordVisibility();

/* Listener globale per il pulsante di stampa */
document.addEventListener('click', (e) => {
    const printBtn = e.target.closest('#show-print-button');
    
    if (printBtn) {
        e.preventDefault();
        window.print();
    }
});

/** Aggiorna solo i badge dei menu presenti nella pagina e autorizzati dal server. */
function updateMenuCounts(countsByEntity) {
    if ( ! countsByEntity || typeof countsByEntity !== 'object') return;

    document.querySelectorAll('[data-menu-counts]').forEach(container => {
        const entity = container.dataset.menuCounts;
        const counts = countsByEntity[entity];
        const types = entity === 'messages' ? ['unread', 'read', 'archived'] : ['active', 'trashed', 'suspended'];

        if ( ! counts || ! types.every(type => Number.isSafeInteger(counts[type]) && counts[type] >= 0)) return;

        container.querySelectorAll('[data-menu-count]').forEach(badge => {
            const type = badge.dataset.menuCount;
            if ( ! types.includes(type)) return;

            badge.textContent = String(counts[type]);
            const label = badge.dataset.label + ': ' + counts[type];
            badge.title = label;
            badge.setAttribute('aria-label', label);
            ['title', 'aria-label'].forEach(attribute => {
                const binding = badge.getAttribute('data-i18n-' + attribute);
                if (binding) badge.setAttribute('data-i18n-' + attribute, JSON.stringify({ ...JSON.parse(binding), suffix: ': ' + counts[type] }));
            });
        });
    });
}

/* --- Gestione globale Loader e Controlli --- */
export function toggleLoader(show) {
    const loader = document.getElementById('show-loader');

    if (show) {
        if (loader) {
            loader.style.display = 'block';
            loader.innerHTML = `<img src="${urlbase}assets/img/squares_wave.gif" alt="">`;
        }

        disableAllControls();
    } else {
        if (loader) {
            loader.style.display = 'none';
            loader.innerHTML = '';
        }

        enableAllControls();
    }
}


/* Coda di esecuzione per evitare Race Conditions sulle chiamate simultanee */
let fetchQueue = Promise.resolve();
let sessionRedirecting = false;
const ajaxErrorContexts = new WeakMap();

/** Mantiene disponibili i dettagli senza cambiare il tipo dell'errore HTTP originale. */
export function getAjaxErrorContext(error) {
    return error !== null && (typeof error === 'object' || typeof error === 'function') ? ajaxErrorContexts.get(error) : undefined;
}

function ajaxMessage(key, fallback) {
    return document.getElementById('js-i18n-errors')?.dataset[key]?.trim() || fallback;
}

function reportAjaxError(error, context) {
    const original = error !== null && (typeof error === 'object' || typeof error === 'function')
        ? error : new Error(String(error), { cause: error });
    const details = { ...context, handled: true };
    ajaxErrorContexts.set(original, details);
    handleAjaxError(details, details.message, original);
    return original;
}

/* --- Chiamata fetch generica ottimizzata --- */
export function apiFetch(input, init = {}) {
    closePasswordInfo();
    init = init ?? {};
    if (init.passwordConfirmation) return confirmAndFetch(input, init);
    const request = fetchQueue.then(() => executeFetch(input, init));
    const currentTask = init.loader instanceof HTMLButtonElement
        ? request.finally(startPanelLoader(init.loader))
        : request;
    fetchQueue = currentTask.catch(() => {});
    return currentTask.then(response => {
        // La richiesta e gia conclusa: la verifica password non entra in attesa della propria coda.
        if (!init.passwordInModal && !init.passwordRetry && init.body instanceof FormData
            && response.headers.get('X-Current-Password-Required') === '1') {
            return confirmAndFetch(input, { ...init, passwordConfirmation: { force: true }, passwordRetry: true });
        }
        return response;
    });
}

async function confirmAndFetch(input, init) {
    const options = { ...init };
    const confirmation = options.passwordConfirmation === true ? {} : options.passwordConfirmation;
    delete options.passwordConfirmation;
    const formData = options.body;
    if (!(formData instanceof FormData)) throw new TypeError('Password confirmation requires FormData.');
    if (!formData.get('currentPassword') && (confirmation.message || passwordConfirmationRequired(confirmation.force === true))) {
        const confirmed = await askConfirm(confirmation.message || '', {
            password: true, forcePassword: confirmation.force === true, formData
        });
        if (!confirmed) return Response.json({ result: false, cancelled: true });
    }
    try {
        return await apiFetch(input, options);
    } finally {
        formData.delete('currentPassword');
    }
}

async function executeFetch(input, init = {}) {
    let response = null;
    let method = 'GET';
    let requestUrl = '';
    let phase = 'request';
    let signal;
    let loaderStarted = false;

    try {
        if (sessionRedirecting) {
            const expired = new Error('Session redirect in progress.');
            expired.name = 'SessionExpiredError';
            throw expired;
        }

        const { loader, ...options } = init;
        const reloadOnForbidden = options.reloadOnForbidden !== false;
        delete options.reloadOnForbidden;
        delete options.passwordInModal;
        delete options.passwordRetry;
        const isRequest = input instanceof Request;
        method = String(options.method || (isRequest ? input.method : 'GET')).toUpperCase();
        const address = new URL(isRequest ? input.url : String(input), document.baseURI);
        requestUrl = address.origin + address.pathname;
        signal = Object.prototype.hasOwnProperty.call(options, 'signal') ? options.signal : (isRequest ? input.signal : undefined);
        if (signal?.aborted) throw signal.reason || new DOMException('Request aborted.', 'AbortError');

        if (!(loader instanceof HTMLButtonElement)) {
            loaderStarted = true;
            toggleLoader(true);
        }
        const headers = new Headers(isRequest ? input.headers : undefined);
        new Headers(options.headers).forEach((value, key) => headers.set(key, value));
        if ( ! headers.has('X-Requested-With')) headers.set('X-Requested-With', 'XMLHttpRequest');
        const csrfMeta = document.getElementById('csrf-meta');
        if (['POST', 'PUT', 'DELETE', 'PATCH'].includes(method) && csrfMeta && ! headers.has('X-CSRF-TOKEN')) {
            headers.set('X-CSRF-TOKEN', csrfMeta.getAttribute('content'));
        }
        options.headers = headers;

        phase = 'network';
        response = await fetch(input, options);
        phase = 'response';
        if (response.ok && (method === 'HEAD' || [204, 205].includes(response.status))) return response;

        let data;
        try {
            data = await response.clone().json();
        } catch (error) {
            if (response.ok || signal?.aborted || error?.name === 'AbortError') throw error;
            data = {};
        }
        if (response.ok && (data === null || typeof data !== 'object' || Array.isArray(data))) {
            throw new SyntaxError('Expected an application JSON object.');
        }

        /* Il token va aggiornato anche se l'aggiornamento dell'interfaccia fallisce. */
        if (typeof data?.passwordConfirmationRequired === 'boolean') {
            const definitions = document.getElementById('lang-definitions');
            if (definitions) definitions.dataset.passwordConfirmation = data.passwordConfirmationRequired ? '1' : '0';
        }
        const newHash = data?.csrfHash || data?.csrf;
        if (typeof newHash === 'string' && newHash && csrfMeta) csrfMeta.setAttribute('content', newHash);

        if (data?.result === 'no_current_user_logged') {
            phase = 'session';
            sessionRedirecting = true;
            window.location.href = `${urlbase}backend/auth`;
            const expired = new Error('Session expired.');
            expired.name = 'SessionExpiredError';
            throw expired;
        }

        if ( ! response.ok) {
            phase = 'http';
            const securityException = 'CodeIgniter\\Security\\Exceptions\\SecurityException';
            const csrfFailure = response.status === 403 && (data?.title === securityException || data?.type === securityException);
            const messages = {
                403: ajaxMessage('err403', 'Accesso negato (403).'),
                404: ajaxMessage('err404', 'Risorsa non trovata (404).'),
                500: ajaxMessage('err500', 'Errore interno (500).'),
                504: ajaxMessage('err504', 'Timeout del server (504).')
            };
            const message = csrfFailure ? ajaxMessage('errCsrf', 'Verifica di sicurezza non riuscita. Ricarica la pagina prima di riprovare.')
                : messages[response.status] || `${ajaxMessage('errHttp', 'Il server non ha potuto completare la richiesta.')} (${response.status})`;
            reportAjaxError(response, { kind: 'http', phase, status: response.status, url: requestUrl, method, message, data });
            if (csrfFailure && reloadOnForbidden) setTimeout(() => window.location.reload(), 2000);
            throw response;
        }

        /* Un errore nei badge o nelle traduzioni non trasforma un salvataggio riuscito in un fallimento. */
        phase = 'interface';
        try {
            changeLanguageManager.rememberMessages(data.translationBindings);
            if (data.result === true) updateMenuCounts(data.menuCounts);
        } catch (error) {
            reportAjaxError(error, { kind: 'interface', phase, status: response.status, url: requestUrl, method,
                message: ajaxMessage('errClient', 'Errore nell’interfaccia. Controlla lo stato dell’operazione prima di riprovare.') });
        }
        return response;

    } catch (error) {
        if (getAjaxErrorContext(error)) throw error;
        const kind = signal?.reason?.name === 'TimeoutError' || error?.name === 'TimeoutError' ? 'timeout'
            : signal?.aborted || error?.name === 'AbortError' ? 'abort'
            : error?.name === 'SessionExpiredError' ? 'session'
            : phase === 'network' ? 'network' : phase === 'response' ? 'response' : phase === 'request' ? 'request' : 'interface';
        if (phase === 'session' && kind !== 'session') sessionRedirecting = false;
        const messages = {
            network: ajaxMessage('errNetwork', 'Impossibile comunicare con il server.'),
            timeout: ajaxMessage('err504', 'Il server non ha risposto in tempo. Controlla lo stato dell’operazione prima di riprovare.'),
            response: ajaxMessage('errResponse', 'Il server ha restituito una risposta non valida. L’operazione potrebbe essere già stata eseguita.'),
            request: ajaxMessage('errRequest', 'Non è stato possibile avviare la richiesta.'),
            interface: ajaxMessage('errClient', 'Errore nell’interfaccia. Controlla lo stato dell’operazione prima di riprovare.')
        };
        throw reportAjaxError(error, { kind, phase, status: response?.status || 0, url: requestUrl, method,
            message: messages[kind] || '', notify: ! ['abort', 'session'].includes(kind) });
    } finally {
        if (loaderStarted) {
            try {
                toggleLoader(false);
            } catch (error) {
                reportAjaxError(error, { kind: 'interface', phase: 'cleanup', status: response?.status || 0, url: requestUrl, method,
                    message: ajaxMessage('errClient', 'Errore nell’interfaccia. Controlla lo stato dell’operazione prima di riprovare.') });
            }
        }
    }
}

/* Funzione per disabilitare i links durante la chiamata ajax */
function disableLinks() {
    document.querySelectorAll('a').forEach(link => {
        link.dataset.originalHref = link.getAttribute('href') || '';
        link.removeAttribute('href');
        link.setAttribute('aria-disabled', 'true');
        link.setAttribute('tabindex', '-1');
        link.classList.add('disabled-link');
    });
}

/* Funzione per abilitare i links durante la chiamata ajax */
function enableLinks() {
    document.querySelectorAll('a').forEach(link => {
        const href = link.dataset.originalHref;
        if (href) link.setAttribute('href', href);
        delete link.dataset.originalHref;
        link.removeAttribute('aria-disabled');
        link.removeAttribute('tabindex');
        link.classList.remove('disabled-link');
    });
}

/* Funzione per disabilitare tutti gli elementi di un form durante lka chiamata ajax */
function disableAllControls() {
    const controls = document.querySelectorAll('input, select, textarea, button');

    controls.forEach(el => {
        if (el.classList.contains('no-general-disabled')) return;

        // Conserva lo stato originale prima di disabilitare.
        el.dataset.wasDisabled = el.disabled ? 'true' : 'false';

        el.disabled = true;
    });

    disableLinks();
}

/* Funzione per abilitare tutti gli elementi di un form durante la chiamata ajax */
function enableAllControls() {
    const controls = document.querySelectorAll('input, select, textarea, button');

    controls.forEach(el => {
        if (el.classList.contains('no-general-disabled')) return;

        // Ripristina lo stato precedente alla richiesta AJAX.
        if (el.dataset.wasDisabled !== undefined) {
            el.disabled = el.dataset.wasDisabled === 'true';
            delete el.dataset.wasDisabled;
        }
    });

    enableLinks();
}


let validationErrorToken = 0;

/* Funzione per gestire gli errori di validazione */
export function handleValidationErrors(errors) {
    const token = String(++validationErrorToken);

    /* Scollega gli errori dal precedente alert senza modificarne il contenuto. */
    document.querySelectorAll('[data-validation-alert-token]').forEach(el => {
        delete el.dataset.validationAlertToken;
    });

    Object.entries(errors).forEach(([key, value]) => {
        document.querySelectorAll('.error_' + key).forEach(el => {
            el.textContent = value;
            changeLanguageManager.bindMessage(el, value, true);
            el.dataset.validationAlertToken = token;
            // el.classList.add('error-show'); // fade-in
        });
    });
}

/* Funzione per gestire gli errori per le immagini adattata a CI4 */
export function handleValidationImages(errors) {
    Object.entries(errors).forEach(([key, message]) => {
        
        /* Intercettiamo le chiavi che iniziano con "images." */
        if (key.startsWith('images.')) {

            /* Estraiamo l'ID JS dopo il punto (es. "mrcesmbk5w52f") */
            const id = key.split('.')[1];
            
            /* Cerchiamo direttamente l'ID del div di errore che è fuori dal preview-item */
            const errorBox = document.getElementById(`error-img-${id}`);
            
            if (errorBox) {
                errorBox.textContent = Array.isArray(message) ? message.join(', ') : String(message);
                changeLanguageManager.bindMessage(errorBox, errorBox.textContent, true);
            }
        }
    });
}

/* Presentazione centralizzata degli errori AJAX; conserva anche la firma a tre argomenti. */
export function handleAjaxError(jqXHR, textStatus, errorThrown) {
    const details = jqXHR || {};
    const message = typeof textStatus === 'string' && textStatus.trim() ? textStatus
        : ajaxMessage('errHttp', 'Il server non ha potuto completare la richiesta.');
    const report = {
        kind: details.kind || 'http',
        phase: details.phase || 'unknown',
        url: details.url || '',
        method: details.method || '',
        httpStatus: details.status || 0,
        userMessage: details.notify === false ? '' : message,
        nativeError: errorThrown || null
    };
    if (details.notify === false) {
        console.debug('[Essential AJAX]', report);
        return;
    }
    console.error('[Essential AJAX]', report);
    try {
        showAlert('danger', message);
    } catch (presentationError) {
        console.error('[Essential AJAX] - Alert non visualizzato:', presentationError);
    }
}

export function showAlert(type, message, customIcon = '')
{
    let alertClass;
    let defaultIcon;

    /* Determina classe e icona di default in base al tipo */
    switch (type) {
        case 'success':
            alertClass = 'app-alert-success fw-bold';
            defaultIcon = '<i class="fa-solid fa-circle-check"></i>';
            break;
        case 'danger':
            alertClass = 'app-alert-danger fw-bold';
            defaultIcon = '<i class="fa-solid fa-triangle-exclamation"></i>';
            break;
        case 'info':
            alertClass = 'app-alert-info fw-bold';
            defaultIcon = '<i class="fa-solid fa-circle-info"></i>';
            break;
        default:
            alertClass = 'app-alert fw-bold';
            defaultIcon = '';
    }

    const langDefinitions = document.getElementById('lang-definitions');

    /* Usa l'icona custom se passata, altrimenti quella di default */
    const finalIcon = customIcon || defaultIcon;
    const iconHTML = finalIcon ? `${finalIcon} ` : '';

    const alertHTML = `
        <div class="alert ${alertClass} alert-dismissible fade show border-0 d-flex align-items-center p-3 mb-0" role="alert">
            <div class="w-100 text-center p-0 ms-4">
                ${iconHTML}<span class="alert-message">${message}</span>
            </div>
            <button type="button" class="badge app-bg-danger p-2 border-0 rounded-1 ms-auto" data-bs-dismiss="alert" style="cursor: pointer;">
                <i class="fa-solid fa-xmark"></i> ${ langDefinitions.dataset.remove }
            </button>
        </div>`;

    const alertContainer = document.getElementById('alert-container');
    if ( ! alertContainer) return;

    /* Memorizza esclusivamente gli errori di validazione associati a questo alert. */
    const validationErrors = Array.from(
        document.querySelectorAll('[data-validation-alert-token]')
    ).map(element => ({
        element,
        token: element.dataset.validationAlertToken
    }));

    /* Cerca l'alert di sessione PHP e lo rimuove dal DOM se presente */
    const sessionAlert = document.querySelector('.alert-session');
    if (sessionAlert) {
        sessionAlert.remove();
    }

    /* Svuota il contenitore rimuovendo l'alert precedente prima di inserire il nuovo */
    alertContainer.innerHTML = '';

    const alertElement = document.createElement('div');
    alertElement.innerHTML = alertHTML.trim();
    const currentAlert = alertElement.firstChild;
    
    changeLanguageManager.bindMessage(currentAlert.querySelector('.alert-message'), message);
    alertContainer.appendChild(currentAlert);

    currentAlert.addEventListener('closed.bs.alert', function () {
        validationErrors.forEach(({ element, token }) => {
            /* Non cancella un eventuale errore più recente sullo stesso campo. */
            if (element.dataset.validationAlertToken !== token) return;

            element.textContent = '\u00A0';
            delete element.dataset.validationAlertToken;
        });

        this.remove();
    }, { once: true });
}

let confirmModalInitialized = false;

let confirmationQueue = null;

export function askConfirm(message, options = {}) {
    const show = () => showConfirmation(message, options);
    const task = confirmationQueue ? confirmationQueue.then(show, show) : show();
    confirmationQueue = task;
    const release = () => { if (confirmationQueue === task) confirmationQueue = null; };
    task.then(release, release);
    return task;
}

async function showConfirmation(message, options) {
    return new Promise(resolve => {
        
        const modalEl = document.getElementById('globalConfirmModal');
        const needsPassword = options.password === true && passwordConfirmationRequired(options.forcePassword === true);
        message = message || document.getElementById('lang-definitions')?.dataset.sensitiveConfirm || '';
        if (needsPassword && options.formData instanceof FormData) options.formData.delete('currentPassword');
        if (needsPassword && (!modalEl || !(options.formData instanceof FormData))) return resolve(false);

        /* 1. Fallback di sicurezza: window.confirm non supporta HTML, quindi spogliamo i tag */
        if ( ! modalEl) {
            const strippedMessage = message.replace(/<[^>]*>?/gm, '');
            return resolve(window.confirm(strippedMessage));
        }

        /* 2. Inizializzazione eventi backdrop una sola volta */
        if ( ! confirmModalInitialized) {
            const backdropEl = document.getElementById('customBackdrop');
            if (backdropEl) {
                modalEl.addEventListener('show.bs.modal', () => backdropEl.classList.add('active'));
                modalEl.addEventListener('hidden.bs.modal', () => backdropEl.classList.remove('active'));
            }
            confirmModalInitialized = true;
        }

        /* Recupero o creazione istanza Bootstrap */
        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl, {
            backdrop: false, 
            keyboard: false  
        });

        /* --- INIZIO SCUDO ENTERPRISE (Anti-XSS) --- */
        const sanitizeHTML = (str) => {
            if (!str) return '';
            
            /* Escapa le parentesi angolari disinnescando qualsiasi tag */
            let safe = str.toString().replace(/</g, "&lt;").replace(/>/g, "&gt;");
            
            /* Ripristina esplicitamente solo i tag autorizzati.
               Per lo <span> consentiamo opzionalmente l'attributo class 
               limitato a caratteri sicuri (alfanumerici, trattini, underscore, spazi).
            */
            safe = safe.replace(/&lt;(br\s*\/?|b|\/b|p|\/p|span(?:\s+class="[a-zA-Z0-9\-_ ]+")?|\/span)&gt;/gi, '<$1>');
            
            return safe;
        };
        /* --- FINE SCUDO ENTERPRISE --- */

        /* 3. Aggiornamento esclusivo del messaggio iniettando l'HTML sanificato */
        const body = modalEl.querySelector('.modal-body');
        body.removeAttribute('data-i18n-text');
        body.innerHTML = sanitizeHTML(message);
        let passwordForm = null;
        if (needsPassword) {
            const template = document.getElementById('global-confirm-password-template');
            if (!template) return resolve(false);
            const messageEl = document.createElement('div');
            messageEl.innerHTML = body.innerHTML;
            body.replaceChildren(messageEl);
            changeLanguageManager.bindMessage(messageEl, message);
            passwordForm = document.createElement('form');
            passwordForm.noValidate = true;
            passwordForm.append(template.content.cloneNode(true));
            body.append(passwordForm);
        } else {
            changeLanguageManager.bindMessage(body, message);
        }

        /* 4. Prevenzione Multi-Esecuzione: controlli di esistenza prima della clonazione */
        let oldOkBtn = modalEl.querySelector('.btn-ok');
        let oldCancelBtn = modalEl.querySelector('.btn-cancel');

        if ( ! oldOkBtn || ! oldCancelBtn) {
            console.error("askConfirm: Pulsanti '.btn-ok' o '.btn-cancel' mancanti nel DOM.");
            return resolve(false); 
        }

        const okBtn = oldOkBtn.cloneNode(true);
        const cancelBtn = oldCancelBtn.cloneNode(true);

        oldOkBtn.replaceWith(okBtn);
        oldCancelBtn.replaceWith(cancelBtn);

        /* 5. Gestione sicura della scelta tramite semaforo */
        let isAnswered = false;
        let isShown = false;

        modalEl.addEventListener('shown.bs.modal', () => {
            isShown = true;
        }, { once: true });

        const handleChoice = (choice) => {
            if (isAnswered) return;
            isAnswered = true;
            if (passwordForm) passwordForm.reset();
            closePasswordInfo();

            const closeModal = () => {
                modalEl.addEventListener('hidden.bs.modal', () => resolve(choice), { once: true });
                modalInstance.hide();
            };

            if (isShown) {
                closeModal();
            } else {
                modalEl.addEventListener('shown.bs.modal', closeModal, { once: true });
            }
        };

        let verifying = false;
        const confirm = async () => {
            if (!needsPassword) return handleChoice(true);
            if (verifying || isAnswered) return;
            const input = passwordForm.querySelector('[name="currentPassword"]');
            const errorEl = passwordForm.querySelector('.error_currentPassword');
            errorEl.textContent = '';
            if (!input.value) {
                errorEl.textContent = input.dataset.requiredMessage;
                input.focus();
                return;
            }
            verifying = true;
            okBtn.disabled = cancelBtn.disabled = true;
            try {
                const verification = new FormData();
                verification.set('currentPassword', input.value);
                const response = await apiFetch(urlbase + 'backend/account/verifyCurrentPassword', { method: 'POST', body: verification });
                const data = await response.json();
                verification.delete('currentPassword');
                if (data.result !== true) {
                    errorEl.textContent = data.errors?.currentPassword || data.message || '';
                    input.value = '';
                    input.focus();
                    return;
                }
                options.formData.set('currentPassword', input.value);
                handleChoice(true);
            } catch (error) {
                input.value = '';
                errorEl.textContent = document.getElementById('js-i18n-errors')?.dataset.errRequest || '';
            } finally {
                verifying = false;
                okBtn.disabled = cancelBtn.disabled = false;
            }
        };
        okBtn.addEventListener('click', confirm);
        passwordForm?.addEventListener('submit', event => { event.preventDefault(); confirm(); });
        if (passwordForm) modalEl.addEventListener('shown.bs.modal', () => passwordForm.querySelector('input').focus(), { once: true });
        cancelBtn.addEventListener('click', () => handleChoice(false));

        modalInstance.show();
    });
}

/* Tooltip condiviso: la password personale e quella SMTP mantengono spiegazioni distinte. */
let passwordInfo = null;

function closePasswordInfo() {
    if (!passwordInfo) return;
    const { button, tooltip } = passwordInfo;
    passwordInfo = null;
    tooltip.dispose();
    button.setAttribute('aria-expanded', 'false');
}

document.addEventListener('click', event => {
    const button = event.target.closest('[data-password-info]');
    if (button) {
        event.preventDefault();
        const wasOpen = passwordInfo?.button === button;
        closePasswordInfo();
        if (wasOpen) return;
        const tooltip = bootstrap.Tooltip.getOrCreateInstance(button, {
            trigger: 'manual', container: 'body', animation: false, html: false,
            title: () => button.dataset.passwordInfo
        });
        passwordInfo = { button, tooltip };
        button.setAttribute('aria-expanded', 'true');
        tooltip.show();
    } else if (passwordInfo) {
        const tooltip = document.getElementById(passwordInfo.button.getAttribute('aria-describedby'));
        if (!tooltip?.contains(event.target)) closePasswordInfo();
    }
});
document.addEventListener('hide.bs.modal', closePasswordInfo);
document.addEventListener('hidden.bs.modal', event => {
    event.target.querySelectorAll('input[name="currentPassword"]').forEach(input => { input.value = ''; });
});
document.addEventListener('keydown', event => {
    if (event.key === 'Escape') closePasswordInfo();
});

export function passwordConfirmationRequired(force = false) {
    return force || document.getElementById('lang-definitions')?.dataset.passwordConfirmation === '1';
}

/** Usa il modale comune soltanto quando la riconferma e richiesta dalla configurazione. */
export async function confirmSensitiveAction(formData, message = '', force = false) {
    if (!passwordConfirmationRequired(force)) return true;
    return askConfirm(message, { password: true, forcePassword: force, formData });
}

/* Gestione globale delle modifiche non salvate */
const unsavedFormSelector = 'form[data-unsaved-check]';
const unsavedFormStates = new WeakMap();
const unsavedExternalStates = new WeakMap();
let allowPageUnload = false;
let unsavedConfirmation = null;

/* Restituisce uno stato confrontabile dei campi presenti nel form */
function getUnsavedFormState(form) {
    const state = [];
    const formData = new FormData(form);

    formData.forEach((value, key) => {
        if (value instanceof File) {
            if (value.name === '' && value.size === 0) return;

            state.push([
                key,
                value.name,
                value.size,
                value.type,
                value.lastModified
            ]);
            return;
        }

        state.push([key, String(value)]);
    });

    return JSON.stringify(state);
}

/* Registra il valore iniziale di un form monitorato */
function registerUnsavedForm(form) {
    if (!(form instanceof HTMLFormElement)) return;
    if (!form.matches(unsavedFormSelector)) return;
    if (unsavedFormStates.has(form)) return;

    unsavedFormStates.set(form, getUnsavedFormState(form));
    unsavedExternalStates.set(form, false);
}

/* Recupera i form monitorati presenti nel contesto indicato */
function getUnsavedForms(scope = document) {
    const forms = [];

    if (scope instanceof HTMLFormElement && scope.matches(unsavedFormSelector)) {
        forms.push(scope);
    }

    if (scope && typeof scope.querySelectorAll === 'function') {
        scope.querySelectorAll(unsavedFormSelector).forEach(form => forms.push(form));
    }

    forms.forEach(registerUnsavedForm);

    return forms;
}

/* Verifica la presenza di campi o risorse esterne non salvate */
export function hasUnsavedChanges(scope = document) {
    return getUnsavedForms(scope).some(form => {
        const initialState = unsavedFormStates.get(form);
        const currentState = getUnsavedFormState(form);
        const hasExternalChanges = unsavedExternalStates.get(form) === true;

        return initialState !== currentState || hasExternalChanges;
    });
}

/* Imposta come salvato lo stato corrente dei form indicati */
export function markUnsavedClean(scope) {
    getUnsavedForms(scope).forEach(form => {
        unsavedFormStates.set(form, getUnsavedFormState(form));
        unsavedExternalStates.set(form, false);
    });
}

/* Registra modifiche non rappresentate direttamente dai campi del form */
export function setUnsavedExternalState(form, hasChanges) {
    if (!(form instanceof HTMLFormElement)) return;
    if (!form.matches(unsavedFormSelector)) return;

    registerUnsavedForm(form);
    unsavedExternalStates.set(form, hasChanges === true);
}

/* Richiede conferma prima di eliminare dati non salvati dal contesto */
export async function confirmDiscardChanges(scope = document) {
    if (!hasUnsavedChanges(scope)) return true;

    if (unsavedConfirmation) return false;

    const message = langDefinitions?.dataset.unsavedChanges || '';

    unsavedConfirmation = askConfirm(message).finally(() => {
        unsavedConfirmation = null;
    });

    return unsavedConfirmation;
}

/* Registra anche i form caricati successivamente tramite AJAX */
const unsavedFormsObserver = new MutationObserver(mutations => {
    mutations.forEach(mutation => {
        mutation.addedNodes.forEach(node => {
            if (!(node instanceof Element)) return;

            if (node.matches(unsavedFormSelector)) {
                registerUnsavedForm(node);
            }

            node.querySelectorAll(unsavedFormSelector).forEach(registerUnsavedForm);
        });
    });
});

getUnsavedForms(document);

if (document.body) {
    unsavedFormsObserver.observe(document.body, {
        childList: true,
        subtree: true
    });
}

/* Navigazione tramite link interni o esterni */
document.addEventListener('click', async e => {
    if (e.button !== 0 || e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) return;

    const link = e.target.closest('a[href]');
    if (!link || link.target === '_blank' || link.hasAttribute('download')) return;

    const href = link.getAttribute('href');
    if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;

    const destination = new URL(link.href, window.location.href);
    if (!['http:', 'https:'].includes(destination.protocol)) return;

    if (
        destination.origin === window.location.origin &&
        destination.pathname === window.location.pathname &&
        destination.search === window.location.search
    ) return;

    if (!hasUnsavedChanges(document)) return;

    e.preventDefault();
    e.stopImmediatePropagation();

    const confirmed = await confirmDiscardChanges(document);
    if (!confirmed) return;

    allowPageUnload = true;
    window.location.assign(destination.href);
}, true);

/* Navigazione tramite form esplicitamente destinati al cambio pagina */
document.addEventListener('submit', async e => {
    const form = e.target;

    if (!(form instanceof HTMLFormElement)) return;
    if (!form.matches('[data-unsaved-navigation]')) return;
    if (!hasUnsavedChanges(document)) return;

    e.preventDefault();
    e.stopImmediatePropagation();

    const confirmed = await confirmDiscardChanges(document);
    if (!confirmed) return;

    allowPageUnload = true;
    form.submit();
}, true);

/* Refresh, chiusura scheda e navigazione gestita direttamente dal browser */
window.addEventListener('beforeunload', e => {
    if (allowPageUnload || !hasUnsavedChanges(document)) return;

    e.preventDefault();
    e.returnValue = '';
});

const smoothReplaceTimers = new WeakMap();

export function smoothReplace(container, newHtml) {
    if (!container) return;

    // Annulla un eventuale svuotamento precedente.
    const previousTimer = smoothReplaceTimers.get(container);

    if (previousTimer !== undefined) {
        clearTimeout(previousTimer);
        smoothReplaceTimers.delete(container);
    }

    if (newHtml === '') {
        container.style.transition = 'opacity 0.25s ease-in-out';
        container.style.opacity = '0';

        const timer = setTimeout(() => {
            smoothReplaceTimers.delete(container);

            container.innerHTML = '';
            container.style.transition = '';
            container.style.opacity = '';
        }, 250);

        smoothReplaceTimers.set(container, timer);
        return;
    }

    container.style.transition = 'none';
    container.style.opacity = '0';

    container.innerHTML = newHtml;

    container.offsetHeight;

    container.style.transition = 'opacity 0.25s ease-in-out';
    container.style.opacity = '1';
}

/* Inizializza la chiusura automatica di un Offcanvas al click sui link interni */
export function initOffcanvasAutoClose(offcanvasId, linkSelector = '.cmd-link') {

    const offcanvasEl = document.getElementById(offcanvasId);
    
    // Se l'Offcanvas non esiste nella pagina, interrompe l'esecuzione
    if ( ! offcanvasEl) return;

    const links = offcanvasEl.querySelectorAll(linkSelector);
    
    links.forEach(link => {
        link.addEventListener('click', () => {
            const instance = bootstrap.Offcanvas.getInstance(offcanvasEl);
            if (instance) {
                instance.hide();
            }
        });
    });
}

/* Applica le traduzioni backend anche alle select dei frammenti appena caricati. */
const translateSelect = select => changeLanguageManager.update(select);
export function initTomSelects(scope = document) {
    enhanceTomSelects(scope, translateSelect);
}

observeTomSelects(translateSelect);

/**
 * Inizializza un singolo campo data/ora senza vincoli di intervallo
 * @param {string} containerSelector - Il selettore del contenitore (es. '.input-group' o '#id-parent')
 */
export function initSingleDatePicker(containerSelector) {
    if (typeof flatpickr === 'undefined') return;

    /* Recupera la lingua attiva dal tag HTML (es. 'it', 'en') */
    const currentLang = document.documentElement.lang || 'it';

    return flatpickr(containerSelector, {
        locale: flatpickr.l10ns[currentLang] || 'default',
        enableTime: true,
        time_24hr: true,
        dateFormat: "Y-m-d H:i:s",
        altInput: true,
        altFormat: "d/m/Y H:i:s",
        allowInput: true,
        wrap: true
    });
}

/**
 * Inizializza una coppia di campi data/ora relazionati tra loro (Range)
 * @param {string} fromContainerSelector - Il selettore del contenitore "Da"
 * @param {string} toContainerSelector - Il selettore del contenitore "A"
 */
export function initRangeDatePicker(fromContainerSelector, toContainerSelector) {
    if (typeof flatpickr === 'undefined') return { pickerFrom: null, pickerTo: null };

    /* Recupera la lingua attiva dal tag HTML (es. 'it', 'en') */
    const currentLang = document.documentElement.lang || 'it';

    const baseConfig = {
        /* Assegna dinamicamente la lingua o usa il default (inglese) in caso di errore */
        locale: flatpickr.l10ns[currentLang] || 'default',
        enableTime: true,
        enableSeconds: true,
        time_24hr: true,
        dateFormat: "Y-m-d H:i:s",
        altInput: true,
        altFormat: "d/m/Y H:i:s",
        allowInput: true,
        wrap: true,
        
        /* OVERRIDE: Aggira il bug nativo di Flatpickr forzando il padding a 2 cifre */
        formatDate: (date, formatStr) => {
            const pad = (n) => n < 10 ? '0' + n : n;
            
            const tokens = {
                Y: date.getFullYear(),
                m: pad(date.getMonth() + 1),
                d: pad(date.getDate()),
                H: pad(date.getHours()),
                i: pad(date.getMinutes()),
                s: pad(date.getSeconds()) /* Forza lo zero iniziale sui secondi */
            };
            
            /* Sostituisce i token nella stringa di formato con i valori calcolati */
            return formatStr.replace(/[YmdHis]/g, match => tokens[match]);
        }
    };

    const pickerFrom = flatpickr(fromContainerSelector, {
        ...baseConfig,
        onChange: function(selectedDates, dateStr, instance) {
            if (pickerTo) pickerTo.set("minDate", dateStr);
            const input = instance.element.querySelector('input') || instance.element;
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }
    });

    const pickerTo = flatpickr(toContainerSelector, {
        ...baseConfig,
        onChange: function(selectedDates, dateStr, instance) {
            if (pickerFrom) pickerFrom.set("maxDate", dateStr);
            const input = instance.element.querySelector('input') || instance.element;
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }
    });

    return { pickerFrom, pickerTo };
}
