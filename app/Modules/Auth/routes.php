<?php

declare(strict_types=1);

use App\Core\Middleware\Authenticate;
use App\Core\Middleware\CsrfMiddleware;
use App\Core\Middleware\Guest;
use App\Modules\Auth\Controllers\AuthController;
use App\Modules\Auth\Controllers\AccountController;

$router->get('/connexion', [AuthController::class, 'loginPage'], [Guest::class]);
$router->post('/connexion', [AuthController::class, 'login'], [CsrfMiddleware::class, Guest::class]);
$router->post('/deconnexion', [AuthController::class, 'logout'], [CsrfMiddleware::class, Authenticate::class]);
$router->get('/verification-email', [AuthController::class, 'verificationPage'], [Guest::class]);
$router->post('/verification-email/resend', [AuthController::class, 'resendVerificationEmail'], [CsrfMiddleware::class, Guest::class]);
$router->get('/verifier-email/{token}', [AuthController::class, 'verifyEmail'], [Guest::class]);
$router->get('/inscription', [AuthController::class, 'registerPage'], [Guest::class]);
$router->post('/inscription', [AuthController::class, 'registerCustomer'], [CsrfMiddleware::class, Guest::class]);
$router->get('/inscription/marchand', [AuthController::class, 'merchantRegisterPage'], [Guest::class]);
$router->post('/inscription/marchand', [AuthController::class, 'registerMerchant'], [CsrfMiddleware::class, Guest::class]);
$router->get('/mot-de-passe-oublie', [AuthController::class, 'forgotPage'], [Guest::class]);
$router->post('/mot-de-passe-oublie', [AuthController::class, 'forgotPassword'], [CsrfMiddleware::class, Guest::class]);
$router->get('/reset-password/{token}', [AuthController::class, 'resetPage'], [Guest::class]);
$router->post('/reset-password/{token}', [AuthController::class, 'resetPassword'], [CsrfMiddleware::class, Guest::class]);
$router->get('/compte', [AccountController::class, 'customerDashboard'], [Authenticate::class]);
$router->get('/marchand', [AccountController::class, 'merchantDashboard'], [Authenticate::class, new \App\Core\Middleware\Role('merchant')]);