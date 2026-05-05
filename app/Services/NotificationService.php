<?php

namespace App\Services;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationService
{
    /**
     * Send notification to a user.
     */
    public function send(int $userId, string $type, string $title, string $body, array $data = [])
    {
        return Notification::create([
            'user_id' => $userId,
            'type'    => $type,
            'title'   => $title,
            'body'    => $body,
            'data'    => $data,
            'is_read' => false,
            'created_at' => now(),
        ]);
    }

    /**
     * Get user notifications.
     */
    public function getNotifications(int $limit = 10)
    {
        return Notification::where('user_id', Auth::id())
            ->latest('created_at')
            ->paginate($limit);
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(int $notificationId)
    {
        return Notification::where('user_id', Auth::id())
            ->where('id', $notificationId)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);
    }

    /**
     * Mark all as read.
     */
    public function markAllAsRead()
    {
        return Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);
    }
}
