import { urlbase, action, initOffcanvasAutoClose, initRangeDatePicker } from './backend.js';
import { ListManager, AddManager, EditManager, DeleteManager, GeneralDataManager, MetaDataManager } from './modules/Crud.js';

const actions = {
    showAll: function() {
        const listManager = new ListManager({
            controller: 'projects',
            url: urlbase + 'backend/projects/showAll',
            rootId: 'projects-list',
            containerId: 'showAll-projects-container',
            searchFields: ['name'],
            searchDates: ['created_at', 'updated_at']
        }, {
            onShowAfter: data => {
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
            controller: 'projects',
            urls: {
                softDelete: urlbase + 'backend/projects/softDelete',
                restoreDelete: urlbase + 'backend/projects/restoreDelete',
                hardDelete: urlbase + 'backend/projects/hardDelete'
            },
            listManager: listManager
        }).init();

        initRangeDatePicker('#wrapper-projects-created_at-from', '#wrapper-projects-created_at-to');
        initRangeDatePicker('#wrapper-projects-updated_at-from', '#wrapper-projects-updated_at-to');
        initOffcanvasAutoClose('projects-actionsOffcanvas');
    },

    add: function() {
        new AddManager({
            url: urlbase + 'backend/projects/add',
            formSelector: '#projects-add',
            resetSelector: '#add-reset',
            containerId: 'add-projects-container'
        }).init();
    },

    edit: function() {
        new EditManager({
            url: urlbase + 'backend/projects/edit',
            formSelector: '#projects-edit',
            refreshSelector: '#edit-refresh',
            containerId: 'edit-projects-container'
        }).init();
        initPanels();
    },

    show: function() {
        initPanels();
    }
};

function initPanels() {
    new GeneralDataManager({ url: urlbase + 'backend/projects/getGeneralData', formSelector: '#getGeneralData' }).init();
    new MetaDataManager({ url: urlbase + 'backend/projects/getMetaData', formSelector: '#getMetaData' }).init();
}

if (actions[action]) {
    actions[action]();
}
