<?php

return [

    /*
    Informasi kontak WhatsApp yang ditampilkan pada halaman Pusat Bantuan.
    */

    'admin' => [
        'whatsapp' => env('MIPTA_ADMIN_WHATSAPP', '081234567890'),
        'service_hours' => env('MIPTA_ADMIN_SERVICE_HOURS', 'Senin - Jumat, 08.00 - 16.00 WIB'),
        'whatsapp_message' => env('MIPTA_ADMIN_WHATSAPP_MESSAGE', 'Halo Admin MIPTA, saya butuh bantuan. Username: ... Peran akun: ... Kendala: ...'),
    ],

];
