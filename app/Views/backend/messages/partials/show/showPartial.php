<div class="row">
    <div class="col-12 col-lg-8 offset-lg-2">
        <div class="card" id="show-wrapper">
            <div class="card-header rounded-0 d-flex justify-content-between align-items-center">
                <h2 class="card-title text-start mb-0"><?= ui_text('backend/messages.panels.generalData', [], false); ?></h2>
                <form id="getMessageGeneralData">
                    <input type="hidden" name="id" value="<?= esc((string) $contactMessage['id'], 'attr'); ?>">
                    <button type="submit" class="btn btn-sm app-btn-secondary">
                        <?= view('components/panelReloadIcon', []); ?><?= ui_text('backend/admins.buttons.reload', [], false); ?>
                    </button>
                </form>
            </div>
            <div id="messageGeneralData">
                <?= $this->include('backend/messages/partials/show/generalDataPartial', $this->data); ?>
            </div>
        </div>
    </div>
</div>
