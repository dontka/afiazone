<?php

declare(strict_types=1);

namespace App\Models;

class Role extends BaseModel
{
    protected string $table = 'roles';

    protected array $fillable = [
        'name',
        'description',
    ];

    public function getPermissions(): array
    {
        return $this->belongsToMany(Permission::class, 'role_permissions', 'role_id', 'permission_id');
    }

    public function getUsers(): array
    {
        return $this->belongsToMany(User::class, 'user_roles', 'role_id', 'user_id');
    }

    public static function findByName(string $name): ?self
    {
        return self::findBy('name', $name);
    }
}
