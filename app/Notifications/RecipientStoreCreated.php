<?php

namespace App\Notifications;

use App\Services\NotificationTemplateRenderer;
use Illuminate\Notifications\Notification;

class RecipientStoreCreated extends Notification
{
    /**
     * Buat notifikasi toko penerima baru.
     */
    public function __construct(
        public string $storeName,
        public string $marketName,
        public string $distributorName,
        public ?int $storeId = null,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $template = app(NotificationTemplateRenderer::class)->render('recipient_store_created', [
            'nama toko' => $this->storeName,
            'nama distributor' => $this->distributorName,
        ]);

        return [
            ...$template,
            'store_name' => $this->storeName,
            'market_name' => $this->marketName,
            'distributor_name' => $this->distributorName,
            'store_id' => $this->storeId,
        ];
    }
}
