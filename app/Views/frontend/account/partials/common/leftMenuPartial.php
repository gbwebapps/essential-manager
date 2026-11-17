<nav class="account-navigation text-start" <?= ui_binding('frontend/account.titles.index', 'aria-label'); ?> aria-label="<?= esc(lang('frontend/account.titles.index'), 'attr'); ?>">
    <?php foreach ($sections as $key => $section): ?>
        <a href="<?= base_url($section['route']); ?>" class="<?= ($key === 'deleteAccount') ? 'account-removal-link' : ''; ?><?= ($action === $key) ? ' active fw-bold' : ''; ?>"<?= ($action === $key) ? ' aria-current="page"' : ''; ?>>
            <span><?= $section['icon']; ?> <?= ui_text('frontend/account.leftMenu.' . $key, [], true); ?></span>
            <?php if ($action === $key): ?>
                <i class="fa-solid fa-caret-right flex-shrink-0 text-end" aria-hidden="true"></i>
            <?php endif; ?>
        </a>
    <?php endforeach; ?>
</nav>
