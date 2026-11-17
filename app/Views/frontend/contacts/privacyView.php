<?php if (is_file(APPPATH . 'Views/frontend/contacts/privacyNoticeLocal.php')): ?>
    <?= $this->include('frontend/contacts/privacyNoticeLocal'); ?>
<?php else: ?>
    <p><?= ui_text('frontend/contacts.privacy.description', [], true); ?></p>
    <p><?= ui_text('frontend/contacts.privacy.handling', [], true); ?></p>
<?php endif; ?>
<a class="btn btn-outline-light btn-sm mt-3" href="<?= esc(site_url('contacts'), 'attr'); ?>"><?= ui_text('frontend/contacts.privacy.back', [], true); ?></a>
