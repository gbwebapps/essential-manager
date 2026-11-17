<?= $this->extend('backend/template/mainView'); ?>

<?= $this->section('content'); ?>

<div id="messages-list" class="messages crud-list">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-12 col-md-1 mb-3 mb-md-0">
                            <select class="form-select changeNumRows" data-native-select aria-label="<?= esc(lang('backend/messages.labels.rows'), 'attr'); ?>" <?= ui_binding('backend/messages.labels.rows', 'aria-label'); ?>>
                                <?php foreach ([5, 10, 15, 20] as $rows): ?>
                                    <option value="<?= $rows; ?>"><?= $rows; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 d-md-none mb-3">
                            <button type="button" class="btn app-btn-secondary w-100" data-bs-toggle="offcanvas" data-bs-target="#messages-actionsOffcanvas" aria-controls="messages-actionsOffcanvas">
                                <i class="fa-solid fa-bars" aria-hidden="true"></i> <?= ui_text('backend/messages.labels.filters', [], true); ?>
                            </button>
                        </div>
                        <div class="col-12 col-md-11 offcanvas-md offcanvas-bottom" tabindex="-1" id="messages-actionsOffcanvas" aria-labelledby="messages-actions-title">
                            <div class="offcanvas-header">
                                <h2 class="offcanvas-title h5" id="messages-actions-title"><?= ui_text('backend/messages.labels.filters', [], true); ?></h2>
                                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#messages-actionsOffcanvas" aria-label="<?= esc(lang('backend/global.buttons.close'), 'attr'); ?>" <?= ui_binding('backend/global.buttons.close', 'aria-label'); ?>></button>
                            </div>
                            <div class="offcanvas-body pt-md-2">
                                <div class="d-flex flex-column flex-md-row flex-md-wrap align-items-stretch align-items-md-center justify-content-md-end w-100 gap-0 gap-md-3">
                                    <div class="d-flex flex-column flex-md-row gap-0 gap-md-3 me-md-auto mb-3 mb-md-0 border-md-end pe-md-3">
                                        <?php foreach (['unread' => 'fa-envelope', 'read' => 'fa-envelope-open', 'archived' => 'fa-box-archive', 'all' => 'fa-list'] as $status => $icon): ?>
                                            <a href="#" class="cmd-link" data-trash-filter="<?= $status; ?>">
                                                <i class="fa-solid <?= $icon; ?>" aria-hidden="true"></i> <?= ui_text('backend/messages.statuses.' . $status, [], false); ?>
                                                <span class="badge app-bg-surface app-text-muted border rounded-1 ms-1" data-trash-count="<?= $status; ?>" hidden></span>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                    <a href="#" class="cmd-link link-search" data-bs-toggle="collapse" data-bs-target="#messages-search-bar" aria-controls="messages-search-bar" aria-expanded="false">
                                        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i> <?= ui_text('backend/messages.links.search', [], true); ?>
                                    </a>
                                    <a href="#" class="cmd-link link-reset-search"><i class="fa-solid fa-xmark" aria-hidden="true"></i> <?= ui_text('backend/messages.links.resetSearch', [], true); ?></a>
                                    <a href="#" class="cmd-link reset-sorting-link"><i class="fa-solid fa-sort" aria-hidden="true"></i> <?= ui_text('backend/messages.links.resetSorting', [], true); ?></a>
                                    <a href="#" class="cmd-link refresh-list"><i class="fa-solid fa-arrows-rotate" aria-hidden="true"></i> <?= ui_text('backend/messages.links.reload', [], true); ?></a>
                                    <?php if (\App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, 'messages_export')): ?>
                                        <a href="#" class="cmd-link" id="export-entity" data-export-entity="contact_messages"><i class="fa-solid fa-file-export" aria-hidden="true"></i> <?= ui_text('backend/components/export.links.export', [], true); ?></a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="messages-search-bar" class="collapse search-bar">
                    <div class="row card-body">
                        <?php foreach (['name' => 'sender', 'email' => 'email'] as $field => $label): ?>
                            <div class="col-md-3">
                                <div class="mb-2 mb-md-0">
                                    <label for="messages-<?= $field; ?>"><?= ui_text('backend/messages.labels.' . $label, [], false); ?></label>
                                    <div class="input-group">
                                        <input <?= ui_binding('backend/messages.placeholders.' . $label, 'placeholder', [], true); ?> type="text" id="messages-<?= $field; ?>" class="form-control" placeholder="<?= esc(lang('backend/messages.placeholders.' . $label), 'attr'); ?>" data-search-field="<?= $field; ?>" autocomplete="off">
                                        <span class="input-group-text reset-search-field"><i class="fa-solid fa-times"></i></span>
                                    </div>
                                    <div data-search-error="<?= $field; ?>" class="error_<?= $field; ?> app-text-danger fw-bold small pt-1">&nbsp;</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php foreach (['from' => 'dateFrom', 'to' => 'dateTo'] as $bound => $label): ?>
                            <?php $field = 'created_at-' . $bound; ?>
                            <div class="col-md-3">
                                <div class="mb-2 mb-md-0">
                                    <label for="messages-<?= $field; ?>"><?= ui_text('backend/messages.labels.' . $label, [], false); ?></label>
                                    <div id="wrapper-messages-<?= $field; ?>" class="input-group">
                                        <input <?= ui_binding('backend/messages.placeholders.' . $label, 'placeholder', [], true); ?> type="text" id="messages-<?= $field; ?>" data-input class="form-control" placeholder="<?= esc(lang('backend/messages.placeholders.' . $label), 'attr'); ?>" data-search-field="<?= $field; ?>" autocomplete="off">
                                        <span class="input-group-text reset-search-field" data-clear><i class="fa-solid fa-times"></i></span>
                                        <span class="input-group-text" data-open><i class="fa-solid fa-calendar-days"></i></span>
                                    </div>
                                    <div data-search-error="<?= $field; ?>" class="error_<?= $field; ?> app-text-danger fw-bold small pt-1">&nbsp;</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div id="showAll-messages-container"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="export-modal-container"></div>

<?= $this->endSection(); ?>
