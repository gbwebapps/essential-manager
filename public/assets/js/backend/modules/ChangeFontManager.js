import { apiFetch, showAlert, getAjaxErrorContext } from '../backend.js';

/** Usa soltanto le famiglie locali ammesse dal server; la preferenza rapida dura per la sessione. */
export class ChangeFontManager {
    constructor() {
        if (ChangeFontManager.instance) return ChangeFontManager.instance;
        ChangeFontManager.instance = this;
        this.root = document.getElementById('interface-preferences');
        this.fonts = JSON.parse(this.root?.dataset.fonts || '{}');
        this.pending = false;

        document.addEventListener('click', event => {
            const button = event.target.closest('[data-interface-font]');
            if (!button || !this.root) return;
            event.preventDefault();
            bootstrap.Dropdown.getInstance(button.closest('.dropdown').querySelector('[data-bs-toggle="dropdown"]'))?.hide();
            this.change(button.dataset.interfaceFont);
        });
    }

    getActiveFont() {
        return document.documentElement.dataset.backendFont || 'comfortaa';
    }

    /** Carica il file locale prima di salvare la preferenza e cambiare la resa della pagina. */
    async prepare(font) {
        if (!Object.hasOwn(this.fonts, font)) throw new Error('Invalid font');
        let timeout;

        try {
            const faces = await Promise.race([
                document.fonts.load('400 1em "' + this.fonts[font] + '"'),
                new Promise((resolve, reject) => { timeout = setTimeout(() => reject(new Error('Font timeout')), 15000); })
            ]);
            if (!faces.length) throw new Error('Font unavailable');
        } finally {
            clearTimeout(timeout);
        }
    }

    commit(font) {
        document.documentElement.style.setProperty('--backend-font', this.fonts[font]);
        document.documentElement.dataset.backendFont = font;
        if (this.root) this.root.dataset.activeFont = font;
        document.querySelectorAll('[data-interface-font]').forEach(button => {
            button.classList.toggle('active', button.dataset.interfaceFont === font);
            button.setAttribute('aria-pressed', String(button.dataset.interfaceFont === font));
        });
    }

    async apply(font) {
        if (font !== this.getActiveFont()) {
            await this.prepare(font);
            this.commit(font);
        }
    }

    async change(font) {
        if (!Object.hasOwn(this.fonts, font)) return;
        if (this.pending) { this.nextFont = font; return; }
        if (font === this.getActiveFont()) return;
        this.pending = true;

        try {
            await this.prepare(font);
            const form = new FormData();
            form.set('preference', 'font');
            form.set('value', font);
            const data = await (await apiFetch(this.root.dataset.url, { method: 'POST', body: form, reloadOnForbidden: false })).json();
            if (data.result !== true || data.font !== font) throw new Error('Font rejected');
            this.commit(font);
        } catch (error) {
            if ( ! getAjaxErrorContext(error)?.handled) showAlert('danger', document.getElementById('lang-definitions')?.dataset.interfaceError || error.message);
        } finally {
            this.pending = false;
            const next = this.nextFont;
            this.nextFont = null;
            if (next && next !== this.getActiveFont()) this.change(next);
        }
    }
}
