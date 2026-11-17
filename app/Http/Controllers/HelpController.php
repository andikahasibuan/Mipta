<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class HelpController extends Controller
{
    /**
     * Tampilkan halaman pusat bantuan beserta kontak admin.
     */
    public function __invoke(): View
    {
        $whatsappNumber = $this->normalizeWhatsappNumber((string) config('help.admin.whatsapp'));

        return view('help.index', [
            'whatsappDisplay' => $this->formatWhatsappNumber($whatsappNumber),
            'whatsappUrl' => 'https://wa.me/'.$whatsappNumber.'?text='.rawurlencode((string) config('help.admin.whatsapp_message')),
            'adminEmail' => (string) config('help.admin.email'),
            'serviceHours' => (string) config('help.admin.service_hours'),
        ]);
    }

    /**
     * Normalkan nomor WhatsApp menjadi format internasional tanpa simbol.
     */
    private function normalizeWhatsappNumber(string $number): string
    {
        $digits = preg_replace('/\D+/', '', $number) ?? '';

        if (str_starts_with($digits, '0')) {
            return '62'.substr($digits, 1);
        }

        return $digits;
    }

    /**
     * Format nomor WhatsApp agar mudah dibaca pengguna.
     */
    private function formatWhatsappNumber(string $number): string
    {
        if (! str_starts_with($number, '62')) {
            return '+'.$number;
        }

        $local = substr($number, 2);

        $groups = array_filter([
            substr($local, 0, 3),
            substr($local, 3, 4),
            substr($local, 7),
        ], fn (string $group) => $group !== '');

        return '+62 '.implode('-', $groups);
    }
}
