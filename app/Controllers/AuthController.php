<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\AuthService;

class AuthController extends BaseController
{
    private AuthService $authService;

    public function __construct()
    {
        parent::__construct();
        $this->authService = new AuthService();
    }

    public function showLogin(): void
    {
        require base_path('html/front/auth/login.php');
    }

    public function showRegister(): void
    {
        require base_path('html/front/auth/register.php');
    }

    public function showForgotPassword(): void
    {
        require base_path('html/front/auth/forgot-password.php');
    }

    public function showResetPassword(): void
    {
        require base_path('html/front/auth/reset-password.php');
    }

    public function register(): void
    {
        try {
            $result = $this->authService->register($this->getData());
            $this->jsonResponse($result, 201, 'Registration successful');
        } catch (\App\Exceptions\ValidationException $e) {
            $this->errorResponse($e->getMessage(), 422, $e->getErrors());
        } catch (\Throwable $e) {
            $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function login(): void
    {
        try {
            // Support both 'email' (legacy) and 'identifier' (new) parameter names
            $identifier = (string) ($this->getData('identifier') ?? $this->getData('email') ?? '');
            $password = (string) $this->getData('password');

            if (empty($identifier)) {
                $this->errorResponse('Identifier or email is required', 422);
                return;
            }

            if (empty($password)) {
                $this->errorResponse('Password is required', 422);
                return;
            }

            $result = $this->authService->login($identifier, $password);

            $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                    || (($_SERVER['SERVER_PORT'] ?? 80) == 443);
            $ttl = (int) env('JWT_EXPIRATION', 3600);
            setcookie('auth_token', $result['token'], [
                'expires' => time() + $ttl,
                'path' => '/',
                'httponly' => true,
                'secure' => $isHttps,
                'samesite' => 'Lax',
            ]);

            $this->jsonResponse($result);
        } catch (\App\Exceptions\UnauthorizedException $e) {
            $this->errorResponse($e->getMessage(), 401);
        } catch (\App\Exceptions\ForbiddenException $e) {
            $this->errorResponse($e->getMessage(), 403);
        } catch (\Throwable $e) {
            $this->errorResponse('Login failed', 500);
        }
    }

    public function refresh(): void
    {
        $token = $this->getData('token') ?? '';
        $newToken = $this->authService->refreshToken($token);

        if (!$newToken) {
            $this->errorResponse('Invalid token', 401);
            return;
        }

        $this->jsonResponse(['token' => $newToken]);
    }

    public function logout(): void
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        $token = (string) ($this->getData('token') ?? ($_COOKIE['auth_token'] ?? ''));
        if (preg_match('/Bearer\s+(.+)/', $header, $m)) {
            $token = $m[1];
        }

        $this->authService->logout($token);

        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if ($method === 'GET') {
            setcookie('auth_token', '', [
                'expires' => time() - 3600,
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);

            $redirect = (string) ($this->getData('redirect') ?? '/');
            if (!str_starts_with($redirect, '/') || str_starts_with($redirect, '//')) {
                $redirect = '/';
            }

            $this->redirect($redirect);
            return;
        }

        $this->jsonResponse(['message' => 'Logout successful']);
    }

    public function verifyEmail(): void
    {
        $token = $this->getData('token') ?? '';
        $ok = $this->authService->verifyEmail($token);

        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if ($method === 'GET') {
            if ($ok) {
                $this->redirect('/auth/login?success=' . urlencode('Email vérifié avec succès. Vous pouvez vous connecter.'));
                return;
            }

            $this->redirect('/auth/login?error=' . urlencode('Lien de vérification invalide ou expiré.'));
            return;
        }

        if (!$ok) {
            $this->errorResponse('Invalid or expired verification token', 400);
            return;
        }

        $this->jsonResponse(['message' => 'Email verified']);
    }

    public function forgotPassword(): void
    {
        $this->authService->requestPasswordReset((string) $this->getData('email'));
        $this->jsonResponse(['message' => 'If the email exists, a reset link was sent']);
    }

    public function resetPassword(): void
    {
        try {
            $ok = $this->authService->resetPassword(
                (string) $this->getData('token'),
                (string) $this->getData('password')
            );

            if (!$ok) {
                $this->errorResponse('Invalid or expired reset token', 400);
                return;
            }

            $this->jsonResponse(['message' => 'Password reset successfully']);
        } catch (\App\Exceptions\ValidationException $e) {
            $this->errorResponse($e->getMessage(), 422, $e->getErrors());
        } catch (\Throwable $e) {
            $this->errorResponse('Reset password failed', 500);
        }
    }
}
