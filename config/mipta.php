<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Kontak Admin
    |--------------------------------------------------------------------------
    |
    | Nomor WhatsApp dan email admin yang ditampilkan pada halaman Pusat
    | Bantuan. Nomor WhatsApp boleh ditulis dengan awalan 0 dan akan
    | dinormalkan menjadi format internasional saat dibuat tautan wa.me.
    |
    */

    'admin_whatsapp' => env('ADMIN_WHATSAPP', '081234567890'),

    'admin_email' => env('ADMIN_EMAIL', 'admin@mipta.test'),

];
