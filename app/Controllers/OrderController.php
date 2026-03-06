<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\OrderService;

class OrderController extends BaseController
{
    private OrderService $orderService;

    public function __construct()
    {
        parent::__construct();
        $this->orderService = new OrderService();
    }

    public function index(): void
    {
        $this->requireAuth();
        $page = (int) ($this->getData('page') ?? 1);
        $perPage = (int) ($this->getData('per_page') ?? 20);

        $result = $this->orderService->getUserOrders($this->authUserId(), $page, $perPage);
        $this->jsonResponse($result);
    }

    public function show(int $id): void
    {
        $this->requireAuth();
        $order = $this->orderService->getById($id);

        if (!$order) {
            $this->errorResponse('Order not found', 404);
            return;
        }

        $this->jsonResponse(['order' => $order->toArray()]);
    }

    public function store(): void
    {
        $this->requireAuth();
        $order = $this->orderService->createFromCart($this->authUserId(), $this->getData());
        $this->jsonResponse(['order' => $order->toArray()], 201);
    }

    public function updateStatus(int $id): void
    {
        $this->authorize('orders.update_status');
        $this->orderService->updateStatus(
            $id,
            (string) $this->getData('status'),
            $this->authUserId(),
            (string) ($this->getData('notes') ?? '')
        );
        $this->jsonResponse(['message' => 'Status updated']);
    }

    public function cancel(int $id): void
    {
        $this->requireAuth();
        $this->orderService->cancel($id, $this->authUserId(), (string) ($this->getData('reason') ?? ''));
        $this->jsonResponse(['message' => 'Order cancelled']);
    }
}
