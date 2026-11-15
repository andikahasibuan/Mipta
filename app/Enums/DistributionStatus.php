<?php

namespace App\Enums;

enum DistributionStatus: string
{
    case Submitted = 'submitted';

    /**
     * Label status laporan yang ditampilkan ke pengguna.
     */
    public function label(): string
    {
        return 'Terkirim';
    }
}
