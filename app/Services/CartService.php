<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ShoppingCart;
use App\Models\CartItem;
use App\Models\Product;

class CartService extends BaseService
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getCart(int $userId): ShoppingCart
    {
        return ShoppingCart::getOrCreate($userId);
    }

    public function getItems(int $userId): array
    {
        $cart = $this->getCart($userId);
        return $cart->getItems();
    }

    public function addItem(int $userId, int $productId, int $quantity = 1): CartItem
    {
        if ($quantity < 1) {
            $this->throwIfErrors(['quantity' => 'Quantity must be at least 1']);
        }

        $product = Product::find($productId);
        if (!$product || !$product->is_active) {
            throw new \App\Exceptions\NotFoundException('Product not found or unavailable');
        }

        $cart = $this->getCart($userId);

        // Check if item already in cart — update quantity
        $existing = CartItem::query()
            ->where('cart_id', $cart->id)
            ->where('product_id', $productId)
            ->first();

        if ($existing) {
            $newQty = (int) $existing->quantity + $quantity;
            $existing->update([
                'quantity' => $newQty,
                'price_at_add' => $product->price,
            ]);
            $cart->recalculateTotal();
            return $existing;
        }

        $item = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $productId,
            'quantity' => $quantity,
            'price_at_add' => $product->price,
        ]);

        $cart->recalculateTotal();

        $this->log('Item added to cart', [
            'user_id' => $userId,
            'product_id' => $productId,
            'quantity' => $quantity,
        ]);

        return $item;
    }

    public function updateItemQuantity(int $userId, int $itemId, int $quantity): bool
    {
        if ($quantity < 1) {
            return $this->removeItem($userId, $itemId);
        }

        $cart = $this->getCart($userId);
        $item = CartItem::find($itemId);

        if (!$item || (int) $item->cart_id !== (int) $cart->id) {
            throw new \App\Exceptions\NotFoundException('Cart item not found');
        }

        $item->update(['quantity' => $quantity]);
        $cart->recalculateTotal();

        return true;
    }

    public function removeItem(int $userId, int $itemId): bool
    {
        $cart = $this->getCart($userId);
        $item = CartItem::find($itemId);

        if (!$item || (int) $item->cart_id !== (int) $cart->id) {
            return false;
        }

        $item->delete();
        $cart->recalculateTotal();

        $this->log('Item removed from cart', ['user_id' => $userId, 'item_id' => $itemId]);
        return true;
    }

    public function clearCart(int $userId): bool
    {
        $cart = $this->getCart($userId);
        $items = $cart->getItems();

        foreach ($items as $item) {
            $item->delete();
        }

        $cart->update(['total_price' => 0]);
        return true;
    }
}
