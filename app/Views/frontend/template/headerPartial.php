<div>
    <h3 class="float-md-start mb-0"><?= esc($siteName); ?></h3>

    <nav class="nav nav-masthead justify-content-center float-md-end" <?= ui_binding('frontend/account.titles.index', 'aria-label'); ?> aria-label="<?= esc(lang('frontend/account.titles.index'), 'attr'); ?>">
        <?php if (($controller ?? '') === 'auth' && ($action ?? '') === 'setPassword'): ?>
            <a class="nav-link fw-bold py-1 px-0" href="<?= base_url('auth/login'); ?>"><?= ui_text('frontend/auth.titles.login'); ?></a>
        <?php else: ?>
            <?php $isHome = ($controller ?? '') === 'home'; ?>
            <?php $isPage = ($controller ?? '') === 'pages'; ?>
            <?php $isContact = ($controller ?? '') === 'contacts'; ?>
            <?php $isPublicPage = $isHome || $isPage || $isContact; ?>
            <a class="nav-link fw-bold py-1 px-0<?= $isHome ? ' active' : ''; ?>"<?= $isHome ? ' aria-current="page"' : ''; ?> href="<?= base_url(); ?>">Home</a>
            <?php if ($isPublicPage): ?>
                <?php $pages = config(\Config\Frontend\Pages::class); ?>
                <?php if ($pages->enabled): ?>
                    <?php foreach ($pages->items as $slug => $page): ?>
                        <?php if (($page['menu'] ?? false) === true && service('routes')->reverseRoute('frontend-page-' . $slug) !== false): ?>
                            <?php $active = $isPage && ($action ?? '') === $slug; ?>
                            <a class="nav-link fw-bold py-1 px-0<?= $active ? ' active' : ''; ?>"<?= $active ? ' aria-current="page"' : ''; ?> href="<?= esc(site_url($slug), 'attr'); ?>"><?= ui_text($page['title'], [], true); ?></a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
                <?php if (config(\Config\Frontend\Contacts::class)->enabled): ?>
                    <a class="nav-link fw-bold py-1 px-0<?= $isContact ? ' active' : ''; ?>"<?= $isContact && ($action ?? '') === 'index' ? ' aria-current="page"' : ''; ?> href="<?= esc(site_url('contacts'), 'attr'); ?>"><?= ui_text('frontend/contacts.titles.index', [], true); ?></a>
                <?php endif; ?>
            <?php endif; ?>
            <?php if ($currentUser): ?>
                <?php $isAccount = ($controller ?? '') === 'account'; ?>
                <a class="nav-link fw-bold py-1 px-0 d-flex align-items-center gap-2<?= $isAccount ? ' active' : ''; ?>"<?= ($isAccount && ($action ?? '') === 'index') ? ' aria-current="page"' : ''; ?> href="<?= base_url('account'); ?>">
                    <img class="frontend-nav-avatar<?= empty($avatarUrl) ? ' d-none' : ''; ?>" data-account-avatar
                         <?php if ( ! empty($avatarUrl)): ?>src="<?= esc($avatarUrl, 'attr'); ?>"<?php endif; ?> width="25" height="25" alt="">
                    <i class="fa-solid fa-circle-user frontend-nav-avatar<?= ! empty($avatarUrl) ? ' d-none' : ''; ?>" data-account-avatar-placeholder aria-hidden="true"></i>
                    <span><?= esc(trim($currentUser->firstname . ' ' . $currentUser->lastname)); ?></span>
                </a>
                <form method="post" action="<?= base_url('auth/logout'); ?>" class="m-0 d-flex align-items-center" data-unsaved-navigation>
                    <?= csrf_field(); ?>
                    <button type="submit" class="nav-link fw-bold py-1 px-0 ms-3"><?= ui_text('backend/global.menu.topRight.logout'); ?></button>
                </form>
            <?php elseif ($isPublicPage): ?>
                <?php if (config(\Config\Frontend\Auth::class)->enabled): ?>
                    <a class="nav-link fw-bold py-1 px-0" href="<?= base_url('auth/login'); ?>"><?= ui_text(config(\Config\Frontend\Auth::class)->registration ? 'frontend/auth.links.accessOrRegister' : 'frontend/auth.buttons.login'); ?></a>
                <?php endif; ?>
            <?php else: ?>
                <a class="nav-link fw-bold py-1 px-0<?= (($action ?? '') === 'login') ? ' active' : ''; ?>" href="<?= base_url('auth/login'); ?>"><?= ui_text('frontend/auth.titles.login'); ?></a>
                <a class="nav-link fw-bold py-1 px-0<?= (($action ?? '') === 'resetPassword') ? ' active' : ''; ?>" href="<?= base_url('auth/resetPassword'); ?>"><?= ui_text('frontend/auth.titles.resetPassword'); ?></a>
                <?php if (config(\Config\Frontend\Auth::class)->registration): ?>
                    <a class="nav-link fw-bold py-1 px-0<?= (($action ?? '') === 'register') ? ' active' : ''; ?>" href="<?= base_url('auth/register'); ?>"><?= ui_text('frontend/auth.buttons.register'); ?></a>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
        <?= $this->include('frontend/template/languagePartial'); ?>
    </nav>
</div>
