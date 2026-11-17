<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\DatabaseNotification;

class NotificationFeedService
{
    /**
     * Ringkasan notifikasi untuk topbar.
     *
     * @return array{notifications: Collection<int, DatabaseNotification>, unread_count: int}
     */
    public function summaryFor(User $user): array
    {
        return [
            'notifications' => $user->notifications()->latest()->take(5)->get(),
            'unread_count' => $user->unreadNotifications()->count(),
        ];
    }
}
