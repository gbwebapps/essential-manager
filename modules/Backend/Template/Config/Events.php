<?php declare(strict_types = 1);

use CodeIgniter\Events\Events;
use Modules\Backend\Template\Controllers\DashboardController;

// Registra la dashboard applicativa prima delle rotte comuni.
// La precedenza nativa di CI4 mantiene il controller applicativo senza modificare i file del backbone.
Events::on('pre_system', static function (): void {
    service('routes')->get('backend/dashboard', '\\' . DashboardController::class . '::index', ['filter' => 'authorization']);
});
