<?php $colorClass = match ($status) {
    'unread' => 'app-bg-warning',
    'read' => 'app-bg-success',
    'archived' => 'messages-status-archived',
}; ?>
<span class="badge rounded-1 <?= $colorClass; ?>" data-message-status="<?= esc($status, 'attr'); ?>"<?= isset($currentStatus) && $currentStatus !== $status ? ' hidden' : ''; ?>>
    <?= ui_text('backend/messages.stateLabels.' . $status, [], true); ?>
</span>
