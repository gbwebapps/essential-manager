<!-- Barra di navigazione inferiore fissa (Footer Nav) -->
<nav class="navbar fixed-bottom app-bg-surface border-top">
    <div class="container-fluid d-flex align-items-center justify-content-between">

        <!-- Il contenuto viene renderizzato solo per utenti autenticati-->
        <?php if (isset($currentAdmin) && $currentAdmin): ?>

            <!-- Sezione Sinistra: Menu a comparsa verso l'alto (Moduli) -->
            <div class="btn-group dropup me-auto">
                <button type="button" class="btn app-btn-secondary dropdown-toggle btn-sm" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa-solid fa-cubes"></i> 
                    <span><?= ui_text('backend/global.buttons.modules', [], false); ?></span>
                </button>

                <ul class="dropdown-menu">
                <?php

                    $visibleLeftItems = [];
                    foreach ($menuBottomLeft as $item):
                        if (isset($item['permission']) && ! \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin, $item['permission'])):
                            continue;
                        endif;
                        $visibleLeftItems[] = $item;
                    endforeach;

                    /* Ciclo di generazione delle voci del menu moduli */
                    $totalLeft = count($visibleLeftItems);
                    foreach ($visibleLeftItems as $index => $ele): 

                        /* Controllo sicuro per evitare notice se il controller manca */
                        $eleController = $ele['controller'] ?? '';
                        $active = (isset($controller) && $controller === $eleController) ? ' active' : '';
                        
                        /* Gestione dinamica dell'indirizzo o dell'identificativo */
                        $href = isset($ele['route']) ? base_url($ele['route']) : '#'; 
                        $dataIdAttr = isset($ele['id']) ? ' data-id="' . esc($ele['id']) . '"' : '';
                ?>
                    <li>
                        <a class="dropdown-item<?= $active; ?><?= isset($menuCounts[$eleController]) ? ' d-flex align-items-center gap-3' : ''; ?>" href="<?= $href; ?>"<?= $dataIdAttr; ?>>
                            <span><span class="me-2"><?= $ele['icon'] ?? ''; ?></span> <?= ui_value($ele['label'] ?? '', false); ?></span>
                            <?= view('backend/template/menuCountsPartial', ['menuCountEntity' => $eleController, 'menuCounts' => $menuCounts ?? []]); ?>
                        </a>
                    </li>
                    
                    <!-- Inserimento del divisore se non è l'ultimo elemento -->
                    <?php if ($index < $totalLeft - 1): ?>
                        <li><hr class="dropdown-divider"></li>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Sezione Centrale: Pulsanti di azione dinamici (es. Salva, Aggiungi, Elimina) -->
            <div id="dynamic_actions" class="mx-auto">
                <?php
                    $hasDelegatedActions = in_array($controller, ['users', 'messages'], true);
                    $showExport = ! $hasDelegatedActions || \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin, $controller . '_export');
                    $showPrint = ! $hasDelegatedActions || \App\Libraries\Backend\AuthorizationClass::allows($currentAdmin, $controller . '_print');
                ?>
                <?= view_cell('BackendButtonsCell::render', ['controller' => $controller, 'action' => $action, 'showExport' => $showExport, 'showPrint' => $showPrint]); ?>
            </div>

            <!-- Sezione Destra: Menu Servizi (visibile solo agli utenti Master) -->
            <?php if ((int) $currentAdmin->superadmin === 1): ?>
            <div class="btn-group dropup ms-auto">
                <button type="button" class="btn app-btn-secondary dropdown-toggle btn-sm" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa-solid fa-gears"></i> 
                    <span><?= ui_text('backend/global.buttons.services', [], false); ?></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                <?php

                    /* Ciclo di generazione delle voci del menu servizi */
                    $totalRight = count($menuBottomRight);
                    foreach ($menuBottomRight as $index => $ele): 

                        /* Controllo sicuro per evitare notice se il controller manca */
                        $eleController = $ele['controller'] ?? '';
                        $active = (isset($controller) && $controller === $eleController) ? ' active' : '';
                        
                        /* Gestione dinamica dell'indirizzo o dell'identificativo */
                        $href = isset($ele['route']) ? base_url($ele['route']) : '#'; 
                        $dataIdAttr = isset($ele['id']) ? ' data-id="' . esc($ele['id']) . '"' : '';
                ?>
                    <li>
                        <a class="dropdown-item<?= $active; ?>" href="<?= $href; ?>"<?= $dataIdAttr; ?>>
                            <span class="me-2"><?= $ele['icon'] ?? ''; ?></span> <?= ui_value($ele['label'] ?? '', false); ?>
                        </a>
                    </li>
                    
                    <!-- Inserimento del divisore se non è l'ultimo elemento -->
                    <?php if ($index < $totalRight - 1): ?>
                        <li><hr class="dropdown-divider"></li>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php else: ?>
                <!-- Spacer tecnico per mantenere la simmetria del flexbox quando il menu Servizi è nascosto -->
                <div class="ms-auto" style="width: 40px;">&nbsp;</div>
            <?php endif; ?>

        <?php else: ?>

            <!-- Contenuto centrato -->
            <div class="text-center w-100">
                <a href="https://www.gbwebapps.com" target="_blank">
                    <span class="brand-logo brand-logo-gbwebapps" role="img" aria-label="GB Web Apps"></span>
                </a>
            </div>

        <?php endif; ?>

    </div>
</nav>
