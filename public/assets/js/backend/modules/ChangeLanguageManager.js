import { apiFetch, showAlert, getAjaxErrorContext } from '../backend.js';
import { LocalizedBindings } from '../../components/LocalizedBindings.js';
import { localizeTomSelects } from '../../components/TomSelect.js';

/** Aggiorna esclusivamente etichette e attributi esplicitamente associati a una chiave. */
export class ChangeLanguageManager extends LocalizedBindings {
    constructor() {
        if (ChangeLanguageManager.instance) return ChangeLanguageManager.instance;
        super();
        ChangeLanguageManager.instance = this;
        this.root = document.getElementById('interface-preferences');
        this.messages = new Map();
        this.pending = false;
        this.localeLoads = new Map();

        document.addEventListener('click', event => {
            const button = event.target.closest('[data-interface-language]');
            if (!button || !this.root) return;
            event.preventDefault();
            bootstrap.Dropdown.getInstance(button.closest('.dropdown').querySelector('[data-bs-toggle="dropdown"]'))?.hide();
            this.change(button.dataset.interfaceLanguage);
        });

        this.observer = new MutationObserver(mutations => {
            const scopes = new Set();
            mutations.forEach(mutation => {
                if (mutation.target instanceof Element) scopes.add(mutation.target);
                mutation.addedNodes.forEach(node => { if (node instanceof Element) scopes.add(node); });
            });
            scopes.forEach(scope => this.update(scope));
        });
        this.observer.observe(document.body, { childList: true, subtree: true });
    }

    rememberMessages(bindings = {}) {
        Object.entries(bindings).forEach(([message, binding]) => this.messages.set(message, binding));
    }

    bindMessage(node, message, escaped = false) {
        node.removeAttribute('data-i18n-text');
        let binding = this.messages.get(message);
        if (!binding) {
            const sources = document.querySelectorAll('[data-i18n-data-message], [data-i18n-data-confirm], [data-i18n-data-required-message], #lang-definitions, #js-i18n-errors');
            for (const source of sources) {
                const attribute = Array.from(source.attributes).find(item => item.name.startsWith('data-i18n-data-') && source.getAttribute(item.name.slice(10)) === message);
                if (!attribute) continue;
                try { binding = JSON.parse(attribute.value); } catch { continue; }
                break;
            }
        }
        if (binding) node.setAttribute('data-i18n-text', JSON.stringify({ ...binding, escaped }));
    }

    async ensureLocale(language) {
        if (typeof flatpickr === 'undefined' || language === 'en' || flatpickr.l10ns[language]) return;
        if (!this.localeLoads.has(language)) {
            this.localeLoads.set(language, new Promise((resolve, reject) => {
                const script = document.createElement('script');
                const base = document.getElementById('hidden-urlbase').dataset.urlbase;
                script.src = base + 'assets/vendor/flatpickr/js/' + language + '.js';
                const fail = () => { clearTimeout(timeout); script.remove(); this.localeLoads.delete(language); reject(new Error('Locale unavailable')); };
                const timeout = setTimeout(fail, 15000);
                script.onload = () => { clearTimeout(timeout); resolve(); };
                script.onerror = fail;
                document.head.append(script);
            }));
        }
        await this.localeLoads.get(language);
    }

    localizeWidgets(language) {
        const pickers = new Set();
        document.querySelectorAll('*').forEach(element => { if (element._flatpickr) pickers.add(element._flatpickr); });
        pickers.forEach(picker => {
            const value = picker.input.value;
            const alternative = picker.altInput?.value;
            picker.set('locale', language === 'en' ? 'default' : language);
            picker.input.value = value;
            if (picker.altInput) picker.altInput.value = alternative;
        });
        localizeTomSelects();
    }

    async apply(data) {
        if (!['en', 'it', 'es', 'fr', 'de', 'zh'].includes(data.language) || !data.translations) throw new Error('Invalid language');
        await this.ensureLocale(data.language);
        const focused = document.activeElement;
        const selection = focused && typeof focused.selectionStart === 'number' ? [focused.selectionStart, focused.selectionEnd] : null;
        const scroll = [window.scrollX, window.scrollY];
        document.querySelectorAll('[class^="error_"], [id^="error-img-"]').forEach(node => {
            if (node.textContent.trim() && !node.hasAttribute('data-i18n-text')) this.bindMessage(node, node.textContent, true);
        });
        this.translations = data.translations;
        document.documentElement.lang = data.language;
        if (this.root) this.root.dataset.activeLanguage = data.language;
        this.update(document.documentElement);
        this.localizeWidgets(data.language);
        document.querySelectorAll('[data-interface-language]').forEach(button => {
            button.classList.toggle('active', button.dataset.interfaceLanguage === data.language);
            button.setAttribute('aria-pressed', String(button.dataset.interfaceLanguage === data.language));
        });
        document.dispatchEvent(new CustomEvent('interface:language', { detail: { language: data.language } }));
        if (focused?.isConnected && document.activeElement !== focused) focused.focus({ preventScroll: true });
        if (selection) focused.setSelectionRange(...selection);
        window.scrollTo(...scroll);
    }

    async change(language) {
        if (!['en', 'it', 'es', 'fr', 'de', 'zh'].includes(language)) return;
        if (this.pending) { this.nextLanguage = language; return; }
        if (language === document.documentElement.lang) return;
        this.pending = true;
        try {
            await this.ensureLocale(language);
            const form = new FormData();
            form.set('preference', 'language');
            form.set('value', language);
            const data = await (await apiFetch(this.root.dataset.url, { method: 'POST', body: form, reloadOnForbidden: false })).json();
            if (data.result !== true) throw new Error('Language rejected');
            await this.apply(data);
        } catch (error) {
            if ( ! getAjaxErrorContext(error)?.handled) showAlert('danger', document.getElementById('lang-definitions')?.dataset.interfaceError || error.message);
        } finally {
            this.pending = false;
            const next = this.nextLanguage;
            this.nextLanguage = null;
            if (next && next !== document.documentElement.lang) this.change(next);
        }
    }
}
