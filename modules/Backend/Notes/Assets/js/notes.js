/* Struttura delle azioni del modulo; nessuna operazione ancora implementata. */
import { action } from './backend.js';

const actions = {
    index: function() {
        // TODO: implementare.
    },

    showAll: function() {
        // TODO: implementare.
    },

    add: function() {
        // TODO: implementare.
    },

    edit: function() {
        // TODO: implementare.
    },

    show: function() {
        // TODO: implementare.
    },
};

if (typeof actions[action] === 'function') {
    actions[action]();
}
