<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ShoppingCart;
use App\Models\BaseModel;
use App\Repositories\OrderRepository;

class OrderService extends BaseService
{
    private OrderRepository $orderRepo;

    public function __construct()
    {
        parent::__construct();
        $this->orderRepo = new OrderRepository();
    }

    public function getUserOrders(int $userId, int $page = 1, int $perPage = 20): array
    {
        return $this->orderRepo->findByUser($userId, $page, $perPage);
    }

    public function getById(int $id): ?Order
    {
        /** @var ?Order */
        return $this->orderRepo->find($id);
    }

    public function getByOrderNumber(string $orderNumber): ?Order
    {
        return $this->orderRepo->findByOrderNumber($orderNumber);
    }

    public function createFromCart(int $userId, array $data): Order
    {
        $errors = $this->validate($data, [
            'payment_method' => 'required|in:cash_on_delivery,wallet,card,mobile_money',
        ]);
        $this->throwIfErrors($errors);

        $cart = ShoppingCart::findByUserId($userId);
        if (!$cart) {
            throw new \App\Exceptions\NotFoundException('Cart not found');
        }

        $items = $cart->getItems();
        if (empty($items)) {
            $this->throwIfErrors(['cart' => 'Cart is empty']);
        }

        return BaseModel::transaction(function () use ($userId, $cart, $items, $data) {
            $totalAmount = 0.0;
            $taxAmount = 0.0;

            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $userId,
                'total_amount' => 0,
                'tax_amount' => 0,
                'shipping_fee' => $data['shipping_fee'] ?? 0,
                'discount_amount' => $data['discount_amount'] ?? 0,
                'final_amount' => 0,
                'payment_method' => $data['payment_method'],
                'payment_status' => 'pending',
                'order_status' => 'pending',
            ]);

            foreach ($items as $cartItem) {
                $subtotal = (float) $cartItem->price_at_add * (int) $cartItem->quantity;
                $itemTax = $subtotal * 0; // Tax calculated per product in future phases
                $totalAmount += $subtotal;
                $taxAmount += $itemTax;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'unit_price' => $cartItem->price_at_add,
                    'tax_amount' => $itemTax,
                    'subtotal' => $subtotal,
                ]);
            }

            $finalAmount = $totalAmount + $taxAmount + (float) ($data['shipping_fee'] ?? 0)
                         - (float) ($data['discount_amount'] ?? 0);

            $order->update([
                'total_amount' => $totalAmount,
                'tax_amount' => $taxAmount,
                'final_amount' => $finalAmount,
            ]);

            $order->logStatusChange('pending', null, 'Order created');

            // Clear cart after order creation
            foreach ($items as $item) {
                $item->delete();
            }
            $cart->update(['total_price' => 0]);

            $this->log('Order created', ['order_id' => $order->id, 'user_id' => $userId]);

            return $order;
        });
    }

    public function updateStatus(int $id, string $status, ?int $changedBy = null, string $notes = ''): bool
    {
        $order = $this->getById($id);
        if (!$order) {
            throw new \App\Exceptions\NotFoundException('Order not found');
        }

        $validTransitions = [
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['processing', 'cancelled'],
            'processing' => ['shipped', 'cancelled'],
            'shipped' => ['delivered', 'returned'],
            'delivered' => ['returned'],
        ];

        $currentStatus = $order->order_status;
        $allowed = $validTransitions[$currentStatus] ?? [];
        if (!in_array($status, $allowed, true)) {
            $this->throwIfErrors(['status' => "Cannot transition from {$currentStatus} to {$status}"]);
        }

        $order->logStatusChange($status, $changedBy, $notes);
        $result = $order->update(['order_status' => $status]);

        $this->log('Order status updated', ['order_id' => $id, 'status' => $status]);
        return $result;
    }

    public function cancel(int $id, ?int $userId = null, string $reason = ''): bool
    {
        return $this->updateStatus($id, 'cancelled', $userId, $reason);
    }
}
