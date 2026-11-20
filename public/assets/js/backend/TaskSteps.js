function focusStep(input) {
    if ( ! input) return;
    input.focus({ preventScroll: true });
    const rect = input.getBoundingClientRect();
    if (rect.top < 0 || rect.bottom > window.innerHeight) {
        input.scrollIntoView({ block: 'nearest', behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth' });
    }
}

function updateStepRows(editor) {
    const rows = [...editor.querySelectorAll('[data-task-step-list] > [data-task-step-row]')];
    rows.forEach((row, index) => {
        const input = row.querySelector('[data-step-title]');
        const id = `${editor.dataset.fieldPrefix}-step-${row.dataset.stepKey}`;
        input.id = id;
        input.name = `steps[${index}][title]`;
        row.querySelector('[data-step-uuid]').name = `steps[${index}][uuid]`;
        row.querySelector('[data-step-number]').textContent = String(index + 1);
        const errorBox=row.querySelector('[data-work-field-error]');
        errorBox.dataset.workFieldError = `steps.${index}.title`;
        if(editor.dataset.standalone==='true'){const otherClasses=[...errorBox.classList].filter(name=>!name.startsWith('error_step_'));errorBox.className=`error_step_${index}_title ${otherClasses.join(' ')}`;}
        row.querySelector('[data-task-step-up]').disabled=editor.dataset.busy==='true'||index===0;
        row.querySelector('[data-task-step-down]').disabled=editor.dataset.busy==='true'||index===rows.length-1;
        const label = row.querySelector('[data-step-label]');
        label.htmlFor = id;
        label.textContent = `${editor.dataset.stepLabel} ${index + 1}`;
        row.querySelector('[data-task-step-remove]').disabled = editor.dataset.busy === 'true' || rows.length === 1;
    });
    editor.querySelector('[data-task-step-add]').disabled = editor.dataset.busy === 'true';
}

function createStepRow(editor, step = {}) {
    const row = editor.querySelector('[data-task-step-template]').content.firstElementChild.cloneNode(true);
    row.dataset.stepKey = editor.dataset.nextStepKey || '0';
    editor.dataset.nextStepKey = String(Number(row.dataset.stepKey) + 1);
    row.querySelector('[data-step-title]').value = step.title || '';
    row.querySelector('[data-step-uuid]').value = step.uuid || '';
    return row;
}

export function fillTaskSteps(editor, steps) {
    if ( ! editor) return;
    editor.dataset.nextStepKey = '0';
    delete editor.dataset.busy;
    const items = Array.isArray(steps) && steps.length ? steps : [{}];
    editor.querySelector('[data-task-step-list]').replaceChildren(...items.map(step => createStepRow(editor, step)));
    updateStepRows(editor);
}

async function animateStepRow(row, removing = false) {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    const height = row.getBoundingClientRect().height;
    row.style.overflow = 'hidden';
    const animation = row.animate(removing ? [
        { height: `${height}px`, opacity: 1 }, { height: '0px', opacity: 0 }
    ] : [
        { height: '0px', opacity: 0 }, { height: `${height}px`, opacity: 1 }
    ], { duration: 180, easing: 'ease-in-out' });
    await animation.finished;
    row.style.overflow = '';
}

export function initTaskSteps(form) {
    const editor = form.querySelector('[data-task-steps]');
    if ( ! editor || editor.dataset.bound === 'true') return;
    editor.dataset.bound = 'true';
    [...editor.querySelectorAll('[data-task-step-list] > [data-task-step-row]')].forEach((row,index)=>row.dataset.stepKey=String(index));
    editor.dataset.nextStepKey=String(editor.querySelectorAll('[data-task-step-list] > [data-task-step-row]').length);
    updateStepRows(editor);
    editor.addEventListener('click', async event => {
        const add = event.target.closest('[data-task-step-add]');
        const remove = event.target.closest('[data-task-step-remove]');
        const move=event.target.closest('[data-task-step-up],[data-task-step-down]');
        if(move&&editor.dataset.busy!=='true'){
            const row=move.closest('[data-task-step-row]');const peer=move.hasAttribute('data-task-step-up')?row.previousElementSibling:row.nextElementSibling;
            if(!peer)return;const before=[...row.parentElement.children].map(node=>[node,node.getBoundingClientRect().top]);
            if(move.hasAttribute('data-task-step-up'))row.parentElement.insertBefore(row,peer);else row.parentElement.insertBefore(peer,row);
            updateStepRows(editor);
            if(!matchMedia('(prefers-reduced-motion: reduce)').matches)before.forEach(([node,top])=>node.animate([{transform:`translateY(${top-node.getBoundingClientRect().top}px)`},{transform:'translateY(0)'}],{duration:180,easing:'ease-in-out'}));
            return;
        }
        if (( ! add && ! remove) || editor.dataset.busy === 'true') return;
        const list = editor.querySelector('[data-task-step-list]');
        if (remove && list.children.length <= 1) return;
        editor.dataset.busy = 'true';
        updateStepRows(editor);
        try {
            if (add) {
                const row = createStepRow(editor);
                list.append(row);
                updateStepRows(editor);
                await animateStepRow(row);
                focusStep(row.querySelector('[data-step-title]'));
            } else {
                const row = remove.closest('[data-task-step-row]');
                const next = row.nextElementSibling || row.previousElementSibling;
                await animateStepRow(row, true);
                row.remove();
                focusStep(next?.querySelector('[data-step-title]'));
            }
        } finally {
            delete editor.dataset.busy;
            updateStepRows(editor);
        }
    });
}

