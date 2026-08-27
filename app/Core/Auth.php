<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

class Auth
{
    private const SESSION_KEY = '_auth_user_id';

    public static function login(int $userId): void
    {
        Session::regenerate();
        Session::put(self::SESSION_KEY, $userId);
    }

    public static function logout(): void
    {
        Session::forget(self::SESSION_KEY);
        Session::regenerate();
    }

    public static function id(): ?int
    {
        $userId = Session::get(self::SESSION_KEY);

        return filter_var($userId, FILTER_VALIDATE_INT) !== false ? (int) $userId : null;
    }

    public static function check(): bool
    {
        return self::id() !== null;
    }

    public static function user(): ?array
    {
        $userId = self::id();
        if ($userId === null) {
            return null;
        }

        $statement = Database::connection()->prepare(
            'SELECT u.id, u.uuid, u.email, u.phone, u.status, p.full_name, p.business_name FROM users u LEFT JOIN user_profiles p ON p.user_id = u.id WHERE u.id = :id AND u.status = :status LIMIT 1'
        );
        $statement->execute(['id' => $userId, 'status' => 'active']);
        $user = $statement->fetch();

        return is_array($user) ? $user : null;
    }

    public static function hasAnyRole(array $roles): bool
    {
        foreach ($roles as $role) {
            if (self::hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    public static function hasRole(string $role): bool
    {
        return self::hasRelation(
            'SELECT 1 FROM user_roles ur INNER JOIN roles r ON r.id = ur.role_id WHERE ur.user_id = :user_id AND r.name = :name LIMIT 1',
            $role
        );
    }

    public static function hasPermission(string $permission): bool
    {
        return self::hasRelation(
            'SELECT 1 FROM user_roles ur INNER JOIN role_permissions rp ON rp.role_id = ur.role_id INNER JOIN permissions p ON p.id = rp.permission_id WHERE ur.user_id = :user_id AND p.name = :name LIMIT 1',
            $permission
        );
    }

    private static function hasRelation(string $sql, string $name): bool
    {
        $userId = self::id();
        if ($userId === null) {
            return false;
        }

        $statement = Database::connection()->prepare($sql);
        $statement->execute(['user_id' => $userId, 'name' => $name]);

        return $statement->fetchColumn() !== false;
    }
}