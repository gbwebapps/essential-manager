<style>
    [data-tasks-overview].task-overview {
        --task-overview-border: #dcc685;
        color: #595044;
        background: #fff3cd;
        border-color: var(--task-overview-border);
        overflow: hidden;
    }
    .task-overview-grid { display: grid; grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); }
    .task-overview-focus, .task-overview-summary { min-inline-size: 0; padding: 1rem 1.25rem; }
    .task-overview-focus { display: flex; flex-direction: column; }
    .task-overview-heading { display: flex; flex-wrap: wrap; align-items: center; gap: .5rem; color: #79520c; font-size: .95rem; font-weight: 700; }
    .task-overview-heading > i { font-size: 1.2rem; margin-inline-end: .125rem; }
    .task-overview-state { margin-inline-start: auto; padding: .2rem .5rem; font-size: .8rem; border: 1px solid var(--task-overview-border); border-radius: .25rem; background: rgba(255, 255, 255, .5); }
    .task-overview-title { margin: .6rem 0 0; font-size: 1.3rem; font-weight: 700; line-height: 1.5; overflow-wrap: anywhere; }
    .task-overview-title a { color: inherit; text-decoration: none; }
    .task-overview-title a:hover { text-decoration: underline; }
    .task-overview-context { display: flex; flex-wrap: wrap; gap: .25rem 1rem; margin-block-start: .35rem; font-size: .95rem; overflow-wrap: anywhere; }
    .task-overview-context > span { min-inline-size: 0; }
    .task-overview-next { display: flex; align-items: flex-start; gap: .75rem; padding: .65rem .8rem; margin-block: .85rem; border: 1px solid rgba(121, 82, 12, .15); border-radius: .25rem; background: rgba(255, 255, 255, .45); }
    .task-overview-next > i { margin-block-start: .2rem; color: #79520c; font-size: 1.15rem; }
    .task-overview-caption { font-size: .8rem; font-weight: 700; }
    .task-overview-next-title { margin-block-start: .15rem; font-size: 1rem; line-height: 1.55; overflow-wrap: anywhere; }
    .task-overview-footer { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .6rem 1rem; margin-block-start: auto; padding-block-start: .25rem; }
    .task-overview-progress { display: flex; flex-wrap: wrap; align-items: center; gap: .5rem; }
    .task-overview-empty { margin-block-start: .6rem; font-size: 1rem; line-height: 1.6; }
    .task-overview-summary { background: rgba(255, 255, 255, .3); border-inline-start: 1px solid var(--task-overview-border); }
    .task-overview-summary-title { margin: 0 0 .75rem; font-size: 1rem; font-weight: 700; line-height: 1.5; }
    .task-overview-counts { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .6rem; }
    .task-overview-count { display: grid; grid-template-columns: auto 1fr; align-items: center; gap: .15rem .5rem; min-inline-size: 0; padding: .5rem .65rem; border: 1px solid rgba(121, 82, 12, .18); border-radius: .25rem; background: rgba(255, 255, 255, .4); }
    .task-overview-count > i { color: #79520c; font-size: 1rem; }
    .task-overview-count > strong { font-size: 1.35rem; font-weight: 700; line-height: 1.3; font-variant-numeric: tabular-nums; }
    .task-overview-count > span { grid-column: 1 / -1; font-size: .85rem; line-height: 1.4; overflow-wrap: anywhere; }
    .task-overview-remaining { margin-block-start: .65rem; padding-block-start: .6rem; border-top: 1px solid var(--task-overview-border); font-size: .85rem; line-height: 1.5; }
    @media (max-width: 767.98px) {
        .task-overview-grid { grid-template-columns: minmax(0, 1fr); }
        .task-overview-focus, .task-overview-summary { padding: .85rem 1rem; }
        .task-overview-summary { border-inline-start: 0; border-top: 1px solid var(--task-overview-border); }
        .task-overview-title { font-size: 1.15rem; }
    }
</style>
