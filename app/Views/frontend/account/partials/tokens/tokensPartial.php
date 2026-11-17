<div class="card-body">
    <?php if(count($tokens)): ?>
        <ul class="list-group">
            <?php foreach($tokens as $token): ?>

                <?php
                    /* Creiamo l'oggetto DateTime partendo dalla data stringa del database */
                    $dateExpire = new \DateTime($token->token_expire);

                    /* Creiamo l'oggetto DateTime con l'ora attuale per fare il confronto */
                    $now = new \DateTime();

                    /* Applichiamo le classi in base al confronto (se adesso è maggiore della scadenza, è scaduto) */
                    $class = ($now > $dateExpire) ? 'app-text-danger fw-bold text-decoration-line-through' : 'app-text-success fw-bold';

                    /* Mappatura delle traduzioni per i tipi di token */
                    $typeMap = [
                        'session'    => 'frontend/account.labels.session',
                        'activation' => 'frontend/account.labels.activation',
                        'cookie'     => 'frontend/account.labels.rememberMe'
                    ];

                    /* Assegnazione con fallback automatico se la chiave non esiste */
                    $tokenType = $typeMap[$token->token_type] ?? 'frontend/account.labels.unknown';

                    /* Parsing della stringa User Agent tramite l'istanza passata dal Controller */
					$userAgent->parse((string) $token->user_agent);
                    
                    /* Inizializzazione corretta della variabile in camelCase */
					$agentText = '<span class="text-white">' . ui_text('frontend/account.labels.operatingSystem') . '</span> <span class="text-primary fw-bold">' . esc($userAgent->getPlatform()) . '</span>';
                    
                    /* Valutazione con metodi nativi in camelCase di CodeIgniter 4 */
                    if($userAgent->isBrowser()):
						$agentText .= ' &bull; <span class="text-white">' . ui_text('frontend/account.labels.browser') . '</span> <span class="text-primary fw-bold">' . esc($userAgent->getBrowser()) . '</span>';
                    elseif($userAgent->isMobile()):
						$agentText .= ' &bull; <span class="text-white">' . ui_text('frontend/account.labels.mobile') . '</span> <span class="text-primary fw-bold">' . esc($userAgent->getMobile()) . '</span>';
                    elseif($userAgent->isRobot()):
						$agentText .= ' &bull; <span class="text-white">' . ui_text('frontend/account.labels.robot') . '</span> <span class="text-primary fw-bold">' . esc($userAgent->getRobot()) . '</span>';
                    endif;
                ?>

                <li class="list-group-item py-3">
                    <div class="row align-items-center">
                        
                        <!-- Dati Token (Creazione, Scadenza, Tipo) e User Agent -->
                        <div class="col-12 col-lg">
                            
                            <div class="row">
                                <div class="col-12 col-md-4 mb-3 mb-md-0 text-start">
                                    <small class="app-text-muted fw-bold d-block mb-1"><i class="fa-solid fa-circle-arrow-down me-1"></i><?= ui_text('frontend/account.labels.createdToken', [], false); ?></small>
									<span class="fw-bold"><?= ui_date($token->token_create, 'conversational'); ?></span>
                                </div>
                                <div class="col-12 col-md-4 mb-3 mb-md-0 text-start">
                                    <small class="app-text-muted fw-bold d-block mb-1"><i class="fa-solid fa-circle-arrow-down me-1"></i><?= ui_text('frontend/account.labels.expiredToken', [], false); ?></small>
									<span class="<?= $class; ?>"><?= ui_date($dateExpire->format('Y-m-d H:i:s'), 'conversational'); ?></span>
                                </div>
                                <div class="col-12 col-md-4 mb-3 mb-md-0 text-start">
                                    <small class="app-text-muted fw-bold d-block mb-1"><i class="fa-solid fa-circle-arrow-down me-1"></i><?= ui_text('frontend/account.labels.typeToken', [], false); ?></small>
                                    <span class="fw-bold"><?= ui_text($tokenType, [], true); ?></span>
                                </div>
                            </div>
                            
                            <!-- User Agent e IP -->
                            <div class="row mt-lg-3">
                                <div class="col-12 text-start">
                                    <hr class="d-md-none my-2 app-text-muted">
                                    <small class="token-system-details">
                                        <?= $agentText; ?>
                                        &bull; 
                                        <span class="text-white"><?= ui_text('frontend/account.labels.ipAddress', [], false); ?></span>
                                        <span class="text-primary fw-bold"><?= esc($token->ip_address); ?></span>
                                    </small>
                                </div>
                            </div>

                        </div>
                        
                        <!-- Azione (Elimina o Badge Sessione Corrente) -->
                        <div class="col-12 col-lg-auto mt-3 mt-lg-0 text-start text-lg-end">
                            
                            <?php if (isset($currentTokenId) && $currentTokenId === (int) $token->id): ?>
                                
                                <!-- Segnaposto per la sessione in corso: non eliminabile -->
                                <span class="badge app-bg-success py-2 px-3 shadow-sm">
                                    <i class="fa-solid fa-desktop me-1"></i> <?= ui_text('frontend/account.labels.currentDevice', [], false); ?>
                                </span>
                                
                            <?php else: ?>
                                
                                <!-- Form per eliminare tutti gli ALTRI tokens -->
                                <form <?= ui_binding('frontend/account.messages.areYouSureDeleteToken', 'data-message', [esc($currentUser->firstname), esc($currentUser->lastname)], false); ?> class="deleteToken d-grid d-lg-block" data-message="<?= sprintf(lang('frontend/account.messages.areYouSureDeleteToken'), esc($currentUser->firstname), esc($currentUser->lastname)); ?>">
                                    <input type="hidden" name="id" value="<?= esc($token->id); ?>">
                                    <button type="submit" class="btn app-btn-danger btn-sm shadow-none">
                                        <i class="fa-solid fa-trash me-1"></i> <?= ui_text('frontend/account.buttons.delete', [], false); ?>
                                    </button>
                                </form>
                                
                            <?php endif; ?>

                        </div>
                        
                    </div>
                </li>

            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <div class="text-center py-3 fw-bold">
            <?= ui_text('frontend/account.messages.noTokensFound', [], false); ?>
        </div>
    <?php endif; ?>
</div>
