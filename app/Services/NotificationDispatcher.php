<?php

namespace App\Services;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class NotificationDispatcher
{
    /**
     * Ambil pengguna aktif yang menerima notifikasi monitoring.
     *
     * @return Collection<int, User>
     */
    public function recipients(): Collection
    {
        return User::query()
            ->whereIn('role', [Role::Admin->value, Role::KepalaDinas->value])
            ->where('is_active', true)
            ->get();
    }

    /**
     * Ambil admin aktif yang menerima notifikasi data toko.
     *
     * @return Collection<int, User>
     */
    public function adminRecipients(): Collection
    {
        return User::query()
            ->where('role', Role::Admin->value)
            ->where('is_active', true)
            ->get();
    }

    /**
     * Kirim notifikasi hanya ke seluruh admin aktif.
     */
    public function sendToAdmin(Notification $notification): void
    {
        $recipients = $this->adminRecipients();

        if ($recipients->isNotEmpty()) {
            NotificationFacade::send($recipients, $notification);
        }
    }

    /**
     * Kirim notifikasi ke seluruh admin dan kepala dinas aktif.
     */
    public function sendToAdminAndKepalaDinas(Notification $notification): void
    {
        $recipients = $this->recipients();

        if ($recipients->isNotEmpty()) {
            NotificationFacade::send($recipients, $notification);
        }
    }
}
