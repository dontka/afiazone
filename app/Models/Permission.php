<?php

declare(strict_types=1);

namespace App\Models;

class Permission extends BaseModel
{
    protected string $table = 'permissions';

    protected array $fillable = [
        'name',
        'description',
    ];

    public static function findByName(string $name): ?self
    {
        return self::findBy('name', $name);
    }
}
