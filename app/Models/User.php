<?php

declare(strict_types=1);

namespace App\Models;

class User extends BaseModel
{
    protected string $table = 'users';
    protected string $primaryKey = 'id';

    protected array $fillable = [
        'email',
        'phone',
        'password_hash',
        'first_name',
        'last_name',
        'status',
        'email_verified_at',
        'phone_verified_at',
        'last_login_at',
    ];

    public function getRoles(): array
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id');
    }

    public function getRoleNames(): array
    {
        $roles = $this->getRoles();
        return array_map(fn($r) => $r->name, $roles);
    }

    public function hasRole(string $roleName): bool
    {
        return in_array($roleName, $this->getRoleNames(), true);
    }

    public function getPermissions(): array
    {
        $sql = "SELECT DISTINCT p.name FROM permissions p
                INNER JOIN role_permissions rp ON rp.permission_id = p.id
                INNER JOIN user_roles ur ON ur.role_id = rp.role_id
                WHERE ur.user_id = ?";
        $rows = self::raw($sql, [$this->id]);
        return array_column($rows, 'name');
    }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->getPermissions(), true);
    }

    public function getProfile(): ?BaseModel
    {
        return $this->hasOne(UserProfile::class, 'user_id');
    }

    public function getWallet(): ?Wallet
    {
        /** @var ?Wallet */
        return $this->hasOne(Wallet::class, 'user_id');
    }

    public function getOrders(): array
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    public function assignRole(string $roleName): void
    {
        $role = Role::findBy('name', $roleName);
        if ($role) {
            $db = db();
            $stmt = $db->prepare(
                'INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (?, ?)'
            );
            $stmt->execute([$this->id, $role->id]);
        }
    }

    public function getFullName(): string
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
    }

    public static function findByEmail(string $email): ?self
    {
        return self::findBy('email', $email);
    }

    public static function findByPhone(string $phone): ?self
    {
        return self::findBy('phone', $phone);
    }

    public function toArray(): array
    {
        $data = parent::toArray();
        unset($data['password_hash']);
        return $data;
    }
}
