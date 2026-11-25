<div class="row">
    <div class="col-12 col-lg-8 offset-lg-2">
        <div class="card">
            <form novalidate id="checklists-add" data-unsaved-check>
                <div id="generalData">
                    <div class="card-header"><h2 class="card-title mb-0"><?= ui_text('backend/checklists.panels.generalData', [], false); ?></h2></div>
                    <?= view(\Modules\Backend\Checklists\Config\Views::FORM_PARTIAL, ['checklist' => null, 'currentAdmin' => $currentAdmin], ['saveData' => false]); ?>
                </div>
            </form>
        </div>
    </div>
</div>
