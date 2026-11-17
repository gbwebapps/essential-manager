const selector = 'select:not([multiple]):not([data-native-select]), select.tom-select[multiple]:not([data-native-select])';
const accessibleAttributes = ['aria-label', 'aria-describedby', 'aria-required', 'aria-busy'];
const defaults = new WeakMap();

function selectsIn(root, query = selector) {
    const selects = [...root.querySelectorAll(query)];
    if (root.matches?.(query)) selects.unshift(root);
    return selects;
}

function syncAccessibility(select) {
    const control = select.tomselect.focus_node;
    accessibleAttributes.forEach(attribute => {
        const value = select.getAttribute(attribute);
        if (value === null) control.removeAttribute(attribute);
        else control.setAttribute(attribute, value);
    });
}

/** Inizializza le select singole e le select multiple già abilitate al componente. */
export function initTomSelects(scope = document, translate = null) {
    if (typeof globalThis.TomSelect !== 'function') return;

    const definitions = document.getElementById('lang-definitions');
    for (const select of selectsIn(scope)) {
        if (select.tomselect || select.closest('.flatpickr-calendar')) continue;
        translate?.(select);

        const options = [...select.options];
        const selected = options.filter(option => option.defaultSelected).map(option => option.value);
        const fallback = options.find(option => !option.disabled && !option.closest('optgroup[disabled]'))?.value ?? '';
        defaults.set(select, select.multiple ? selected : selected.at(-1) ?? fallback);

        const hasEmpty = options.some(option => option.value === '');
        const canClear = select.multiple || select.classList.contains('tom-select') || hasEmpty;
        new TomSelect(select, {
            placeholder: definitions?.dataset.select || 'Seleziona',
            allowEmptyOption: !select.multiple && hasEmpty,
            plugins: canClear ? {remove_button: {title: definitions?.dataset.remove || 'Rimuovi'}} : {},
            persist: false,
            create: false,
        });
        syncAccessibility(select);
    }
}

/** Mantiene coerente il valore nativo quando un gestore aggiorna un campo senza emettere eventi. */
export function setControlValue(control, value) {
    const instance = control.tomselect;
    if (!instance) {
        control.value = value;
        return;
    }

    instance.setValue(value, true);
    if (instance.items.length === 0) instance.updateOriginalInput({silent: true});
}

/** Aggiorna etichette e opzioni dopo la traduzione dell'interfaccia. */
export function localizeTomSelects() {
    const definitions = document.getElementById('lang-definitions');
    document.querySelectorAll('select.tomselected').forEach(select => {
        const instance = select.tomselect;
        const search = instance.control_input.value;
        instance.settings.placeholder = definitions?.dataset.select || '';
        instance.inputState();
        [...select.options].forEach(option => {
            if (option.hasAttribute('data-i18n-text') && Object.hasOwn(instance.options, option.value)) {
                instance.updateOption(option.value, {...instance.options[option.value], text: option.textContent});
            }
        });
        instance.control_input.value = search;
        instance.control.querySelectorAll('.remove').forEach(button => button.title = definitions?.dataset.remove || '');
        const removeButton = instance.plugins?.settings?.remove_button;
        if (removeButton) removeButton.title = definitions?.dataset.remove || '';
    });
}

/** Collega le select dei frammenti AJAX e rilascia le istanze rimosse dalla pagina. */
export function observeTomSelects(translate = null) {
    initTomSelects(document, translate);

    const observer = new MutationObserver(records => {
        for (const record of records) {
            if (record.type === 'attributes') {
                const select = record.target;
                const instance = select.tomselect;
                if (!instance) continue;
                if (record.attributeName === 'disabled') {
                    if (select.disabled !== instance.isDisabled) {
                        if (select.disabled) instance.disable();
                        else instance.enable();
                    }
                } else if (record.attributeName === 'required') {
                    instance.isRequired = select.required;
                    instance.refreshState();
                } else {
                    syncAccessibility(select);
                }
                continue;
            }

            record.removedNodes.forEach(node => {
                if (!(node instanceof Element)) return;
                selectsIn(node, 'select.tomselected').forEach(select => {
                    if (!select.isConnected) select.tomselect?.destroy();
                });
            });
            record.addedNodes.forEach(node => {
                if (node instanceof Element && node.isConnected) initTomSelects(node, translate);
            });
        }
    });
    observer.observe(document.body, {
        subtree: true, childList: true, attributes: true,
        attributeFilter: ['disabled', 'required', ...accessibleAttributes]
    });

    document.addEventListener('change', event => {
        const select = event.target;
        const instance = select.tomselect;
        if (!instance) return;

        const value = select.multiple ? [...select.selectedOptions].map(option => option.value) : select.value;
        if (JSON.stringify(instance.getValue()) !== JSON.stringify(value)) instance.sync();
    }, true);

    document.addEventListener('reset', event => {
        const form = event.target;
        setTimeout(() => {
            if (event.defaultPrevented || !form.isConnected) return;
            form.querySelectorAll('select.tomselected').forEach(select => {
                setControlValue(select, defaults.get(select));
            });
        }, 0);
    });
}
