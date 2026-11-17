<!DOCTYPE html>
<html lang="<?= esc(service('request')->getLocale(), 'attr'); ?>">
<body>
    <p><?= esc($firstname . ' ' . $lastname); ?></p>
    <p><?= esc(lang('frontend/auth.email.registerSubject')); ?></p>
    <p><a href="<?= esc(base_url('auth/setPassword/' . $token), 'attr'); ?>"><?= esc(lang('frontend/auth.titles.setPassword')); ?></a></p>
</body>
</html>
