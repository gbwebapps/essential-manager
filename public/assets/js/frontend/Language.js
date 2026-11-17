import {apiFetch, urlbase, showAlert, langDefinitions} from './frontend.js';
import {LocalizedBindings} from '../components/LocalizedBindings.js';
import { localizeTomSelects } from '../components/TomSelect.js';

/** Cambia la lingua del visitatore senza sostituire pagine o form. */
class FrontendLanguageManager extends LocalizedBindings {
    /** Collega il selettore dell'intestazione alla preferenza corrente. */
    init() {
        const form = document.querySelector('[data-frontend-language-form]');
        if (!form) return;

        const select = form.querySelector('select');
        let current = select.value;
        let pending = null;

        form.addEventListener('submit', event => event.preventDefault());
        select.addEventListener('change', async () => {
            if (pending !== null) {
                select.value = pending;
                return;
            }
            if (select.value === current) return;

            pending = select.value;
            select.setAttribute('aria-busy', 'true');
            try {
                const scopes = [document.documentElement, ...Array.from(document.querySelectorAll('template'), template => template.content)];
                const body = new FormData(form);
                body.set('keys', JSON.stringify(this.translationKeys(scopes)));
                const response = await apiFetch(urlbase + 'interface/language', {method: 'POST', body, showLoader: false, reloadOnForbidden: false});
                const data = await response.json();
                if (data.result !== true || data.language !== pending || !data.translations) {
                    showAlert('danger', data.message || langDefinitions.dataset.interfaceError);
                    return;
                }

                this.translations = data.translations;
                document.documentElement.lang = data.language;
                scopes.forEach(scope => this.update(scope));
                localizeTomSelects();
                const title = document.querySelector('[data-frontend-site-title]');
                const description = document.querySelector('[data-frontend-site-description]');
                if (title) title.textContent = data.siteTitle;
                if (description) description.content = description.dataset.pageDescription ?? data.siteDescription;
                current = data.language;
            } catch (error) {
                console.error('Cambio lingua frontend non completato:', error);
            } finally {
                select.value = current;
                select.removeAttribute('aria-busy');
                pending = null;
            }
        });
    }

    /**
     * Raccoglie le sole chiavi richieste dai testi, dagli attributi e dai template presenti nella pagina.
     * @param {Array<Element|DocumentFragment>} scopes
     * @returns {string[]}
     */
    translationKeys(scopes) {
        const keys = new Set();
        const collect = binding => {
            if (!binding || typeof binding !== 'object' || binding.variants) return;
            if (typeof binding.key === 'string') keys.add(binding.key);
            (binding.parts || []).forEach(collect);
            (binding.parameters || []).forEach(collect);
        };
        for (const scope of scopes) {
            for (const node of [scope, ...scope.querySelectorAll('*')]) {
                for (const attribute of Array.from(node.attributes || [])) {
                    if (!attribute.name.startsWith('data-i18n-')) continue;
                    try { collect(JSON.parse(attribute.value)); } catch { continue; }
                }
            }
        }
        return [...keys];
    }
}

new FrontendLanguageManager().init();
