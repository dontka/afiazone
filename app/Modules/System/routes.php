<?php

declare(strict_types=1);

use App\Modules\System\Controllers\HealthCheckController;

$router->get('/health-check', [HealthCheckController::class, 'index']);