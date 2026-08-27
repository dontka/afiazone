<?php

declare(strict_types=1);

use App\Core\Middleware\Authenticate;
use App\Core\Middleware\CsrfMiddleware;
use App\Core\Middleware\Role;
use App\Modules\Catalog\Controllers\CatalogController;

$router->get('/catalogue', [CatalogController::class, 'index']);
$router->get('/categorie/{slug}', [CatalogController::class, 'category']);
$router->get('/produit/{slug}', [CatalogController::class, 'show']);
$router->get('/admin/catalogue', [CatalogController::class, 'adminIndex'], [Authenticate::class, new Role(['admin', 'super_admin'])]);
$router->post('/admin/catalogue/categories', [CatalogController::class, 'storeCategory'], [CsrfMiddleware::class, Authenticate::class, new Role(['admin', 'super_admin'])]);
$router->post('/admin/catalogue/produits', [CatalogController::class, 'storeProduct'], [CsrfMiddleware::class, Authenticate::class, new Role(['admin', 'super_admin'])]);