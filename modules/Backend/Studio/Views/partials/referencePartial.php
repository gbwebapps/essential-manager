<?php
    $reference = $task->reference ?? null;
    $canFollow = $reference && \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin ?? null, ($reference->kind === 'project' ? 'projects' : 'applications') . '_show');
?>
<?php if ($canFollow): ?>
    <?php $referenceUrl = base_url('backend/' . ($reference->kind === 'project' ? 'projects' : 'applications') . '/show/' . ($reference->project_uuid ?? $reference->application_uuid)); ?>
    <span class="d-inline-flex align-items-center gap-1 small">
        <i class="fa-solid fa-link" aria-hidden="true"></i>
        <?php if ($reference->deleted_at === null): ?><a class="text-decoration-none" href="<?= esc($referenceUrl, 'attr'); ?>"><?= esc($reference->name); ?></a><?php else: ?><span><?= esc($reference->name); ?></span><?php endif; ?>
    </span>
<?php endif; ?>
