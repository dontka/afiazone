<?php

declare(strict_types=1);

/**
 * API Routes
 *
 * All API routes are defined here.
 * Routes follow RESTful conventions.
 */

return [
    // ── Public ─────────────────────────────────────
    ['method' => 'GET',  'path' => '/',        'controller' => 'HealthController@welcome'],
    ['method' => 'GET',  'path' => '/health',  'controller' => 'HealthController@check'],

    // ── Auth (pages) ──────────────────────────────
    ['method' => 'GET',  'path' => '/auth/login',           'controller' => 'AuthController@showLogin'],
    ['method' => 'GET',  'path' => '/auth/register',        'controller' => 'AuthController@showRegister'],
    ['method' => 'GET',  'path' => '/auth/forgot-password', 'controller' => 'AuthController@showForgotPassword'],
    ['method' => 'GET',  'path' => '/auth/reset-password',  'controller' => 'AuthController@showResetPassword'],

    // ── Auth (API) ─────────────────────────────────
    ['method' => 'POST', 'path' => '/auth/register',        'controller' => 'AuthController@register'],
    ['method' => 'POST', 'path' => '/auth/login',           'controller' => 'AuthController@login'],
    ['method' => 'GET',  'path' => '/auth/logout',          'controller' => 'AuthController@logout'],
    ['method' => 'POST', 'path' => '/auth/logout',          'controller' => 'AuthController@logout',        'middleware' => ['auth']],
    ['method' => 'POST', 'path' => '/auth/refresh',         'controller' => 'AuthController@refresh'],
    ['method' => 'POST', 'path' => '/auth/verify-email',    'controller' => 'AuthController@verifyEmail'],
    ['method' => 'GET',  'path' => '/auth/verify-email',    'controller' => 'AuthController@verifyEmail'],
    ['method' => 'POST', 'path' => '/auth/forgot-password', 'controller' => 'AuthController@forgotPassword'],
    ['method' => 'POST', 'path' => '/auth/reset-password',  'controller' => 'AuthController@resetPassword'],

    // ── User / Profile ─────────────────────────────
    ['method' => 'GET',  'path' => '/me',              'controller' => 'UserController@profile',        'middleware' => ['auth']],
    ['method' => 'PUT',  'path' => '/me',              'controller' => 'UserController@updateProfile',  'middleware' => ['auth']],
    ['method' => 'POST', 'path' => '/me/password',     'controller' => 'UserController@changePassword', 'middleware' => ['auth']],

    // ── Products (public) ──────────────────────────
    ['method' => 'GET',  'path' => '/products',            'controller' => 'ProductController@index'],
    ['method' => 'GET',  'path' => '/products/featured',   'controller' => 'ProductController@featured'],
    ['method' => 'GET',  'path' => '/products/{id}',       'controller' => 'ProductController@show'],
    // Products (merchant / admin)
    ['method' => 'POST',   'path' => '/products',          'controller' => 'ProductController@store',   'middleware' => ['auth', 'verified']],
    ['method' => 'PUT',    'path' => '/products/{id}',     'controller' => 'ProductController@update',  'middleware' => ['auth', 'verified']],
    ['method' => 'DELETE', 'path' => '/products/{id}',     'controller' => 'ProductController@destroy', 'middleware' => ['auth', 'verified']],

    // ── Cart ───────────────────────────────────────
    ['method' => 'GET',    'path' => '/cart',          'controller' => 'CartController@index',      'middleware' => ['auth', 'verified']],
    ['method' => 'POST',   'path' => '/cart/items',    'controller' => 'CartController@addItem',    'middleware' => ['auth', 'verified']],
    ['method' => 'PUT',    'path' => '/cart/items/{id}','controller' => 'CartController@updateItem', 'middleware' => ['auth', 'verified']],
    ['method' => 'DELETE', 'path' => '/cart/items/{id}','controller' => 'CartController@removeItem', 'middleware' => ['auth', 'verified']],
    ['method' => 'DELETE', 'path' => '/cart',          'controller' => 'CartController@clear',      'middleware' => ['auth', 'verified']],

    // ── Orders ─────────────────────────────────────
    ['method' => 'GET',  'path' => '/orders',               'controller' => 'OrderController@index',        'middleware' => ['auth', 'verified']],
    ['method' => 'POST', 'path' => '/orders',               'controller' => 'OrderController@store',        'middleware' => ['auth', 'verified']],
    ['method' => 'GET',  'path' => '/orders/{id}',          'controller' => 'OrderController@show',         'middleware' => ['auth', 'verified']],
    ['method' => 'PUT',  'path' => '/orders/{id}/status',   'controller' => 'OrderController@updateStatus', 'middleware' => ['auth', 'verified']],
    ['method' => 'POST', 'path' => '/orders/{id}/cancel',   'controller' => 'OrderController@cancel',       'middleware' => ['auth', 'verified']],

    // ── Wallet ─────────────────────────────────────
    ['method' => 'GET',  'path' => '/wallet',              'controller' => 'WalletController@show',         'middleware' => ['auth', 'verified']],
    ['method' => 'POST', 'path' => '/wallet/topup',        'controller' => 'WalletController@topup',        'middleware' => ['auth', 'verified']],
    ['method' => 'POST', 'path' => '/wallet/transfer',     'controller' => 'WalletController@transfer',     'middleware' => ['auth', 'verified']],
    ['method' => 'GET',  'path' => '/wallet/transactions', 'controller' => 'WalletController@transactions', 'middleware' => ['auth', 'verified']],

    // ── KYC ────────────────────────────────────────
    ['method' => 'GET',  'path' => '/kyc',            'controller' => 'KycController@show',    'middleware' => ['auth']],
    ['method' => 'POST', 'path' => '/kyc',            'controller' => 'KycController@submit',  'middleware' => ['auth']],
    ['method' => 'POST', 'path' => '/kyc/{id}/approve','controller' => 'KycController@approve', 'middleware' => ['auth']],
    ['method' => 'POST', 'path' => '/kyc/{id}/reject', 'controller' => 'KycController@reject',  'middleware' => ['auth']],

    // ── Admin (HTML) ───────────────────────────────
    ['method' => 'GET',  'path' => '/admin',                   'controller' => 'Admin\AuthController@showLogin'],
    ['method' => 'GET',  'path' => '/admin/login',             'controller' => 'Admin\AuthController@showLogin'],
    ['method' => 'POST', 'path' => '/admin/login',             'controller' => 'Admin\AuthController@login'],
    ['method' => 'GET',  'path' => '/admin/register',          'controller' => 'Admin\AuthController@showRegister'],
    ['method' => 'POST', 'path' => '/admin/register',          'controller' => 'Admin\AuthController@register'],
    ['method' => 'GET',  'path' => '/admin/forgot-password',   'controller' => 'Admin\AuthController@showForgotPassword'],
    ['method' => 'POST', 'path' => '/admin/forgot-password',   'controller' => 'Admin\AuthController@forgotPassword'],
    ['method' => 'GET',  'path' => '/admin/reset-password',    'controller' => 'Admin\AuthController@showResetPassword'],
    ['method' => 'POST', 'path' => '/admin/reset-password',    'controller' => 'Admin\AuthController@resetPassword'],
    ['method' => 'GET',  'path' => '/admin/2fa',               'controller' => 'Admin\AuthController@show2FA'],
    ['method' => 'GET',  'path' => '/admin/logout',            'controller' => 'Admin\AuthController@logout'],
    ['method' => 'POST', 'path' => '/admin/logout',            'controller' => 'Admin\AuthController@logout'],

    // Admin - Dashboard
    ['method' => 'GET', 'path' => '/admin/dashboard',           'controller' => 'Admin\DashboardController@dashboard',         'middleware' => ['auth', 'rbac:super_admin,admin,moderator']],
    ['method' => 'GET', 'path' => '/admin/dashboard/admin', 'controller' => 'Admin\DashboardController@adminDashboard',    'middleware' => ['auth', 'rbac:super_admin,admin']],
    ['method' => 'GET', 'path' => '/admin/dashboard/merchant', 'controller' => 'Admin\DashboardController@merchantDashboard', 'middleware' => ['auth', 'rbac:super_admin,admin,merchant']],
    ['method' => 'GET', 'path' => '/admin/dashboard/partner',       'controller' => 'Admin\DashboardController@partnerDashboard',  'middleware' => ['auth', 'rbac:super_admin,admin,partner']],
    ['method' => 'GET', 'path' => '/admin/dashboard/deliverer',     'controller' => 'Admin\DashboardController@delivererDashboard','middleware' => ['auth', 'rbac:super_admin,admin,deliverer']],
];
