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

    public function findByUsername(string $username): ?User
    {
        /** @var ?User */
        return $this->findBy('username', $username);
    }

    public function findByUniqueId(string $uniqueId): ?User
    {
        /** @var ?User */
        return $this->findBy('unique_id', $uniqueId);
    }

    /**
     * Find user by any identifier (email, phone, username, or unique_id)
     */
    public function findByIdentifier(string $identifier): ?User
    {
        // Try by email first
        $user = $this->findByEmail($identifier);
        if ($user) {
            return $user;
        }

        // Try by phone
        $user = $this->findByPhone($identifier);
        if ($user) {
            return $user;
        }

        // Try by username
        $user = $this->findByUsername($identifier);
        if ($user) {
            return $user;
        }

        // Try by unique_id
        return $this->findByUniqueId($identifier);
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

    public function usernameExists(string $username): bool
    {
        return $this->exists('username', $username);
    }

    public function uniqueIdExists(string $uniqueId): bool
    {
        return $this->exists('unique_id', $uniqueId);
    }
}
