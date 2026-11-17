<?php
    $status = (int) $admin->status;

    if($status === 1):
        $statusText = lang('backend/users.labels.active');
        $statusClass = "app-text-success fw-bold";
    elseif($status === 0):
        $statusText = lang('backend/users.labels.unactive');
        $statusClass = "app-text-danger fw-bold";
    endif;
?>
<?php if (\App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, 'users_change_status')): ?>
<form <?= ui_binding('backend/users.messages.areYouSureChangeStatus', 'data-message', [$admin->firstname, $admin->lastname], true); ?> class="changeStatus" data-message="<?= esc(sprintf(lang('backend/users.messages.areYouSureChangeStatus'), $admin->firstname, $admin->lastname), 'attr'); ?>">
    <input type="hidden" name="uuid" value="<?= esc($admin->uuid, 'attr'); ?>">
    <input type="hidden" name="context" value="show">
    <button type="submit" class="<?= $statusClass; ?> btn btn-link shadow-none">
        <?= ui_value($statusText, false); ?>
    </button>
</form>
<?php else: ?>
    <span class="<?= $statusClass; ?>"><?= ui_value($statusText, false); ?></span>
<?php endif; ?>
