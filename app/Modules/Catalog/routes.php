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
$router->get('/admin/categories', [CatalogController::class, 'adminCategories'], [Authenticate::class, new Role(['admin', 'super_admin'])]);
$router->post('/admin/categories', [CatalogController::class, 'storeCategory'], [CsrfMiddleware::class, Authenticate::class, new Role(['admin', 'super_admin'])]);
$router->patch('/admin/categories/{id}', [CatalogController::class, 'updateCategory'], [CsrfMiddleware::class, Authenticate::class, new Role(['admin', 'super_admin'])]);
$router->delete('/admin/categories/{id}', [CatalogController::class, 'deleteCategory'], [CsrfMiddleware::class, Authenticate::class, new Role(['admin', 'super_admin'])]);
$router->get('/admin/produits', [CatalogController::class, 'adminProducts'], [Authenticate::class, new Role(['admin', 'super_admin'])]);
$router->post('/admin/produits', [CatalogController::class, 'storeProduct'], [CsrfMiddleware::class, Authenticate::class, new Role(['admin', 'super_admin'])]);
$router->get('/admin/produits/{id}/modifier', [CatalogController::class, 'editProduct'], [Authenticate::class, new Role(['admin', 'super_admin'])]);
$router->patch('/admin/produits/{id}/modifier', [CatalogController::class, 'updateProduct'], [CsrfMiddleware::class, Authenticate::class, new Role(['admin', 'super_admin'])]);
$router->delete('/admin/produits/{id}', [CatalogController::class, 'deleteProduct'], [CsrfMiddleware::class, Authenticate::class, new Role(['admin', 'super_admin'])]);
$router->patch('/admin/produits/{id}/statut', [CatalogController::class, 'changeProductStatus'], [CsrfMiddleware::class, Authenticate::class, new Role(['admin', 'super_admin'])]);
$router->post('/admin/produits/{id}/images', [CatalogController::class, 'storeImage'], [CsrfMiddleware::class, Authenticate::class, new Role(['admin', 'super_admin'])]);
$router->post('/admin/produits/{id}/documents', [CatalogController::class, 'storeDocument'], [CsrfMiddleware::class, Authenticate::class, new Role(['admin', 'super_admin'])]);