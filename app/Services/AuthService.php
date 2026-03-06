<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\Token;
use App\Repositories\UserRepository;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthService extends BaseService
{
    private UserRepository $userRepo;
    private string $jwtSecret;
    private int $jwtTtl;

    public function __construct()
    {
        parent::__construct();
        $this->userRepo = new UserRepository();
        $this->jwtSecret = env('APP_KEY', 'change-me-in-production');
        $this->jwtTtl = (int) env('JWT_TTL', 3600);
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

        $user = User::create([
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password_hash' => password_hash($data['password'], PASSWORD_BCRYPT),
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'status' => 'pending_verification',
        ]);

        $user->assignRole('customer');

        $this->log('User registered', ['user_id' => $user->id]);

        $token = $this->generateJwt($user);

        return [
            'user' => $user->toArray(),
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

        return [
            'user' => $user->toArray(),
            'token' => $token,
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
        if (!$user) {
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

    public function verifyEmail(string $tokenHash): bool
    {
        $token = Token::findByHash($tokenHash);
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

    public function requestPasswordReset(string $email): bool
    {
        $user = $this->userRepo->findByEmail($email);
        if (!$user) {
            return true; // Don't reveal if email exists
        }

        Token::createForUser((int) $user->id, 'password_reset', 1800);
        $this->log('Password reset requested', ['user_id' => $user->id]);
        return true;
    }

    public function resetPassword(string $tokenHash, string $newPassword): bool
    {
        $token = Token::findByHash($tokenHash);
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
}
