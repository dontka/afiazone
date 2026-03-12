#!/usr/bin/env php
<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/vendor/autoload.php';
require_once BASE_PATH . '/app/helpers.php';

if (file_exists(BASE_PATH . '/.env')) {
    \Dotenv\Dotenv::createImmutable(BASE_PATH)->safeLoad();
}

$service = new \App\Services\AuthService();
$pass = 0;
$fail = 0;

function test(string $name, callable $fn): void
{
    global $pass, $fail;
    echo "  {$name} ... ";
    try {
        $fn();
        echo "OK\n";
        $pass++;
    } catch (\Throwable $e) {
        echo "FAIL: {$e->getMessage()}\n";
        $fail++;
    }
}

function assert_true(bool $cond, string $msg = ''): void
{
    if (!$cond) throw new \RuntimeException("Assertion failed" . ($msg ? ": {$msg}" : ''));
}

echo "🧪 Phase D — Auth Tests\n";
echo "========================\n\n";

// ── Test 1: Login ───────────────────────────────
$token = '';
test('Admin login', function () use ($service, &$token) {
    $result = $service->login('admin@afiazone.com', 'Admin123!');
    assert_true(!empty($result['token']), 'token missing');
    assert_true(in_array('admin', $result['user']['roles']), 'admin role missing');
    assert_true($result['email_verified'] === true, 'email not verified');
    $token = $result['token'];
});

// ── Test 2: JWT validation ──────────────────────
test('JWT validate', function () use ($service, &$token) {
    $payload = $service->validateToken($token);
    assert_true($payload !== null, 'payload null');
    assert_true((int)$payload['sub'] === 1, 'wrong sub');
    assert_true(in_array('admin', $payload['roles']), 'roles missing');
});

// ── Test 3: Refresh token ───────────────────────
test('Refresh token', function () use ($service, &$token) {
    $newToken = $service->refreshToken($token);
    assert_true($newToken !== null, 'null token');
    assert_true($newToken !== $token, 'same token');
});

// ── Test 4: Wrong password ──────────────────────
test('Wrong password → UnauthorizedException', function () use ($service) {
    try {
        $service->login('admin@afiazone.com', 'wrongpassword');
        throw new \RuntimeException('Should have thrown');
    } catch (\App\Exceptions\UnauthorizedException $e) {
        assert_true($e->getStatusCode() === 401);
    }
});

// ── Test 5: Register ────────────────────────────
test('Register new user', function () use ($service) {
    $result = $service->register([
        'email' => 'testuser@example.com',
        'password' => 'SecurePass123',
        'first_name' => 'Test',
        'last_name' => 'User',
    ]);
    assert_true(!empty($result['token']), 'no token');
    assert_true($result['user']['status'] === 'pending_verification', 'wrong status');
    assert_true(in_array('customer', $result['user']['roles']), 'customer role missing');
});

// ── Test 6: Duplicate email ─────────────────────
test('Duplicate email → ValidationException', function () use ($service) {
    try {
        $service->register([
            'email' => 'testuser@example.com',
            'password' => 'SecurePass123',
            'first_name' => 'Dup',
            'last_name' => 'User',
        ]);
        throw new \RuntimeException('Should have thrown');
    } catch (\App\Exceptions\ValidationException $e) {
        assert_true(isset($e->getErrors()['email']));
    }
});

// ── Test 7: Password reset request ──────────────
test('Password reset request (non-revealing)', function () use ($service) {
    $result = $service->requestPasswordReset('admin@afiazone.com');
    assert_true($result['sent'] === true);
    $result2 = $service->requestPasswordReset('nonexistent@example.com');
    assert_true($result2['sent'] === true, 'should not reveal if email exists');
});

// ── Test 8: Logout (blacklist) ──────────────────
test('Logout blacklists token', function () use ($service, &$token) {
    assert_true(!$service->isTokenBlacklisted($token), 'already blacklisted');
    $service->logout($token);
    assert_true($service->isTokenBlacklisted($token), 'not blacklisted');
});

// ── Test 9: RBAC helpers ────────────────────────
test('User roles & permissions', function () {
    $user = \App\Models\User::find(1);
    assert_true($user !== null, 'user not found');
    assert_true($user->hasRole('admin'), 'no admin role');
    assert_true($user->hasPermission('manage_users'), 'no manage_users');
    assert_true(!$user->hasRole('customer'), 'should not be customer');
});

// ── Test 10: Banned user ────────────────────────
test('Banned user → ForbiddenException', function () use ($service) {
    $user = \App\Models\User::findByEmail('testuser@example.com');
    $user->update(['status' => 'banned']);
    try {
        $service->login('testuser@example.com', 'SecurePass123');
        throw new \RuntimeException('Should have thrown');
    } catch (\App\Exceptions\ForbiddenException $e) {
        assert_true($e->getStatusCode() === 403);
    }
    // Restore
    $user->update(['status' => 'pending_verification']);
});

// ── Summary ─────────────────────────────────────
echo "\n" . str_repeat('─', 40) . "\n";
echo "Results: {$pass} passed, {$fail} failed\n";
exit($fail > 0 ? 1 : 0);
