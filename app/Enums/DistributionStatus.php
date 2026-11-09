<?php

namespace App\Enums;

enum DistributionStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';

    /**
     * Label status laporan yang ditampilkan ke pengguna.
     */
    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draf',
            self::Submitted => 'Terkirim',
        };
    }
}
