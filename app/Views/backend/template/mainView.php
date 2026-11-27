<!DOCTYPE html>
<html lang="<?= esc(backend_language()) ?>" data-backend-font="<?= esc(backend_font(), 'attr'); ?>" style="--backend-font: <?= esc(\App\Libraries\Backend\InterfacePreferences::FONTS[backend_font()], 'attr'); ?>;">

<head>
    <!-- Configurazione meta e charset -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta id="csrf-meta" name="<?= csrf_token(); ?>" content="<?= csrf_hash(); ?>">

    <link rel="icon" type="image/png" sizes="96x96" href="<?= base_url('assets/img/favicon.png'); ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= base_url('assets/img/apple-touch-icon.png'); ?>">

    <!-- Titolo della pagina dinamico -->
    <title <?= ui_value_binding($title, 'text', ' | ' . $siteName); ?>><?= esc($title); ?> | <?= esc($siteName); ?></title>

    <?php /* Ciclo per l'inclusione dei file CSS specifici della pagina */ ?>
    <?php if(isset($assets['css'])): foreach($assets['css'] as $css): ?>
        <link id="<?= esc($css['id']); ?>" rel="stylesheet" href="<?= base_url($css['path']); ?>">
    <?php endforeach; endif; ?>
</head>

<body>
    <!-- Overlay per backdrop personalizzati (es. loader o modali) -->
    <div id="customBackdrop"></div>

    <!-- Sezione Navbar Superiore -->
    <div id="navbar-top-view">
        <?= $this->include('backend/template/navbarTopView'); ?>
    </div>

    <!-- Hook dopo la navbar superiore, fuori dal blocco centrale -->
    <?= $this->renderSection('afterNavbarTopView'); ?>

    <!-- Contenitore per il loader di caricamento AJAX -->
    <div id="show-loader"></div>

    <!-- Contenitore globale per le notifiche Toast di Bootstrap -->
    <div class="alert-container position-fixed top-0 start-50 translate-middle-x p-3" id="alert-container"></div>

    <main class="container-fluid">
        <div class="row">
            <!-- Colonna principale Mobile First -->
            <div class="col-12"> 

                <!-- Hook prima della testata della sezione -->
                <?= $this->renderSection('beforeSectionView'); ?>

                <!-- Inclusione testata della sezione corrente -->
                <div id="section-view">
                    <?= $this->include('backend/template/sectionView'); ?>
                </div>

                <?= $this->renderSection('afterSectionView'); ?>

                <?= $this->renderSection('beforeLinksBarView'); ?>

                <!-- Inclusione barra dei link di navigazione interna -->
                <div id="links-bar-view">
                    <?= $this->include('backend/template/linksBarView'); ?>
                </div>

                <?= $this->renderSection('afterLinksBarView'); ?>

                <!-- Punto di iniezione del contenuto specifico della vista -->
                <div id="content-body-wrapper" 
                    class="<?= (isset($centerContent) && $centerContent) ? 'd-flex flex-column justify-content-center w-100' : ''; ?>" 
                    style="<?= (isset($centerContent) && $centerContent) ? 'min-height: 65dvh;' : ''; ?>">
                    <?= $this->renderSection('beforeContent'); ?>
                    <?= $this->renderSection('content'); ?>
                    <?= $this->renderSection('afterContent'); ?>
                </div>
                
            </div>
        </div>
    </main>

    <!-- Hook prima della navbar inferiore, fuori dal blocco centrale -->
    <?= $this->renderSection('beforeNavbarBottomView'); ?>

    <!-- Sezione Navbar Inferiore -->
    <div id="navbar-bottom-view">
        <?= $this->include('backend/template/navbarBottomView'); ?>
    </div>

    <!-- Pulsante per lo scroll rapido verso l'alto -->
    <button type="button" class="scrollup fade-out btn app-btn-secondary btn-sm">
        <i class="fa-solid fa-arrow-circle-up"></i> 
        <span id="scroll-up-text"><?= ui_text('backend/global.buttons.backToTop', [], false); ?></span>
    </button>

    <?php /* Passaggio del nome controller al comparto Javascript */ ?>
    <?php if(isset($controller)): ?>
        <div id="controller" data-controller="<?= esc($controller); ?>"></div>
    <?php endif; ?>

    <!-- Passaggio del nome action al comparto Javascript -->
    <?php if(isset($action)): ?>
        <div id="action" data-action="<?= esc($action); ?>"></div>
    <?php endif; ?>

    <!-- URL base del sito per le chiamate AJAX in JS -->
    <div id="hidden-urlbase" data-urlbase="<?= base_url(); ?>"></div>

    <!-- Ciclo per l'inclusione dei file Javascript con supporto ai moduli ES6 -->
    <?php if(isset($assets['js'])): foreach($assets['js'] as $js): ?>
        <script 
            <?= ($js['isModule'] ?? false) ? 'type="module"' : 'type="text/javascript"'; ?> 
            src="<?= base_url($js['path']); ?>">
        </script>
    <?php endforeach; endif; ?>

    <!-- ######################### MODALE ALERT ######################### -->
    <div id="modal-alert-view">
        <?= $this->include('backend/template/alertView', $this->data); ?>
    </div>
    <!-- ######################### FINE MODALE ALERT ######################### -->

    <!-- ######################### DEFINIZIONE LINGUE ######################### -->
    <div id="lang-definitions-view">
        <?= $this->include('backend/template/langDefinitionsView', $this->data); ?>
    </div>
    <!-- ######################### FINE DEFINIZIONE LINGUE ######################### -->
    
</body>

</html>
