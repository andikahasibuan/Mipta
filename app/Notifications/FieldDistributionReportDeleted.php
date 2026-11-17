<?php

namespace App\Notifications;

use App\Services\NotificationTemplateRenderer;
use Illuminate\Notifications\Notification;

class FieldDistributionReportDeleted extends Notification
{
    /**
     * Buat notifikasi penghapusan laporan pengawasan.
     */
    public function __construct(
        public string $reportDate,
        public string $marketName,
        public string $contributorName,
        public ?int $reportId = null,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $template = app(NotificationTemplateRenderer::class)->render('field_distribution_report_deleted', [
            'tanggal' => $this->reportDate,
            'nama kontributor pasar' => $this->contributorName,
        ]);

        return [
            ...$template,
            'report_date' => $this->reportDate,
            'market_name' => $this->marketName,
            'contributor_name' => $this->contributorName,
            'report_id' => $this->reportId,
        ];
    }
}
