<?php

declare(strict_types=1);

namespace App\Models;

class Notification extends BaseModel
{
    protected string $table = 'notifications';

    protected array $fillable = [
        'user_id',
        'notification_type',
        'title',
        'message',
        'is_read',
        'action_url',
    ];

    public static function unreadCount(int $userId): int
    {
        return self::query()
            ->where('user_id', $userId)
            ->where('is_read', false)
            ->count();
    }

    public function markRead(): bool
    {
        return $this->update([
            'is_read' => true,
            'read_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
