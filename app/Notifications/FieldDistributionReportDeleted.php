<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class FieldDistributionReportDeleted extends Notification
{
    /**
     * Buat notifikasi penghapusan laporan kondisi lapangan.
     */
    public function __construct(
        public string $reportCode,
        public string $reportDate,
        public string $marketName,
        public string $contributorName,
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
        return [
            'title' => 'Laporan kondisi lapangan dihapus',
            'message' => sprintf(
                'Laporan kondisi lapangan %s tanggal %s di %s telah dihapus oleh %s.',
                $this->reportCode,
                $this->reportDate,
                $this->marketName,
                $this->contributorName,
            ),
            'report_code' => $this->reportCode,
            'report_date' => $this->reportDate,
            'market_name' => $this->marketName,
            'contributor_name' => $this->contributorName,
        ];
    }
}
