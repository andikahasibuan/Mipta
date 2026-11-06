<?php

namespace App\Enums;

enum FieldCondition: string
{
    case Normal = 'normal';
    case PerluPerhatian = 'perlu_perhatian';

    /**
     * Label kondisi yang ditampilkan ke pengguna.
     */
    public function label(): string
    {
        return match ($this) {
            self::Normal => 'Normal',
            self::PerluPerhatian => 'Perlu Perhatian',
        };
    }
}
