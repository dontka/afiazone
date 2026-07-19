<?php

declare(strict_types=1);

use App\Modules\Admin\Controllers\DashboardController;

$router->get('/admin', [DashboardController::class, 'index']);