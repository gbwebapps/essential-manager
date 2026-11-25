<style>
    #manager-dashboard { margin-bottom: 1.5rem; color: var(--app-text); }
    .manager-desk { display: grid; gap: 1.25rem; align-items: start; }
    .manager-desk-split { grid-template-columns: minmax(0, 3fr) minmax(0, 1fr); }
    .manager-desk-activities, .manager-desk-studio { min-inline-size: 0; }
    .manager-desk :is(.manager-postit, .manager-desk-studio) a { color: inherit; text-decoration: none; }
    .manager-desk :is(.manager-postit, .manager-desk-studio) a:hover { text-decoration: underline; }
    .manager-desk :is(.manager-postit, .manager-desk-studio) a:focus-visible { outline: 2px solid currentColor; outline-offset: 3px; }
    .manager-desk-context { display: flex; align-items: start; gap: .35rem; font-size: .8rem; line-height: 1.6; overflow-wrap: anywhere; }
    .manager-desk-context > i { margin-top: .15rem; flex-shrink: 0; }
    .manager-desk-caption { display: block; font-size: .75rem; text-transform: uppercase; letter-spacing: .025em; margin-bottom: .3rem; }
    .manager-desk :is(.manager-postit, .manager-desk-studio) .task-steps-progress { inline-size: 100%; }
    .manager-desk :is(.manager-postit, .manager-desk-studio) .task-steps-progress .progress { background: #52616b25; block-size: .35rem; }
    .manager-desk :is(.manager-postit, .manager-desk-studio) .task-steps-progress .progress-bar { background: #617c88; }
    .manager-desk :is(.manager-postit, .manager-desk-studio) .task-steps-progress[data-steps-complete="true"] .progress-bar { background: var(--bs-success); }
    .manager-desk :is(.manager-postit, .manager-desk-studio) .task-steps-count { font-size: .75rem; }
    .manager-postits { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: .85rem; }
    .manager-desk-activities > .manager-postits { margin-top: 1rem; }
    .manager-postit { --paper: #d8e9f1; --fold: #b3d1df; color: #4c565e; position: relative; display: flex; flex-direction: column; min-inline-size: 0; padding: .9rem .8rem 1.35rem; background: var(--paper); border-top: 1px solid #ffffff65; box-shadow: 0 2px 4px #3a4a581c; }
    .manager-postit[data-paper="green"] { --paper: #dcebd8; --fold: #bad0b4; }
    .manager-postit[data-paper="rose"] { --paper: #f0dce2; --fold: #d9b5c0; }
    .manager-postit[data-paper="violet"] { --paper: #e4def1; --fold: #c9bde0; }
    .manager-postit::after { content: ''; position: absolute; top: 0; right: 0; inline-size: 1.65rem; block-size: 1.65rem; background: linear-gradient(45deg, var(--fold) 49%, var(--app-page-bg) 51%); filter: drop-shadow(-1px 1px 1px #50607018); pointer-events: none; }
    .manager-postit-date { display: flex; align-items: center; gap: .35rem; margin-bottom: .65rem; font-size: .75rem; }
    .manager-postit h3 { margin: 0 0 .5rem; font-size: .9rem; line-height: 1.6; font-weight: 700; overflow-wrap: anywhere; }
    .manager-postit .manager-desk-context { margin-bottom: .65rem; font-size: .75rem; }
    .manager-postit > .task-status { align-self: start; }
    .manager-postit-description { font-size: .75rem; line-height: 1.75; margin: .75rem 0 0; overflow-wrap: anywhere; }
    .manager-postit-next { margin-top: auto; padding-top: 1rem; }
    .manager-postit-next p { margin: 0; font-size: .75rem; line-height: 1.65; overflow-wrap: anywhere; }
    .manager-postit-progress { margin-top: .85rem; }
    .manager-postit-footer { display: flex; justify-content: space-between; align-items: center; gap: .35rem; border-top: 1px solid #6c7d872b; margin-top: .9rem; padding-top: .65rem; font-size: .75rem; }
    .manager-postit-notes { display: inline-flex; align-items: center; gap: .3rem; font-weight: 700; }
    .manager-postit-actions { display: flex; align-items: center; flex-wrap: wrap; justify-content: end; gap: .65rem; margin-inline-start: auto; }
    /* Agenda Studio: pannelli compatti con identità salvia, isolati dalla griglia post-it. */
    .manager-desk-studio {
        --studio-ink: #355345;
        --studio-line: color-mix(in srgb, #8da594 60%, var(--app-border));
        border: 1px solid var(--studio-line); border-radius: .35rem;
        background: color-mix(in srgb, #e1ebe4 15%, var(--app-surface));
    }
    .manager-studio-header { display: flex; align-items: center; gap: .65rem; padding: .85rem; border-bottom: 1px solid var(--studio-line); background: #c6d8ca; color: var(--studio-ink); border-radius: .3rem .3rem 0 0; }
    .manager-studio-emblem { display: inline-flex; align-items: center; justify-content: center; inline-size: 2rem; block-size: 2rem; flex-shrink: 0; border-radius: .35rem; background: #ffffff60; font-size: 1.1rem; }
    .manager-studio-header h2 { margin: 0; font-size: 1.05rem; font-weight: 700; }
    .manager-studio-count.badge { margin-inline-start: auto; color: var(--studio-ink); background: #edf4ed; border: 1px solid #8da594; font-size: .8rem; font-weight: 700; }
    .manager-studio-toolbar { display: flex; justify-content: space-between; gap: .5rem; padding: .65rem .85rem; font-size: .75rem; }
    .manager-studio-agenda { display: grid; gap: .65rem; padding: 0 .65rem .65rem; }
    .manager-studio-agenda:empty { display: none; }
    .manager-studio-task { min-inline-size: 0; padding: .8rem; border: 1px solid color-mix(in srgb, var(--app-border) 45%, transparent); border-radius: .3rem; background: color-mix(in srgb, #eef3ee 15%, var(--app-surface)); box-shadow: 0 1px 2px #354a3c0b; }
    .manager-studio-current { border-color: #95ad9c; background: color-mix(in srgb, #d3e3d5 65%, var(--app-surface)); }
    .manager-studio-task time { display: block; font-size: .7rem; opacity: .8; }
    .manager-studio-task h3 { font-size: .875rem; font-weight: 700; line-height: 1.6; margin: .45rem 0 .5rem; overflow-wrap: anywhere; }
    .manager-studio-current-label { display: flex; align-items: center; gap: .4rem; margin-bottom: .45rem; color: #355345; font-size: .75rem; font-weight: 700; }
    .manager-studio-current-sticky { position: sticky; top: var(--studio-sticky-top); z-index: 2; align-self: start; box-shadow: 0 4px 12px #354a3c26; }
    .manager-studio-next { display: grid; grid-template-columns: auto minmax(0, 1fr); align-items: start; gap: .5rem; margin-top: .7rem; }
    .manager-studio-next > i { margin-top: .15rem; font-size: .8rem; color: #54725e; }
    .manager-studio-caption { display: block; margin-bottom: .2rem; font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .025em; }
    .manager-studio-next p { margin: 0; font-size: .75rem; line-height: 1.6; overflow-wrap: anywhere; }
    .manager-studio-task-footer { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: .55rem; margin-top: .75rem; }
    .manager-studio-progress { flex: 1; min-inline-size: 6rem; }
    .manager-studio-task-links { display: flex; align-items: center; flex-wrap: wrap; justify-content: end; gap: .6rem; margin-inline-start: auto; }
    .manager-studio-task-links > a { font-size: .75rem; white-space: nowrap; }
    .manager-studio-notebook { margin: .15rem .65rem .65rem; padding: .75rem; border: 1px solid var(--studio-line); border-radius: .3rem; background: color-mix(in srgb, #e1ebe4 40%, var(--app-surface)); }
    .manager-studio-notebook-heading { display: flex; align-items: center; gap: .5rem; }
    .manager-studio-notebook-heading > i { color: #54725e; font-size: 1rem; }
    .manager-studio-notebook-heading h3 { flex: 1; min-inline-size: 0; margin: 0; font-size: .8rem; font-weight: 700; line-height: 1.5; overflow-wrap: anywhere; }
    .manager-studio-note-count.badge { flex-shrink: 0; color: #146c43; background: #dceee3; border: 1px solid #a3cfbb; font-size: .8rem; font-weight: 700; }
    .manager-studio-notebook > a { display: block; margin-top: .6rem; font-size: .75rem; text-align: end; }
    .manager-studio-footer { display: flex; justify-content: end; padding: .7rem .85rem; border-top: 1px solid var(--studio-line); font-size: .75rem; }
    .manager-desk-empty { padding: 1rem; margin: 0; text-align: center; font-size: .875rem; font-weight: 700; }
    @media (max-width: 1199.98px) { .manager-postit { padding-inline: .65rem; } .manager-postit-footer { flex-wrap: wrap; } }
    @media (max-width: 991.98px) { .manager-desk-split { grid-template-columns: minmax(0, 1fr); } }
    @media (max-width: 767.98px) { .manager-postits { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 399.98px) { .manager-postits { grid-template-columns: minmax(0, 1fr); } }
</style>
