<?= $this->extend(\Modules\Backend\Template\Config\Views::MAIN_VIEW); ?>
<?= $this->section('beforeSectionView'); ?>
    <?= $this->include(\Modules\Backend\Studio\Config\Views::STYLE_PARTIAL); ?>
<?= $this->endSection(); ?>
<?= $this->section('beforeLinksBarView'); ?>
    <?php if ($canReadTasks): ?>
        <?= $this->include(\Modules\Backend\Tasks\Config\Views::SECTION_STYLE_PARTIAL); ?>
        <?= $this->include(\Modules\Backend\Tasks\Config\Views::OVERVIEW_STYLE_PARTIAL); ?>
        <div id="studio-overview-container" class="mb-3" <?= $studioTab!=='tasks'?'hidden':''; ?>><?= $studioOverview; ?></div>
    <?php endif; ?>
<?= $this->endSection(); ?>
<?= $this->section('content'); ?>
    <?= $this->include(\Modules\Backend\Tasks\Config\Views::ACTION_LINKS_STYLE_PARTIAL); ?>
    <?= $this->include(\Modules\Backend\Tasks\Config\Views::SECTION_STYLE_PARTIAL); ?>
    <?= $this->include(\Modules\Backend\Tasks\Config\Views::STEPS_STYLE_PARTIAL); ?>
    <div id="studio-workspace" data-task-route-base="backend/studio/tasks/" data-save-error="<?= esc(lang('backend/tasks.messages.saveError'),'attr'); ?>">
        <div class="studio-tabs nav" role="tablist" aria-label="<?= esc(lang('backend/studio.title'),'attr'); ?>">
            <?php foreach (['tasks'=>$canReadTasks,'notes'=>$canReadNotes] as $kind=>$allowed): if (!$allowed): continue; endif; ?>
                <button type="button" class="studio-tab nav-link <?= $studioTab===$kind?'active':''; ?>" id="studio-<?= $kind; ?>-tab" data-bs-toggle="tab" data-bs-target="#studio-<?= $kind; ?>" role="tab" aria-controls="studio-<?= $kind; ?>" aria-selected="<?= $studioTab===$kind?'true':'false'; ?>"><i class="fa-solid fa-<?= $kind==='tasks'?'list-check':'note-sticky'; ?> me-2" aria-hidden="true"></i><?= ui_text('backend/studio.'.$kind,[],false); ?> <span class="badge <?= $kind==='notes'?'task-notes-count':'app-bg-muted-subtle app-text-muted-emphasis border rounded-1'; ?>" data-studio-<?= $kind==='tasks'?'task':'note'; ?>-count><?= $kind==='tasks'?$taskCount:$noteCount; ?></span></button>
            <?php endforeach; ?>
        </div>
        <div class="tab-content">
            <?php foreach (['tasks'=>$canReadTasks,'notes'=>$canReadNotes] as $kind=>$allowed): if (!$allowed): continue; endif; ?>
                <section id="studio-<?= $kind; ?>" data-studio-list="<?= $kind; ?>" class="tab-pane <?= $studioTab===$kind?'show active':''; ?>" role="tabpanel" aria-labelledby="studio-<?= $kind; ?>-tab">
                    <form novalidate class="row studio-list-controls" data-studio-search-form>
                        <div class="col col-md-3">
                            <label class="form-label" for="studio-<?= $kind; ?>-search"><?= ui_text('backend/studio.'.($kind==='tasks'?'searchTasks':'searchNotes'),[],false); ?></label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="studio-<?= $kind; ?>-search" data-studio-search autocomplete="off" <?= ui_binding('backend/studio.'.($kind==='tasks'?'searchTasks':'searchNotes'),'placeholder',[],true); ?> placeholder="<?= esc(lang('backend/studio.'.($kind==='tasks'?'searchTasks':'searchNotes')),'attr'); ?>">
                                <button type="button" class="input-group-text reset-search-field" <?= ui_binding('backend/studio.clearSearch','aria-label',[],true); ?> aria-label="<?= esc(lang('backend/studio.clearSearch'),'attr'); ?>" title="<?= esc(lang('backend/studio.clearSearch'),'attr'); ?>"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
                            </div>
                            <div class="small app-text-danger fw-bold pt-1" data-studio-search-error aria-live="polite">&nbsp;</div>
                        </div>
                        <div class="col-auto ms-auto text-end align-self-center"><button type="button" class="btn btn-link task-record-link" data-studio-refresh <?= ui_binding('backend/tasks.links.reloadList','aria-label',[],true); ?> aria-label="<?= esc(lang('backend/tasks.links.reloadList'),'attr'); ?>" title="<?= esc(lang('backend/tasks.links.reloadList'),'attr'); ?>"><i class="fa-solid fa-arrows-rotate" aria-hidden="true"></i></button><button type="button" class="btn btn-link task-record-link" data-studio-sort data-newest="<?= esc(lang('backend/studio.newest'),'attr'); ?>" data-oldest="<?= esc(lang('backend/studio.oldest'),'attr'); ?>" title="<?= esc(lang('backend/studio.oldest'),'attr'); ?>" aria-label="<?= esc(lang('backend/studio.oldest'),'attr'); ?>"><i class="fa-solid fa-arrow-down-wide-short" aria-hidden="true"></i></button></div>
                    </form>
                    <div class="studio-results" data-studio-results aria-live="polite"></div>
                </section>
            <?php endforeach; ?>
        </div>
    </div>
    <?= $this->include(\Modules\Backend\Studio\Config\Views::MODALS_PARTIAL); ?>
    <?= view(\Modules\Backend\Tasks\Config\Views::NOTE_MODAL_PARTIAL,['task'=>null],['saveData'=>false]); ?>
<?= $this->endSection(); ?>
