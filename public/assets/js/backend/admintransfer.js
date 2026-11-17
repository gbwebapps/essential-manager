/* Inizializzazioni dei componenti del pacchetto AdminTransfer. */
import { action } from './backend.js';
import { AdminTransferImportManager, AdminTransferExportManager } from './modules/AdminTransfer.js';

/* Mappa delle inizializzazioni specifiche per ogni pagina del modulo. */
const actions = {
    index: function() {
        new AdminTransferExportManager().init();
        new AdminTransferImportManager().init();
    },
};

if (actions[action]) {
    actions[action]();
}
