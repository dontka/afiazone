<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Order;

class OrderRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Order());
    }

    public function findByOrderNumber(string $orderNumber): ?Order
    {
        /** @var ?Order */
        return $this->findBy('order_number', $orderNumber);
    }

    public function findByUser(int $userId, int $page = 1, int $perPage = 20): array
    {
        return $this->query()
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->paginate($page, $perPage);
    }

    public function findByStatus(string $status, int $page = 1, int $perPage = 20): array
    {
        return $this->query()
            ->where('order_status', $status)
            ->orderBy('created_at', 'DESC')
            ->paginate($page, $perPage);
    }

    public function findByUserAndStatus(int $userId, string $status): array
    {
        return $this->query()
            ->where('user_id', $userId)
            ->where('order_status', $status)
            ->orderBy('created_at', 'DESC')
            ->get();
    }

    public function countByUser(int $userId): int
    {
        return $this->query()->where('user_id', $userId)->count();
    }
}
