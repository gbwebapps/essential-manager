<!-- Struttura principale del form di inserimento -->
<div class="row">
    <div class="col-12 col-lg-8 offset-lg-2">
        <div class="card">
            <form novalidate id="tasks-add" data-unsaved-check>
                <div id="generalData">
                    <div class="card-header">
                        <h2 class="card-title text-center text-lg-start mb-0"><?= ui_text('backend/tasks.panels.generalData', [], false); ?></h2>
                    </div>
                    <?= view(\Modules\Backend\Tasks\Config\Views::FORM_PARTIAL, ['task' => null, 'ownerOptions' => $ownerOptions, 'currentAdmin' => $currentAdmin]); ?>
                </div>
            </form>


        </div>
    </div>
</div>
