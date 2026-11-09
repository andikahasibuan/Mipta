<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class DistributionReportDeleted extends Notification
{
    /**
     * Buat notifikasi penghapusan laporan penyaluran.
     */
    public function __construct(
        public string $reportCode,
        public string $distributionDate,
        public string $distributorName,
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
            'title' => 'Laporan penyaluran dihapus',
            'message' => sprintf(
                'Laporan %s tanggal %s telah dihapus oleh %s.',
                $this->reportCode,
                $this->distributionDate,
                $this->distributorName,
            ),
            'report_code' => $this->reportCode,
            'distribution_date' => $this->distributionDate,
            'distributor_name' => $this->distributorName,
        ];
    }
}
