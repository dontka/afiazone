<?php

declare(strict_types=1);

namespace App\Core;

class Session
{
    public static function start(string $path, bool $secure = false): void
    {
        if (session_status() !== PHP_SESSION_NONE) {
            return;
        }

        if (is_dir($path)) {
            session_save_path($path);
        }

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return ($_SESSION ?? [])[$key] ?? $default;
    }

    public static function put(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function has(string $key): bool
    {
        return array_key_exists($key, $_SESSION ?? []);
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function regenerate(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    public static function flash(string $key, mixed $value): void
    {
        self::put('_flash.' . $key, $value);
    }

    public static function consumeFlash(string $key, mixed $default = null): mixed
    {
        $flashKey = '_flash.' . $key;
        $value = self::get($flashKey, $default);
        self::forget($flashKey);

        return $value;
    }
}