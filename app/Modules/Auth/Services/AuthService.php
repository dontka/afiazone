<?php

declare(strict_types=1);

namespace App\Modules\Auth\Services;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Mailer;

class AuthService
{
    private const MAX_FAILED_ATTEMPTS = 5;
    private const ATTEMPT_WINDOW_MINUTES = 15;

    public function register(array $data, string $role): int
    {
        $connection = Database::connection();
        $connection->beginTransaction();

        try {
            $userStatement = $connection->prepare(
                'INSERT INTO users (uuid, email, phone, password_hash, status, created_at, updated_at) VALUES (:uuid, :email, :phone, :password_hash, :status, NOW(), NOW())'
            );
            $userStatement->execute([
                'uuid' => $this->uuid(),
                'email' => $this->nullable($data['email'] ?? null),
                'phone' => $this->nullable($data['phone'] ?? null),
                'password_hash' => password_hash((string) $data['password'], PASSWORD_DEFAULT),
                'status' => 'pending',
            ]);
            $userId = (int) $connection->lastInsertId();

            $profileStatement = $connection->prepare(
                'INSERT INTO user_profiles (user_id, full_name, business_name, created_at, updated_at) VALUES (:user_id, :full_name, :business_name, NOW(), NOW())'
            );
            $profileStatement->execute([
                'user_id' => $userId,
                'full_name' => trim((string) ($data['full_name'] ?? '')),
                'business_name' => $this->nullable($data['business_name'] ?? null),
            ]);

            $roleStatement = $connection->prepare('SELECT id FROM roles WHERE name = :name LIMIT 1');
            $roleStatement->execute(['name' => $role]);
            $roleId = (int) $roleStatement->fetchColumn();

            $userRoleStatement = $connection->prepare('INSERT INTO user_roles (user_id, role_id) VALUES (:user_id, :role_id)');
            $userRoleStatement->execute(['user_id' => $userId, 'role_id' => $roleId]);

            $levelStatement = $connection->prepare(
                "INSERT INTO account_levels (user_id, level, created_at, updated_at) VALUES (:user_id, 'verified', NOW(), NOW())"
            );
            $levelStatement->execute(['user_id' => $userId]);

            $connection->commit();
            return $userId;
        } catch (\Throwable $exception) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }
            throw $exception;
        }
    }

    public function sendVerificationEmail(int $userId): ?string
    {
        $statement = Database::connection()->prepare('SELECT email, full_name FROM users u INNER JOIN user_profiles p ON p.user_id = u.id WHERE u.id = :id LIMIT 1');
        $statement->execute(['id' => $userId]);
        $user = $statement->fetch();

        if (! is_array($user) || ! is_string($user['email']) || $user['email'] === '') {
            return null;
        }

        $token = bin2hex(random_bytes(32));
        $tokenStatement = Database::connection()->prepare(
            'INSERT INTO email_verifications (user_id, token_hash, expires_at, created_at) VALUES (:user_id, :token_hash, DATE_ADD(NOW(), INTERVAL 24 HOUR), NOW())'
        );
        $tokenStatement->execute([
            'user_id' => $userId,
            'token_hash' => hash('sha256', $token),
        ]);

        $mailConfig = require BASE_PATH . '/config/mail.php';
        $verificationUrl = rtrim((string) env('APP_URL', 'http://afyazone.test'), '/') . '/verifier-email/' . $token;
        $sent = (new Mailer($mailConfig))->sendVerificationEmail(
            (string) $user['email'],
            (string) $user['full_name'],
            $verificationUrl
        );

        if (! $sent) {
            error_log('[AfiaZone] Email de verification non envoye a ' . $user['email'] . PHP_EOL, 3, BASE_PATH . '/storage/logs/app.log');
        }

        return $token;
    }

    public function verifyEmail(string $token): bool
    {
        $statement = Database::connection()->prepare(
            'SELECT id, user_id FROM email_verifications WHERE token_hash = :token_hash AND verified_at IS NULL AND expires_at > NOW() LIMIT 1'
        );
        $statement->execute(['token_hash' => hash('sha256', $token)]);
        $verification = $statement->fetch();

        if (! is_array($verification)) {
            return false;
        }

        $connection = Database::connection();
        $connection->beginTransaction();
        try {
            $userStatement = $connection->prepare("UPDATE users SET status = 'active', email_verified_at = NOW(), updated_at = NOW() WHERE id = :user_id AND status = 'pending'");
            $userStatement->execute(['user_id' => (int) $verification['user_id']]);
            $verificationStatement = $connection->prepare('UPDATE email_verifications SET verified_at = NOW() WHERE id = :id');
            $verificationStatement->execute(['id' => (int) $verification['id']]);
            $connection->commit();
            return true;
        } catch (\Throwable $exception) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }
            throw $exception;
        }
    }

    public function attempt(string $identifier, string $password, string $ipAddress): bool
    {
        $identifier = strtolower(trim($identifier));
        $connection = Database::connection();

        if ($this->isRateLimited($identifier, $ipAddress)) {
            return false;
        }

        $statement = $connection->prepare(
            'SELECT id, password_hash, email, full_name FROM users u INNER JOIN user_profiles p ON p.user_id = u.id WHERE (LOWER(email) = :identifier OR phone = :phone) AND status = :status LIMIT 1'
        );
        $statement->execute([
            'identifier' => $identifier,
            'phone' => $identifier,
            'status' => 'active',
        ]);
        $user = $statement->fetch();
        $valid = is_array($user) && password_verify($password, (string) $user['password_hash']);
        $this->recordAttempt($identifier, $ipAddress, $valid);

        if (! $valid) {
            return false;
        }

        Auth::login((int) $user['id']);
        $this->recordSession((int) $user['id'], $ipAddress);
        if (is_string($user['email']) && $user['email'] !== '') {
            $this->mailer()->sendNewSessionEmail((string) $user['email'], (string) $user['full_name'], $ipAddress);
        }
        return true;
    }

    public function requestPasswordReset(string $identifier): ?string
    {
        $identifier = strtolower(trim($identifier));
        $statement = Database::connection()->prepare(
            'SELECT id FROM users WHERE LOWER(email) = :identifier OR phone = :phone LIMIT 1'
        );
        $statement->execute(['identifier' => $identifier, 'phone' => $identifier]);
        $userId = $statement->fetchColumn();

        if ($userId === false) {
            return null;
        }

        $token = bin2hex(random_bytes(32));
        $tokenStatement = Database::connection()->prepare(
            'INSERT INTO password_resets (user_id, token_hash, expires_at, created_at) VALUES (:user_id, :token_hash, DATE_ADD(NOW(), INTERVAL 1 HOUR), NOW())'
        );
        $tokenStatement->execute([
            'user_id' => (int) $userId,
            'token_hash' => hash('sha256', $token),
        ]);

        $userStatement = Database::connection()->prepare(
            'SELECT email, full_name FROM users u INNER JOIN user_profiles p ON p.user_id = u.id WHERE u.id = :id LIMIT 1'
        );
        $userStatement->execute(['id' => (int) $userId]);
        $user = $userStatement->fetch();
        if (is_array($user) && is_string($user['email']) && $user['email'] !== '') {
            $resetUrl = rtrim((string) env('APP_URL', 'http://afyazone.test'), '/') . '/reset-password/' . $token;
            $this->mailer()->sendPasswordResetEmail((string) $user['email'], (string) $user['full_name'], $resetUrl);
        }

        return $token;
    }

    public function resetPassword(string $token, string $password): bool
    {
        $tokenStatement = Database::connection()->prepare(
            'SELECT id, user_id FROM password_resets WHERE token_hash = :token_hash AND used_at IS NULL AND expires_at > NOW() LIMIT 1'
        );
        $tokenStatement->execute(['token_hash' => hash('sha256', $token)]);
        $reset = $tokenStatement->fetch();

        if (! is_array($reset)) {
            return false;
        }

        $connection = Database::connection();
        $connection->beginTransaction();
        try {
            $passwordStatement = $connection->prepare('UPDATE users SET password_hash = :password_hash, updated_at = NOW() WHERE id = :user_id');
            $passwordStatement->execute([
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'user_id' => (int) $reset['user_id'],
            ]);
            $usedStatement = $connection->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = :id');
            $usedStatement->execute(['id' => (int) $reset['id']]);
            $connection->commit();
            return true;
        } catch (\Throwable $exception) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }
            throw $exception;
        }
    }

    public function resendVerificationEmail(string $email): ?string
    {
        $statement = Database::connection()->prepare(
            "SELECT id FROM users WHERE email = :email AND status = 'pending' LIMIT 1"
        );
        $statement->execute(['email' => strtolower(trim($email))]);
        $userId = $statement->fetchColumn();

        return $userId === false ? null : $this->sendVerificationEmail((int) $userId);
    }

    private function isRateLimited(string $identifier, string $ipAddress): bool
    {
        $statement = Database::connection()->prepare(
            'SELECT COUNT(*) FROM login_attempts WHERE identifier = :identifier AND ip_address = :ip_address AND successful = 0 AND attempted_at >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)'
        );
        $statement->execute(['identifier' => $identifier, 'ip_address' => $ipAddress]);

        return (int) $statement->fetchColumn() >= self::MAX_FAILED_ATTEMPTS;
    }

    private function recordAttempt(string $identifier, string $ipAddress, bool $successful): void
    {
        $statement = Database::connection()->prepare(
            'INSERT INTO login_attempts (identifier, ip_address, successful, attempted_at) VALUES (:identifier, :ip_address, :successful, NOW())'
        );
        $statement->execute([
            'identifier' => $identifier,
            'ip_address' => $ipAddress,
            'successful' => $successful ? 1 : 0,
        ]);
    }

    private function recordSession(int $userId, string $ipAddress): void
    {
        $sessionId = session_id();
        $statement = Database::connection()->prepare(
            'INSERT INTO user_sessions (user_id, session_hash, ip_address, user_agent, last_activity_at, created_at) VALUES (:user_id, :session_hash, :ip_address, :user_agent, NOW(), NOW())'
        );
        $statement->execute([
            'user_id' => $userId,
            'session_hash' => hash('sha256', $sessionId),
            'ip_address' => $ipAddress,
            'user_agent' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
        ]);
    }

    private function uuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    private function nullable(mixed $value): ?string
    {
        $value = is_string($value) ? trim($value) : $value;
        return $value === null || $value === '' ? null : (string) $value;
    }

    private function mailer(): Mailer
    {
        return new Mailer(require BASE_PATH . '/config/mail.php');
    }
}