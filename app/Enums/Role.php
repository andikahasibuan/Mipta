<?php

namespace App\Enums;

enum Role: string
{
    case Admin = 'admin';
    case Distributor = 'distributor';
    case KepalaDinas = 'kepala_dinas';
    case UptdPasar = 'uptd_pasar';

    /**
     * Label peran yang ditampilkan ke pengguna.
     */
    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Distributor => 'Distributor',
            self::KepalaDinas => 'Kepala Dinas',
            self::UptdPasar => 'UPTD Pasar',
        };
    }
}
