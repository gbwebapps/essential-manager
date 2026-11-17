<?php $canShow = \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, 'messages_show'); ?>
<div class="card-body py-0">
    <?php if ($data['records'] === []): ?>
        <p class="text-center fw-bold py-3 mb-0"><?= ui_text('backend/messages.messages.empty', [], true); ?></p>
    <?php else: ?>
        <?= $this->include('backend/template/paginationView'); ?>
        <div class="table-responsive border-top">
            <table class="table table-condensed align-middle text-nowrap mb-0">
                <thead>
                    <tr class="sorting">
                        <th scope="col" style="width: 2.5%;" class="text-center">&nbsp;</th>
                        <?php foreach (['created_at' => 'receivedAt', 'name' => 'sender', 'email' => 'email', 'status' => 'status'] as $column => $label): ?>
                            <?php $sorted = $posts['column'] === $column; ?>
                            <th scope="col"<?= $sorted ? ' aria-sort="' . ($posts['order'] === 'desc' ? 'descending' : 'ascending') . '"' : ''; ?>>
                                <a href="#" class="sort" data-column="<?= $column; ?>" data-order="<?= $sorted && $posts['order'] === 'desc' ? 'asc' : 'desc'; ?>">
                                    <?= ui_text('backend/messages.labels.' . $label, [], true); ?>
                                    <?php if ($sorted): ?><i class="fa-solid fa-arrow-<?= $posts['order'] === 'desc' ? 'down' : 'up'; ?>" aria-hidden="true"></i><?php endif; ?>
                                </a>
                            </th>
                        <?php endforeach; ?>
                        <th scope="col" class="text-end"><?= ui_text('backend/messages.labels.actions', [], true); ?></th>
                    </tr>
                </thead>
                <tbody id="messagesBody">
                    <?php foreach ($data['records'] as $record): ?>
                        <?php $metadataId = 'meta-message-' . (int) $record['id']; ?>
                        <tr class="border-end border-start table-row-60<?= $record['status'] === 'unread' ? ' fw-bold' : ''; ?>">
                            <td class="text-center">
                                <button class="toggle-meta-btn btn btn-sm btn-link p-0 me-1 app-text shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $metadataId; ?>" aria-expanded="false" aria-controls="<?= $metadataId; ?>" <?= ui_binding('backend/messages.labels.showMetadata', 'title'); ?> <?= ui_binding('backend/messages.labels.showMetadata', 'aria-label'); ?> title="<?= esc(lang('backend/messages.labels.showMetadata'), 'attr'); ?>" aria-label="<?= esc(lang('backend/messages.labels.showMetadata'), 'attr'); ?>">
                                    <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                                </button>
                            </td>
                            <td><?= ui_date($record['created_at']); ?></td>
                            <td><?= esc($record['name']); ?></td>
                            <td><?= esc($record['email']); ?></td>
                            <td><?= view('backend/messages/partials/statusBadge', ['status' => $record['status']]); ?></td>
                            <td class="text-end">
                                <?php if ($canShow): ?>
                                    <a class="text-decoration-none badge app-bg-muted-subtle app-text-muted-emphasis action" href="<?= esc(site_url('backend/messages/show/' . $record['id']), 'attr'); ?>">
                                        <i class="fa-solid fa-envelope-open fa-fw" aria-hidden="true"></i> <?= ui_text('backend/messages.actions.show', [], true); ?>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr class="border-end border-start">
                            <td colspan="6" class="p-0 border-0">
                                <div class="collapse" id="<?= $metadataId; ?>">
                                    <div class="p-2 border-bottom app-bg-surface app-text small">
                                        <?= ui_text('backend/messages.labels.receivedAt', [], false); ?>
                                        <span class="fw-bold app-text-success"><?= ui_date($record['created_at'], 'conversational'); ?></span>
                                        <?php if ($record['read_at'] !== null): ?>
                                            &nbsp;&bull;&nbsp;
                                            <?= ui_text('backend/messages.labels.readAt', [], false); ?>
                                            <span class="fw-bold app-text-success"><?= ui_date($record['read_at'], 'conversational'); ?></span>
                                        <?php endif; ?>
                                        <?php if ($record['archived_at'] !== null): ?>
                                            &nbsp;&bull;&nbsp;
                                            <?= ui_text('backend/messages.labels.archivedAt', [], false); ?>
                                            <span class="fw-bold app-text-danger"><?= ui_date($record['archived_at'], 'conversational'); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?= $this->include('backend/template/paginationView'); ?>
    <?php endif; ?>
</div>
