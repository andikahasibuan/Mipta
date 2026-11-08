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

    /**
     * Periksa apakah peran ini kepala dinas.
     */
    public function isKepalaDinas(): bool
    {
        return $this === self::KepalaDinas;
    }

    /**
     * Nama route dashboard utama untuk peran ini, atau null bila peran tidak memiliki area.
     */
    public function homeRoute(): ?string
    {
        return match ($this) {
            self::Admin => 'admin.dashboard',
            self::KepalaDinas => 'kepala-dinas.dashboard',
            default => null,
        };
    }

    /**
     * Prefix URL area untuk peran ini, atau null bila peran tidak memiliki area.
     */
    public function routePrefix(): ?string
    {
        return match ($this) {
            self::Admin => 'admin',
            self::KepalaDinas => 'kepala-dinas',
            default => null,
        };
    }
}
