<?php

namespace App\Enums;

enum ReportStatus: string
{
    case Menunggu = 'menunggu';
    case Terverifikasi = 'terverifikasi';
    case PerluDitinjau = 'perlu_ditinjau';

    /**
     * Label status laporan yang ditampilkan ke pengguna.
     */
    public function label(): string
    {
        return match ($this) {
            self::Menunggu => 'Menunggu',
            self::Terverifikasi => 'Terverifikasi',
            self::PerluDitinjau => 'Perlu Ditinjau',
        };
    }
}
