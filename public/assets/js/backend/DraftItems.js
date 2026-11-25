/**
 * Rende anteprime provenienti esclusivamente dalle viste server dopo la validazione.
 * Gli identificativi locali vengono applicati solo agli attributi di navigazione, mai ai contenuti.
 * @param {Array<object>} items Bozze con previewOutput generato dal server.
 * @param {string} emptyText Messaggio del pannello vuoto.
 * @returns {DocumentFragment} Stessa struttura usata dai record persistiti.
 */
export function renderDraftItems(items, emptyText) {
    const fragment = document.createDocumentFragment();
    if (!items.length) {
        const empty = document.createElement('p');
        empty.className = 'small app-text-muted px-3 py-2 mb-0';
        empty.textContent = emptyText;
        fragment.append(empty);
        return fragment;
    }
    for (const item of items) {
        const template = document.createElement('template');
        template.innerHTML = item.previewOutput;
        const root = template.content.querySelector('[data-draft-item]');
        if (!root || !/^draft-(task|note)-\d+$/.test(item.draftId)) throw new Error('Invalid draft preview');
        const ids = new Map([...root.querySelectorAll('[id]')].map(node => [node.id, `${node.id}-${item.draftId}`]));
        root.dataset.draftItem = item.draftId;
        root.querySelectorAll('[id]').forEach(node => { node.id = ids.get(node.id); });
        for (const attribute of ['aria-controls', 'aria-labelledby', 'data-bs-target']) {
            root.querySelectorAll(`[${attribute}]`).forEach(node => {
                const value = node.getAttribute(attribute);
                const hash = value.startsWith('#');
                const mapped = ids.get(hash ? value.slice(1) : value);
                if (mapped) node.setAttribute(attribute, (hash ? '#' : '') + mapped);
            });
        }
        root.querySelectorAll('[data-draft-id]').forEach(node => { node.dataset.draftId = item.draftId; });
        root.querySelectorAll('[data-draft-remove]').forEach(node => { node.dataset.draftRemove = item.draftId; });
        fragment.append(root);
    }
    return fragment;
}
