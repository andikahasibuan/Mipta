<?php

namespace App\Enums;

enum Role: string
{
    case Admin = 'admin';
    case Distributor = 'distributor';
    case KepalaDinas = 'kepala_dinas';
    case UptdPasar = 'uptd_pasar';
}
