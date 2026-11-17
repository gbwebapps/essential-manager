/** Applica traduzioni ai soli nodi e attributi associati a una chiave. */
export class LocalizedBindings {
    constructor() {
        this.translations = {};
    }

    format(template, parameters = []) {
        let index = 0;
        return template.replace(/%(?:(\d+)\$)?([+\-0\d]*)(?:\.(\d+))?([sdif%])/g, (match, position, width, precision, type) => {
            if (type === '%') return '%';
            const value = parameters[position ? Number(position) - 1 : index++];
            if (value === undefined) return match;
            let text = type === 'd' || type === 'i' ? String(parseInt(value, 10)) : type === 'f' ? Number(value).toFixed(precision === undefined ? 6 : Number(precision)) : String(value);
            if (width && Math.abs(Number(width)) > text.length) text = Number(width) < 0 ? text.padEnd(Math.abs(Number(width)), ' ') : text.padStart(Number(width), width.startsWith('0') ? '0' : ' ');
            return text;
        });
    }

    safeHtml(html) {
        const template = document.createElement('template');
        template.innerHTML = html;
        const allowed = new Set(['B', 'STRONG', 'BR', 'P', 'SPAN', 'DIV', 'EM', 'SMALL', 'UL', 'OL', 'LI']);
        template.content.querySelectorAll('*').forEach(element => {
            if (['SCRIPT', 'STYLE', 'IFRAME', 'OBJECT'].includes(element.tagName)) { element.remove(); return; }
            if (!allowed.has(element.tagName)) { element.replaceWith(...element.childNodes); return; }
            Array.from(element.attributes).forEach(attribute => {
                if (attribute.name !== 'class' || !/^[a-zA-Z0-9 _-]*$/.test(attribute.value)) element.removeAttribute(attribute.name);
            });
        });
        return template.innerHTML;
    }

    render(binding) {
        if (typeof binding !== 'object' || binding === null) return String(binding);
        if (binding.parts) return binding.parts.map(part => this.render(part)).join('');
        const template = binding.variants?.[document.documentElement.lang] ?? this.translations[binding.key];
        if (typeof template !== 'string') return null;
        const parameters = (binding.parameters || []).map(value => typeof value === 'object' && value !== null ? this.render(value) : value);
        return (binding.prefix || '') + this.format(template, parameters) + (binding.suffix || '');
    }

    update(scope) {
        if (!Object.keys(this.translations).length) return;
        const nodes = [scope, ...scope.querySelectorAll('*')];
        nodes.forEach(node => {
            if (node.matches?.('[class^="error_"], [id^="error-img-"]') && node.textContent.trim() === '') node.removeAttribute('data-i18n-text');
            Array.from(node.attributes || []).forEach(attribute => {
                if (!attribute.name.startsWith('data-i18n-')) return;
                const target = attribute.name.slice(10);
                if (target !== 'text' && !/^(placeholder|title|alt|label|aria-[a-z-]+|data-(?!i18n)[a-z0-9-]+)$/.test(target)) return;
                let binding;
                try { binding = JSON.parse(attribute.value); } catch { return; }
                const text = this.render(binding);
                if (text === null) return;

                if (target !== 'text') {
                    if (node.getAttribute(target) !== text) node.setAttribute(target, text);
                } else if (binding.escaped) {
                    if (node.textContent !== text) node.textContent = text;
                } else {
                    const html = this.safeHtml(text);
                    if (node.innerHTML !== html) node.innerHTML = html;
                }
            });
        });
    }
}
