<?php

declare(strict_types=1);

namespace App\Models;

class ShoppingCart extends BaseModel
{
    protected string $table = 'shopping_carts';

    protected array $fillable = [
        'user_id',
        'session_id',
        'total_price',
    ];

    public function getItems(): array
    {
        return $this->hasMany(CartItem::class, 'cart_id');
    }

    public function getUser(): ?User
    {
        /** @var ?User */
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function findByUserId(int $userId): ?self
    {
        return self::findBy('user_id', (string) $userId);
    }

    public static function findBySessionId(string $sessionId): ?self
    {
        return self::findBy('session_id', $sessionId);
    }

    public static function getOrCreate(int $userId): self
    {
        $cart = self::findByUserId($userId);
        if ($cart) {
            return $cart;
        }
        return self::create([
            'user_id' => $userId,
            'total_price' => 0,
        ]);
    }

    public function recalculateTotal(): void
    {
        $total = 0.0;
        foreach ($this->getItems() as $item) {
            $total += (float) $item->price_at_add * (int) $item->quantity;
        }
        $this->update(['total_price' => $total]);
    }
}
