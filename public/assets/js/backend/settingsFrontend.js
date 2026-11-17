/* Import delle costanti e utility da backend.js */
import { action } from './backend.js';

/* Import del gestore della sezione */
import { SettingsFrontendManager } from './modules/SettingsFrontend.js';

const actions = {
    index: function() {
        const root = document.querySelector('#mainSettingsDashboard[data-frontend-settings]');
        if (root) new SettingsFrontendManager(root).init();
    }
};

if (actions[action]) {
    actions[action]();
} else {
    console.error('Azione Settings Frontend non definita:', action);
}
