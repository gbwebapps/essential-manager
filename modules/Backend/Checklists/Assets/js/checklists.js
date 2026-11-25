import { updateNavigationCounts } from './ManagerFeedback.js';
import { initTaskSteps } from './TaskSteps.js';
import { urlbase, action, markUnsavedClean, initOffcanvasAutoClose, initRangeDatePicker } from './backend.js';
import { ListManager, AddManager, EditManager, DeleteManager, GeneralDataManager, MetaDataManager } from './modules/Crud.js';

const actions = {
    showAll: function() {
        const listManager = new ListManager({
            controller: 'checklists',
            url: urlbase + 'backend/checklists/showAll',
            rootId: 'checklists-list',
            containerId: 'showAll-checklists-container',
            searchFields: ['name'],
            searchDates: ['created_at', 'updated_at']
        }, {
            onShowAfter: async data => {
                updateNavigationCounts(data);
                const counts = data.trashCounts;
                const filters = ['active', 'trashed', 'all'];
                if (!counts || !filters.every(filter => Number.isSafeInteger(counts[filter]) && counts[filter] >= 0)) return;
                listManager.root.querySelectorAll('[data-trash-count]').forEach(badge => {
                    const filter = badge.dataset.trashCount;
                    if (!filters.includes(filter)) return;
                    badge.textContent = String(counts[filter]);
                    badge.hidden = false;
                });
            }
        });
        listManager.init();

        new DeleteManager({
            controller: 'checklists',
            urls: {
                softDelete: urlbase + 'backend/checklists/softDelete',
                restoreDelete: urlbase + 'backend/checklists/restoreDelete',
                hardDelete: urlbase + 'backend/checklists/hardDelete'
            },
            listManager: listManager
        }).init();

        initRangeDatePicker('#wrapper-checklists-created_at-from', '#wrapper-checklists-created_at-to');
        initRangeDatePicker('#wrapper-checklists-updated_at-from', '#wrapper-checklists-updated_at-to');
        initOffcanvasAutoClose('checklists-actionsOffcanvas');
    },

    add: function() {
        new AddManager({
            url: urlbase + 'backend/checklists/add',
            formSelector: '#checklists-add',
            resetSelector: '#add-reset',
            containerId: 'add-checklists-container'
        }, {

            onResetAfter: data => { initChecklistSteps(); updateNavigationCounts(data); },
            onAddAfter: updateNavigationCounts
        }).init();
    },

    edit: function() {
        new EditManager({
            url: urlbase + 'backend/checklists/edit',
            formSelector: '#checklists-edit',
            refreshSelector: '#edit-refresh',
            containerId: 'edit-checklists-container'
        }, {
            onRefreshAfter: () => { initChecklistSteps(); markUnsavedClean(document.querySelector('#checklists-edit')); }
        }).init();
        initPanels();
    },

    show: function() {
        initPanels();
    }
};

function initPanels() {
    new GeneralDataManager({ url: urlbase + 'backend/checklists/getGeneralData', formSelector: '#getGeneralData' }, { onGeneralDataAfter: initChecklistSteps }).init();
    new MetaDataManager({ url: urlbase + 'backend/checklists/getMetaData', formSelector: '#getMetaData' }).init();
}


function initChecklistSteps() {
    const form = document.querySelector('#checklists-add, #checklists-edit');
    if (form) initTaskSteps(form);
}
initChecklistSteps();
if (actions[action]) actions[action]();
