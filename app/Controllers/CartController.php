<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\CartService;

class CartController extends BaseController
{
    private CartService $cartService;

    public function __construct()
    {
        parent::__construct();
        $this->cartService = new CartService();
    }

    public function index(): void
    {
        $this->requireAuth();
        $items = $this->cartService->getItems($this->authUserId());
        $this->jsonResponse([
            'items' => array_map(fn($i) => $i->toArray(), $items),
        ]);
    }

    public function addItem(): void
    {
        $this->requireAuth();
        $item = $this->cartService->addItem(
            $this->authUserId(),
            (int) $this->getData('product_id'),
            (int) ($this->getData('quantity') ?? 1)
        );
        $this->jsonResponse(['item' => $item->toArray()], 201);
    }

    public function updateItem(int $itemId): void
    {
        $this->requireAuth();
        $this->cartService->updateItemQuantity(
            $this->authUserId(),
            $itemId,
            (int) $this->getData('quantity')
        );
        $this->jsonResponse(['message' => 'Cart item updated']);
    }

    public function removeItem(int $itemId): void
    {
        $this->requireAuth();
        $this->cartService->removeItem($this->authUserId(), $itemId);
        $this->jsonResponse(['message' => 'Item removed from cart']);
    }

    public function clear(): void
    {
        $this->requireAuth();
        $this->cartService->clearCart($this->authUserId());
        $this->jsonResponse(['message' => 'Cart cleared']);
    }
}
