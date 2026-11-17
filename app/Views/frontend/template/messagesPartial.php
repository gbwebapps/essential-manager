<?php if (session()->getFlashdata('message')): ?>
    <div class="alert alert-session <?= esc(session()->getFlashdata('class') ?? 'app-alert-info', 'attr'); ?>" role="alert">
        <?= esc(session()->getFlashdata('message')); ?>
    </div>
<?php endif; ?>
