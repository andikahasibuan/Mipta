<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Kontak Admin Pusat Bantuan
    |--------------------------------------------------------------------------
    |
    | Informasi kontak yang ditampilkan pada halaman Pusat Bantuan. Ubah nilai
    | berikut melalui variabel lingkungan agar tidak perlu menyunting tampilan.
    | Nomor WhatsApp boleh ditulis dengan awalan 0 dan akan dinormalkan.
    |
    */

    'admin' => [
        'whatsapp' => env('MIPTA_ADMIN_WHATSAPP', '081234567890'),
        'email' => env('MIPTA_ADMIN_EMAIL', 'admin@mipta.test'),
        'service_hours' => env('MIPTA_ADMIN_SERVICE_HOURS', 'Senin - Jumat, 08.00 - 16.00 WIB'),
        'whatsapp_message' => env('MIPTA_ADMIN_WHATSAPP_MESSAGE', 'Halo Admin MIPTA, saya butuh bantuan terkait akun saya.'),
    ],

];
