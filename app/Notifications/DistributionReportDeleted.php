<?php

namespace App\Notifications;

use App\Services\NotificationTemplateRenderer;
use Illuminate\Notifications\Notification;

class DistributionReportDeleted extends Notification
{
    /**
     * Buat notifikasi penghapusan laporan penyaluran.
     */
    public function __construct(
        public string $distributionDate,
        public string $distributorName,
        public ?int $distributionId = null,
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
        $template = app(NotificationTemplateRenderer::class)->render('distribution_report_deleted', [
            'tanggal' => $this->distributionDate,
            'nama distributor' => $this->distributorName,
        ]);

        return [
            ...$template,
            'distribution_date' => $this->distributionDate,
            'distributor_name' => $this->distributorName,
            'distribution_id' => $this->distributionId,
        ];
    }
}
