<!-- Controlli condivisi con i manager frontend: identita e richieste restano separate dal backend. -->
<div id="customBackdrop"></div>
<div id="show-loader" role="status" aria-live="polite"></div>
<div class="alert-container position-fixed top-0 start-50 translate-middle-x p-3" id="alert-container" aria-live="polite"></div>
<div id="controller" data-controller="<?= esc($controller, 'attr'); ?>"></div>
<div id="action" data-action="<?= esc($action, 'attr'); ?>"></div>
<div id="hidden-urlbase" data-urlbase="<?= esc(rtrim(base_url(), '/') . '/', 'attr'); ?>"></div>

<?= $this->include('frontend/template/alertView'); ?>
<?= $this->include('frontend/template/langDefinitionsView'); ?>

<script src="<?= base_url('assets/vendor/bootstrap/js/bootstrap.bundle.min.js'); ?>"></script>
<script src="<?= base_url('assets/vendor/tom-select/js/tom-select.complete.min.js'); ?>"></script>
<?php if ($controller !== 'home' && $controller !== 'pages'): ?>
<script type="module" src="<?= base_url('assets/js/frontend/' . ucfirst($controller) . '.js'); ?>"></script>
<?php endif; ?>
<script type="module" src="<?= base_url('assets/js/frontend/Language.js'); ?>"></script>
