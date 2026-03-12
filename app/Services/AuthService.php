<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\Token;
use App\Repositories\UserRepository;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

class AuthService extends BaseService
{
    private UserRepository $userRepo;
    private string $jwtSecret;
    private int $jwtTtl;
    private int $refreshTtl;

    public function __construct()
    {
        parent::__construct();
        $this->userRepo = new UserRepository();
        $this->jwtSecret = (string) env('JWT_SECRET', '');
        if ($this->jwtSecret === '') {
            $this->jwtSecret = (string) env('APP_KEY', '');
        }
        $this->jwtTtl = (int) env('JWT_EXPIRATION', 3600);
        $this->refreshTtl = (int) env('JWT_REFRESH_EXPIRATION', 604800);
    }

    public function register(array $data): array
    {
        $errors = $this->validate($data, [
            'email' => 'required|email',
            'password' => 'required|min:8',
            'first_name' => 'required',
            'last_name' => 'required',
        ]);
        $this->throwIfErrors($errors);

        if ($this->userRepo->emailExists($data['email'])) {
            $this->throwIfErrors(['email' => 'Email already registered']);
        }

        if (!empty($data['phone']) && $this->userRepo->phoneExists($data['phone'])) {
            $this->throwIfErrors(['phone' => 'Phone number already registered']);
        }

        $user = User::create([
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password_hash' => password_hash($data['password'], PASSWORD_BCRYPT),
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'status' => 'pending_verification',
        ]);

        $user->assignRole('customer');

        // Generate email verification token and send it
        $tokenData = Token::createForUser((int) $user->id, 'email_verification', 86400);
        $this->sendVerificationEmail($user, $tokenData['plain']);

        $this->log('User registered', ['user_id' => $user->id]);

        $token = $this->generateJwt($user);

        $userData = $user->toArray();
        $userData['roles'] = $user->getRoleNames();

        return [
            'user' => $userData,
            'token' => $token,
        ];
    }

    public function login(string $email, string $password): array
    {
        $user = $this->userRepo->findByEmail($email);

        if (!$user || !password_verify($password, $user->password_hash ?? '')) {
            throw new \App\Exceptions\UnauthorizedException('Invalid credentials');
        }

        if ($user->status === 'banned') {
            throw new \App\Exceptions\ForbiddenException('Account suspended');
        }

        $user->update(['last_login_at' => date('Y-m-d H:i:s')]);

        $this->log('User logged in', ['user_id' => $user->id]);

        $token = $this->generateJwt($user);

        $userData = $user->toArray();
        $userData['roles'] = $user->getRoleNames();

        return [
            'user' => $userData,
            'token' => $token,
            'email_verified' => !empty($user->email_verified_at),
        ];
    }

    public function validateToken(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
            return (array) $decoded;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function refreshToken(string $currentToken): ?string
    {
        $payload = $this->validateToken($currentToken);
        if (!$payload) {
            return null;
        }
        $user = User::find($payload['sub'] ?? 0);
        if (!$user || $user->status === 'banned') {
            return null;
        }
        return $this->generateJwt($user);
    }

    private function generateJwt(User $user): string
    {
        $now = time();
        $payload = [
            'iss' => env('APP_URL', 'http://localhost'),
            'sub' => $user->id,
            'email' => $user->email,
            'roles' => $user->getRoleNames(),
            'iat' => $now,
            'exp' => $now + $this->jwtTtl,
        ];
        return JWT::encode($payload, $this->jwtSecret, 'HS256');
    }

    // ── Email Verification ────────────────────────

    public function verifyEmail(string $plainToken): bool
    {
        $token = Token::findByPlainToken($plainToken);
        if (!$token || $token->isExpired() || $token->token_type !== 'email_verification') {
            return false;
        }

        $user = User::find($token->user_id);
        if (!$user) {
            return false;
        }

        $user->update([
            'email_verified_at' => date('Y-m-d H:i:s'),
            'status' => 'active',
        ]);
        $token->markUsed();

        $this->log('Email verified', ['user_id' => $user->id]);
        return true;
    }

    public function resendVerificationEmail(User $user): bool
    {
        if (!empty($user->email_verified_at)) {
            return false;
        }

        $tokenData = Token::createForUser((int) $user->id, 'email_verification', 86400);
        $this->sendVerificationEmail($user, $tokenData['plain']);

        $this->log('Verification email resent', ['user_id' => $user->id]);
        return true;
    }

    // ── Password Reset ────────────────────────────

    public function requestPasswordReset(string $email): array
    {
        $user = $this->userRepo->findByEmail($email);
        if (!$user) {
            // Don't reveal if email exists
            return ['sent' => true];
        }

        $tokenData = Token::createForUser((int) $user->id, 'password_reset', 1800);
        $this->sendPasswordResetEmail($user, $tokenData['plain']);

        $this->log('Password reset requested', ['user_id' => $user->id]);
        return ['sent' => true];
    }

    public function resetPassword(string $plainToken, string $newPassword): bool
    {
        $errors = $this->validate(['password' => $newPassword], [
            'password' => 'required|min:8',
        ]);
        $this->throwIfErrors($errors);

        $token = Token::findByPlainToken($plainToken);
        if (!$token || $token->isExpired() || $token->token_type !== 'password_reset') {
            return false;
        }

        $user = User::find($token->user_id);
        if (!$user) {
            return false;
        }

        $user->update(['password_hash' => password_hash($newPassword, PASSWORD_BCRYPT)]);
        $token->markUsed();

        $this->log('Password reset completed', ['user_id' => $user->id]);
        return true;
    }

    // ── Logout (JWT blacklist via DB) ─────────────

    public function logout(string $token): void
    {
        $payload = $this->validateToken($token);
        if ($payload) {
            // Store hashed token as used to prevent reuse
            $expiresAt = date('Y-m-d H:i:s', $payload['exp'] ?? time());
            Token::create([
                'user_id' => $payload['sub'],
                'token_type' => 'jwt',
                'token_hash' => hash('sha256', $token),
                'expires_at' => $expiresAt,
                'is_used' => true,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            ]);
        }
    }

    public function isTokenBlacklisted(string $token): bool
    {
        $hash = hash('sha256', $token);
        $found = Token::query()
            ->where('token_hash', $hash)
            ->where('token_type', 'jwt')
            ->where('is_used', true)
            ->first();
        return $found !== null;
    }

    // ── Email Sending ─────────────────────────────

    private function sendVerificationEmail(User $user, string $plainToken): void
    {
        $appUrl = env('APP_URL', 'http://localhost');
        $verifyUrl = $appUrl . '/auth/verify-email?token=' . urlencode($plainToken);

        $html = $this->renderEmailTemplate('verify-email', [
            'name' => $user->first_name,
            'verify_url' => $verifyUrl,
        ]);

        $this->sendEmail(
            $user->email,
            'Vérifiez votre adresse email — AfiaZone',
            $html
        );
    }

    private function sendPasswordResetEmail(User $user, string $plainToken): void
    {
        $appUrl = env('APP_URL', 'http://localhost');
        $resetUrl = $appUrl . '/admin/reset-password?token=' . urlencode($plainToken);

        $html = $this->renderEmailTemplate('reset-password', [
            'name' => $user->first_name,
            'reset_url' => $resetUrl,
        ]);

        $this->sendEmail(
            $user->email,
            'Réinitialisation de votre mot de passe — AfiaZone',
            $html
        );
    }

    private function sendEmail(string $to, string $subject, string $html): void
    {
        try {
            $dsn = env('MAIL_DSN', '');
            if (empty($dsn)) {
                $host = env('MAIL_HOST', 'localhost');
                $port = env('MAIL_PORT', '1025');
                $user = env('MAIL_USERNAME', '');
                $pass = env('MAIL_PASSWORD', '');
                if ($user && $pass) {
                    $dsn = "smtp://{$user}:{$pass}@{$host}:{$port}";
                } else {
                    $dsn = "smtp://{$host}:{$port}";
                }
            }

            $transport = Transport::fromDsn($dsn);
            $mailer = new Mailer($transport);

            $fromAddress = env('MAIL_FROM_ADDRESS', 'noreply@afiazone.com');
            $fromName = env('MAIL_FROM_NAME', 'AfiaZone');

            $email = (new Email())
                ->from("{$fromName} <{$fromAddress}>")
                ->to($to)
                ->subject($subject)
                ->html($html);

            $mailer->send($email);
        } catch (\Throwable $e) {
            $this->logError('Failed to send email', $e);
        }
    }

    private function renderEmailTemplate(string $template, array $data): string
    {
        $templatePath = base_path("html/emails/{$template}.php");
        if (!file_exists($templatePath)) {
            // Fallback to simple HTML
            return match ($template) {
                'verify-email' => sprintf(
                    '<h2>Bienvenue sur AfiaZone, %s !</h2>
                    <p>Cliquez sur le lien ci-dessous pour vérifier votre adresse email :</p>
                    <p><a href="%s" style="background:#28a745;color:#fff;padding:12px 24px;text-decoration:none;border-radius:5px;">Vérifier mon email</a></p>
                    <p>Ce lien expire dans 24 heures.</p>
                    <p>Si vous n\'avez pas créé de compte, ignorez cet email.</p>',
                    htmlspecialchars($data['name'], ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars($data['verify_url'], ENT_QUOTES, 'UTF-8')
                ),
                'reset-password' => sprintf(
                    '<h2>Réinitialisation de mot de passe</h2>
                    <p>Bonjour %s, vous avez demandé une réinitialisation de mot de passe.</p>
                    <p><a href="%s" style="background:#007bff;color:#fff;padding:12px 24px;text-decoration:none;border-radius:5px;">Réinitialiser mon mot de passe</a></p>
                    <p>Ce lien expire dans 30 minutes.</p>
                    <p>Si vous n\'avez pas fait cette demande, ignorez cet email.</p>',
                    htmlspecialchars($data['name'], ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars($data['reset_url'], ENT_QUOTES, 'UTF-8')
                ),
                default => '<p>Message from AfiaZone</p>',
            };
        }

        extract($data);
        ob_start();
        require $templatePath;
        return (string) ob_get_clean();
    }
}
