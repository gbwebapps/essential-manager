import { action, urlbase, initRangeDatePicker, initOffcanvasAutoClose } from './backend.js';
import { ListManager, ChangeStatusManager, DeleteManager, GeneralDataManager, MetaDataManager } from './modules/Crud.js';
import { ResetPasswordManager, GetTokensManager, DeleteTokenManager, RestoreRemovedManager } from './modules/Users.js';
import { ExportCsvManager } from './components/ExportCsv.js';
import { GalleryOneManager } from './components/GalleryOne.js';

/* Nessun AddManager, EditManager o ImportManager: l'anagrafica appartiene all'utente. */
if (action === 'showAll') {
    const list = new ListManager({
        controller: 'users', url: urlbase + 'backend/users/showAll', rootId: 'users-list',
        containerId: 'showAll-users-container', searchFields: ['firstname', 'lastname', 'email', 'phone'],
        searchDates: ['created_at', 'updated_at']
    }, {
        onShowAfter: data => {
            if (!data.trashCounts) return;
            document.querySelectorAll('#users-list [data-trash-count]').forEach(badge => {
                const count = data.trashCounts[badge.dataset.trashCount];
                if (Number.isSafeInteger(count) && count >= 0) {
                    badge.textContent = String(count);
                    badge.hidden = false;
                }
            });
        }
    });
    list.init();
    new RestoreRemovedManager(list).init();
    new DeleteManager({
        controller: 'users', urls: { hardDelete: urlbase + 'backend/users/hardDelete' },
        requirePassword: true, forcePassword: true, listManager: list
    }).init();
    initRangeDatePicker('#wrapper-users-created_at-from', '#wrapper-users-created_at-to');
    initRangeDatePicker('#wrapper-users-updated_at-from', '#wrapper-users-updated_at-to');
    new ChangeStatusManager({controller: 'users', requirePassword: true, url: urlbase + 'backend/users/changeStatus'}, {
        onStatusAfter: () => list.showAll()
    }).init();
    new ResetPasswordManager({onResetAfter: () => list.showAll()}).init();
    new ExportCsvManager({controller: 'users'}).init();
    initOffcanvasAutoClose('users-actionsOffcanvas');
}
if (action === 'show') {
    new GeneralDataManager({url: urlbase + 'backend/users/getGeneralData', formSelector: '#getGeneralData'}).init();
    new MetaDataManager({url: urlbase + 'backend/users/getMetaData', formSelector: '#getMetaData'}).init();
    new GalleryOneManager();
    new GetTokensManager().init();
    new DeleteTokenManager().init();
    new ChangeStatusManager({controller: 'users', requirePassword: true, url: urlbase + 'backend/users/changeStatus'}, {
        onStatusAfter: data => {
            if (data.statusView) document.getElementById('changeStatusPartial').innerHTML = data.statusView;
            if (data.metaView) document.getElementById('metaData').innerHTML = data.metaView;
        }
    }).init();
}
