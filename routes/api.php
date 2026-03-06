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

    // ── Auth ───────────────────────────────────────
    ['method' => 'POST', 'path' => '/auth/register',        'controller' => 'AuthController@register'],
    ['method' => 'POST', 'path' => '/auth/login',           'controller' => 'AuthController@login'],
    ['method' => 'POST', 'path' => '/auth/logout',          'controller' => 'AuthController@logout',        'middleware' => ['auth']],
    ['method' => 'POST', 'path' => '/auth/refresh',         'controller' => 'AuthController@refresh'],
    ['method' => 'POST', 'path' => '/auth/verify-email',    'controller' => 'AuthController@verifyEmail'],
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
    ['method' => 'POST',   'path' => '/products',          'controller' => 'ProductController@store',   'middleware' => ['auth']],
    ['method' => 'PUT',    'path' => '/products/{id}',     'controller' => 'ProductController@update',  'middleware' => ['auth']],
    ['method' => 'DELETE', 'path' => '/products/{id}',     'controller' => 'ProductController@destroy', 'middleware' => ['auth']],

    // ── Cart ───────────────────────────────────────
    ['method' => 'GET',    'path' => '/cart',          'controller' => 'CartController@index',      'middleware' => ['auth']],
    ['method' => 'POST',   'path' => '/cart/items',    'controller' => 'CartController@addItem',    'middleware' => ['auth']],
    ['method' => 'PUT',    'path' => '/cart/items/{id}','controller' => 'CartController@updateItem', 'middleware' => ['auth']],
    ['method' => 'DELETE', 'path' => '/cart/items/{id}','controller' => 'CartController@removeItem', 'middleware' => ['auth']],
    ['method' => 'DELETE', 'path' => '/cart',          'controller' => 'CartController@clear',      'middleware' => ['auth']],

    // ── Orders ─────────────────────────────────────
    ['method' => 'GET',  'path' => '/orders',               'controller' => 'OrderController@index',        'middleware' => ['auth']],
    ['method' => 'POST', 'path' => '/orders',               'controller' => 'OrderController@store',        'middleware' => ['auth']],
    ['method' => 'GET',  'path' => '/orders/{id}',          'controller' => 'OrderController@show',         'middleware' => ['auth']],
    ['method' => 'PUT',  'path' => '/orders/{id}/status',   'controller' => 'OrderController@updateStatus', 'middleware' => ['auth']],
    ['method' => 'POST', 'path' => '/orders/{id}/cancel',   'controller' => 'OrderController@cancel',       'middleware' => ['auth']],

    // ── Wallet ─────────────────────────────────────
    ['method' => 'GET',  'path' => '/wallet',              'controller' => 'WalletController@show',         'middleware' => ['auth']],
    ['method' => 'POST', 'path' => '/wallet/topup',        'controller' => 'WalletController@topup',        'middleware' => ['auth']],
    ['method' => 'POST', 'path' => '/wallet/transfer',     'controller' => 'WalletController@transfer',     'middleware' => ['auth']],
    ['method' => 'GET',  'path' => '/wallet/transactions', 'controller' => 'WalletController@transactions', 'middleware' => ['auth']],

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
    ['method' => 'GET',  'path' => '/admin/reset-password',    'controller' => 'Admin\AuthController@showResetPassword'],
    ['method' => 'GET',  'path' => '/admin/2fa',               'controller' => 'Admin\AuthController@show2FA'],
    ['method' => 'POST', 'path' => '/admin/logout',            'controller' => 'Admin\AuthController@logout',           'middleware' => ['auth']],

    // Admin - Dashboard
    ['method' => 'GET', 'path' => '/admin/dashboard',           'controller' => 'Admin\DashboardController@dashboard',         'middleware' => ['auth']],
    ['method' => 'GET', 'path' => '/admin/dashboard/analytics', 'controller' => 'Admin\DashboardController@adminDashboard',    'middleware' => ['auth']],
    ['method' => 'GET', 'path' => '/admin/dashboard/ecommerce', 'controller' => 'Admin\DashboardController@merchantDashboard', 'middleware' => ['auth']],
    ['method' => 'GET', 'path' => '/admin/dashboard/crm',       'controller' => 'Admin\DashboardController@partnerDashboard',  'middleware' => ['auth']],
];
