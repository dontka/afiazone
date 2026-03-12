<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;

class UserRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new User());
    }

    public function findByEmail(string $email): ?User
    {
        /** @var ?User */
        return $this->findBy('email', $email);
    }

    public function findByPhone(string $phone): ?User
    {
        /** @var ?User */
        return $this->findBy('phone', $phone);
    }

    public function findActive(): array
    {
        return $this->query()->where('status', 'active')->get();
    }

    public function searchByName(string $name, int $limit = 20): array
    {
        $pattern = '%' . $name . '%';
        return $this->query()
            ->where('first_name', 'LIKE', $pattern)
            ->limit($limit)
            ->get();
    }

    public function emailExists(string $email): bool
    {
        return $this->exists('email', $email);
    }

    public function phoneExists(string $phone): bool
    {
        return $this->exists('phone', $phone);
    }
}
