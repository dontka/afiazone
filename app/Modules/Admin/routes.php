<?php

declare(strict_types=1);

use App\Modules\Admin\Controllers\DashboardController;
use App\Core\Middleware\Authenticate;
use App\Core\Middleware\Role;

$router->get('/admin', [DashboardController::class, 'index'], [Authenticate::class, new Role(['admin', 'super_admin'])]);