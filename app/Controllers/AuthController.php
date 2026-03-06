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

    public function register(): void
    {
        $result = $this->authService->register($this->getData());
        $this->jsonResponse($result, 201, 'Registration successful');
    }

    public function login(): void
    {
        $result = $this->authService->login(
            (string) $this->getData('email'),
            (string) $this->getData('password')
        );
        $this->jsonResponse($result);
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
        // Stateless JWT — client simply discards the token
        $this->jsonResponse(['message' => 'Logout successful']);
    }

    public function verifyEmail(): void
    {
        $token = $this->getData('token') ?? '';
        $ok = $this->authService->verifyEmail($token);

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
        $ok = $this->authService->resetPassword(
            (string) $this->getData('token'),
            (string) $this->getData('password')
        );

        if (!$ok) {
            $this->errorResponse('Invalid or expired reset token', 400);
            return;
        }

        $this->jsonResponse(['message' => 'Password reset successfully']);
    }
}
