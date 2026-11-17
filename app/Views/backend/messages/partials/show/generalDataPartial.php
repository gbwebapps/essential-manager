<div class="card-body">
    <div class="row mb-3">
        <div class="col-12 col-lg-6">
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><i class="fa-solid fa-arrow-circle-down"></i><?= ui_text('backend/messages.labels.sender', [], false); ?></li>
                <li class="list-group-item fw-bold text-break"><?= esc($contactMessage['name']); ?></li>
            </ul>
        </div>
        <div class="col-12 col-lg-6">
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><i class="fa-solid fa-arrow-circle-down"></i><?= ui_text('backend/messages.labels.email', [], false); ?></li>
                <li class="list-group-item fw-bold text-break"><a class="cmd-link" href="<?= esc('mailto:' . $contactMessage['email'], 'attr'); ?>"><?= esc($contactMessage['email']); ?></a></li>
            </ul>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-12 col-lg-6">
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><i class="fa-solid fa-arrow-circle-down"></i><?= ui_text('backend/messages.labels.receivedAt', [], false); ?></li>
                <li class="list-group-item fw-bold"><?= ui_date($contactMessage['created_at']); ?></li>
            </ul>
        </div>
        <div class="col-12 col-lg-6">
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><i class="fa-solid fa-arrow-circle-down"></i><?= ui_text('backend/messages.labels.status', [], false); ?></li>
                <li class="list-group-item" aria-live="polite">
                    <?php foreach (\App\Models\ContactMessagesModel::STATUSES as $status): ?>
                        <?= view('backend/messages/partials/statusBadge', ['status' => $status, 'currentStatus' => $contactMessage['status']]); ?>
                    <?php endforeach; ?>
                </li>
            </ul>
        </div>
    </div>
    <?php if ( ! empty($contactMessage['phone'])): ?>
        <div class="row mb-3">
            <div class="col-12 col-lg-6">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><i class="fa-solid fa-arrow-circle-down"></i><?= ui_text('backend/messages.labels.phone', [], false); ?></li>
                    <li class="list-group-item fw-bold text-break"><?= esc($contactMessage['phone']); ?></li>
                </ul>
            </div>
        </div>
    <?php endif; ?>
    <?php if ($contactMessage['subject'] !== ''): ?>
        <div class="row">
            <div class="col-12">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><i class="fa-solid fa-arrow-circle-down"></i><?= ui_text('backend/messages.labels.subject', [], false); ?></li>
                    <li class="list-group-item fw-bold text-break"><?= esc($contactMessage['subject']); ?></li>
                </ul>
            </div>
        </div>
    <?php endif; ?>
</div>

<div class="card-header rounded-0 d-flex justify-content-between align-items-center" style="border-top: var(--bs-card-border-width) solid var(--bs-card-border-color);">
    <h2 class="card-title text-start mb-0"><?= ui_text('backend/messages.panels.body', [], false); ?></h2>
</div>
<div class="card-body text-break"><?= nl2br(esc($contactMessage['message'])); ?></div>

<?php if (\App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, 'messages_mark')): ?>
<div class="card-footer">
    <form id="message-status-form" method="post" action="<?= esc(site_url('backend/messages/changeStatus/' . $contactMessage['id']), 'attr'); ?>" class="d-flex flex-wrap justify-content-end gap-2" data-csrf-name="<?= esc(csrf_token(), 'attr'); ?>">
        <?= csrf_field(); ?>
        <input type="hidden" name="previousStatus" value="<?= esc($contactMessage['status'], 'attr'); ?>">
        <?php foreach (['unread' => 'fa-envelope', 'read' => 'fa-envelope-open', 'archived' => 'fa-box-archive'] as $status => $icon): ?>
            <button type="submit" name="status" value="<?= $status; ?>" class="btn btn-sm app-btn-secondary shadow-none"<?= $contactMessage['status'] === $status ? ' hidden' : ''; ?>>
                <i class="fa-solid <?= $icon; ?> me-1" aria-hidden="true"></i><?= ui_text('backend/messages.actions.' . $status, [], false); ?>
            </button>
        <?php endforeach; ?>
    </form>
</div>
<?php endif; ?>
