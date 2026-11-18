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

    complete: function() {
        // TODO: implementare.
    },

    reopen: function() {
        // TODO: implementare.
    },

    getSteps: function() {
        // TODO: implementare.
    },

    addStep: function() {
        // TODO: implementare.
    },

    editStep: function() {
        // TODO: implementare.
    },

    deleteStep: function() {
        // TODO: implementare.
    },

    reorderSteps: function() {
        // TODO: implementare.
    },

    completeStep: function() {
        // TODO: implementare.
    },

    reopenStep: function() {
        // TODO: implementare.
    },

    getCurrent: function() {
        // TODO: implementare.
    },

    setCurrent: function() {
        // TODO: implementare.
    },

    pauseCurrent: function() {
        // TODO: implementare.
    },

    resumeCurrent: function() {
        // TODO: implementare.
    },
};

if (typeof actions[action] === 'function') {
    actions[action]();
}
