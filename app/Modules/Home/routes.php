<?php

declare(strict_types=1);

use App\Modules\Home\Controllers\HomeController;

$router->get('/', [HomeController::class, 'index']);