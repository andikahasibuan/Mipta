<?php

namespace App\Services;

use Carbon\CarbonInterface;

class NotificationTimeFormatter
{
    private const TIMEZONE = 'Asia/Jakarta';

    /**
     * Format waktu notifikasi untuk card pemberitahuan.
     */
    public function format(?CarbonInterface $date): string
    {
        if ($date === null) {
            return '—';
        }

        $localDate = $date->copy()->setTimezone(self::TIMEZONE);
        $now = now(self::TIMEZONE);

        if ($localDate->isSameDay($now)) {
            return $localDate->format('H:i');
        }

        if ($localDate->greaterThan($now->copy()->subMonth())) {
            $days = max(1, (int) $localDate->diffInDays($now));

            return "{$days} hari yang lalu";
        }

        if ($localDate->greaterThan($now->copy()->subMonths(2))) {
            return '1 bulan yang lalu';
        }

        return '2 bulan yang lalu';
    }

    /**
     * Format tanggal lengkap untuk tooltip waktu.
     */
    public function formatFull(?CarbonInterface $date): string
    {
        if ($date === null) {
            return '—';
        }

        return $date->copy()
            ->setTimezone(self::TIMEZONE)
            ->format('d/m/Y H:i').' WIB';
    }
}
