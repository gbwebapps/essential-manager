/* Struttura delle azioni del modulo; nessuna operazione ancora implementata. */
import { action } from './backend.js';

const actions = {
    index: function() {
        // TODO: implementare.
    },

    showAll: function() {
        // TODO: implementare.
    },

    show: function() {
        // TODO: implementare.
    },

    prepare: function() {
        // TODO: implementare.
    },

    check: function() {
        // TODO: implementare.
    },

    tests: function() {
        // TODO: implementare.
    },

    update: function() {
        // TODO: implementare.
    },

    commit: function() {
        // TODO: implementare.
    },

    push: function() {
        // TODO: implementare.
    },

    cloneApp: function() {
        // TODO: implementare.
    },

    getReport: function() {
        // TODO: implementare.
    },
};

if (typeof actions[action] === 'function') {
    actions[action]();
}
