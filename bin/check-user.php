<?php
declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';
(Dotenv\Dotenv::createImmutable(dirname(__DIR__)))->load();
require dirname(__DIR__) . '/app/helpers.php';

$pdo = db();
$stmt = $pdo->prepare('SELECT id, email, password_hash, status FROM users WHERE email = ?');
$stmt->execute(['client1@example.com']);
$u = $stmt->fetch(PDO::FETCH_ASSOC);
if ($u) {
    echo "Found: {$u['email']}  status={$u['status']}" . PHP_EOL;
    echo 'Password123! => ' . var_export(password_verify('Password123!', $u['password_hash']), true) . PHP_EOL;
    echo 'Password1!   => ' . var_export(password_verify('Password1!',   $u['password_hash']), true) . PHP_EOL;
} else {
    echo 'User not found' . PHP_EOL;
}
