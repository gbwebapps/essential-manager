/**
 * Libera le sole istanze Bootstrap delle note prima di rimuovere o ricaricare un pannello.
 * @param {HTMLElement} scope Riga, editor o pannello in sostituzione.
 * @returns {void} I movimenti restano quelli nativi di Bootstrap Collapse.
 */
export function disposeNoteCollapses(scope) {
    scope.querySelectorAll('[data-note-collapse]').forEach(panel => {
        const instance = bootstrap.Collapse.getInstance(panel);
        if (!instance) return;
        if (panel.classList.contains('collapsing')) panel.dispatchEvent(new Event('transitionend'));
        instance.dispose();
    });
}
