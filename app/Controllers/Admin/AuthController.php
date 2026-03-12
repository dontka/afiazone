<?php declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\AuthService;

class AuthController extends BaseController
{
    private AuthService $authService;

    public function __construct()
    {
        parent::__construct();
        $this->authService = new AuthService();
    }

    // ── Show pages ────────────────────────────────

    public function showLogin(): void
    {
        require base_path('html/back/auth/admin-login.php');
    }

    public function showRegister(): void
    {
        require base_path('html/back/auth/admin-register.php');
    }

    public function showForgotPassword(): void
    {
        require base_path('html/back/auth/admin-forgot-password.php');
    }

    public function showResetPassword(): void
    {
        require base_path('html/back/auth/admin-reset-password.php');
    }

    public function show2FA(): void
    {
        require base_path('html/back/auth/admin-2fa.php');
    }

    // ── Form handlers ─────────────────────────────

    public function login(): void
    {
        $email = trim((string) ($_POST['email-username'] ?? $_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if (empty($email) || empty($password)) {
            $this->redirect('/admin/login?error=' . urlencode('Email and password are required'));
            return;
        }

        try {
            $result = $this->authService->login($email, $password);
            $user = $result['user'];

            // Verify admin/moderator role
            $roles = $user['roles'] ?? [];
            if (empty(array_intersect($roles, ['admin', 'moderator', 'super_admin']))) {
                $this->redirect('/admin/login?error=' . urlencode('Access denied. Admin privileges required.'));
                return;
            }

            // Set auth cookie (HttpOnly, Secure only in production over HTTPS)
            $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                    || (($_SERVER['SERVER_PORT'] ?? 80) == 443);
            $secure = $isHttps;
            $ttl = (int) env('JWT_EXPIRATION', 3600);
            setcookie('auth_token', $result['token'], [
                'expires' => time() + $ttl,
                'path' => '/',
                'httponly' => true,
                'secure' => $secure,
                'samesite' => 'Lax',
            ]);

            $this->redirect('/admin/dashboard');
        } catch (\App\Exceptions\UnauthorizedException $e) {
            $this->redirect('/admin/login?error=' . urlencode('Invalid email or password'));
        } catch (\App\Exceptions\ForbiddenException $e) {
            $this->redirect('/admin/login?error=' . urlencode($e->getMessage()));
        } catch (\App\Exceptions\ValidationException $e) {
            $errors = implode(', ', $e->getErrors());
            $this->redirect('/admin/login?error=' . urlencode($errors));
        }
    }

    public function register(): void
    {
        $data = [
            'email' => trim((string) ($_POST['email'] ?? '')),
            'password' => (string) ($_POST['password'] ?? ''),
            'first_name' => trim((string) ($_POST['first_name'] ?? '')),
            'last_name' => trim((string) ($_POST['last_name'] ?? '')),
        ];

        try {
            $this->authService->register($data);
            $this->redirect('/admin/login?success=' . urlencode('Account created. Please check your email for verification.'));
        } catch (\App\Exceptions\ValidationException $e) {
            $errors = implode(', ', $e->getErrors());
            $this->redirect('/admin/register?error=' . urlencode($errors));
        }
    }

    public function forgotPassword(): void
    {
        $email = trim((string) ($_POST['email'] ?? ''));

        if (empty($email)) {
            $this->redirect('/admin/forgot-password?error=' . urlencode('Email is required'));
            return;
        }

        $this->authService->requestPasswordReset($email);
        $this->redirect('/admin/forgot-password?success=' . urlencode('If the email exists, a reset link was sent.'));
    }

    public function resetPassword(): void
    {
        $token = (string) ($_POST['token'] ?? '');
        $password = (string) ($_POST['password'] ?? '');

        if (empty($token) || empty($password)) {
            $this->redirect('/admin/reset-password?error=' . urlencode('Token and password are required') . '&token=' . urlencode($token));
            return;
        }

        $ok = $this->authService->resetPassword($token, $password);

        if (!$ok) {
            $this->redirect('/admin/reset-password?error=' . urlencode('Invalid or expired reset token') . '&token=' . urlencode($token));
            return;
        }

        $this->redirect('/admin/login?success=' . urlencode('Password reset successfully. Please login.'));
    }

    public function logout(): void
    {
        $token = $_COOKIE['auth_token'] ?? null;
        if ($token) {
            $this->authService->logout($token);
            setcookie('auth_token', '', [
                'expires' => time() - 3600,
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }
        $this->redirect('/admin/login');
    }
}
