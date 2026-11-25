import { apiFetch, showAlert } from './backend.js';

let pickerSequence = 0;
/**
 * Collega una selezione al solo editor locale: il recupero del modello non salva alcun record.
 * @param {HTMLElement} editor Editor dei passi.
 * @param {function(Array):Promise<boolean>} append Inserimento locale con limiti e animazione comuni.
 * @returns {void}
 */
export function initChecklistPicker(editor, append) {
    const picker = (editor.closest('form') || editor).querySelector('[data-checklist-picker]');
    if (!picker || picker.dataset.bound === 'true') return;
    picker.dataset.bound = 'true';
    const select = picker.querySelector('[data-checklist-choice]');
    const button = picker.querySelector('[data-checklist-apply]');
    if (!select.id) select.id = `checklist-choice-${++pickerSequence}`;
    if (!select.tomselect) picker.querySelector('[data-checklist-label]').htmlFor = select.id;
    button.addEventListener('click', async () => {
        const uuid = select.value;
        if (!uuid || picker.dataset.busy === 'true' || editor.dataset.busy === 'true') return;
        picker.dataset.busy = 'true'; button.disabled = true;
        // Una risposta tarda non deve alterare un modale già chiuso o riaperto per un altro task.
        const revision = editor.dataset.checklistRevision;
        const modal = picker.closest('.modal');
        let cancelled = false;
        const cancel = () => { cancelled = true; };
        modal?.addEventListener('hide.bs.modal', cancel, { once: true });
        try {
            const body = new FormData(); body.set('uuid', uuid);
            const response = await apiFetch(picker.dataset.endpoint, { method: 'POST', body });
            const data = await response.json();
            if (cancelled || !editor.isConnected || revision !== editor.dataset.checklistRevision) return;
            if (data.result !== true || !Array.isArray(data.steps) || !data.steps.every(step => typeof step.title === 'string')) {
                showAlert('danger', data.message || picker.dataset.error); return;
            }
            if (!await append(data.steps)) showAlert('danger', picker.dataset.limit);
        } catch (error) {
            if (!cancelled && editor.isConnected) showAlert('danger', picker.dataset.error);
        } finally {
            modal?.removeEventListener('hide.bs.modal', cancel);
            delete picker.dataset.busy;
            if (button.isConnected) button.disabled = false;
        }
    });
}
