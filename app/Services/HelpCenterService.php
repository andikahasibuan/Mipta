<?php

namespace App\Services;

class HelpCenterService
{
    /**
     * Nomor WhatsApp admin dalam format internasional tanpa simbol.
     */
    public function whatsappNumber(): string
    {
        $digits = preg_replace('/\D+/', '', (string) config('help.admin.whatsapp')) ?? '';

        if (str_starts_with($digits, '0')) {
            return '62'.substr($digits, 1);
        }

        return $digits;
    }

    /**
     * Nomor WhatsApp admin dalam format yang mudah dibaca.
     */
    public function whatsappDisplay(): string
    {
        $number = $this->whatsappNumber();

        if ($number === '') {
            return '';
        }

        if (! str_starts_with($number, '62')) {
            return '+'.$number;
        }

        $local = substr($number, 2);

        $groups = array_filter([
            substr($local, 0, 3),
            substr($local, 3, 4),
            substr($local, 7),
        ], fn (string $group): bool => $group !== '');

        return '+62 '.implode('-', $groups);
    }

    /**
     * Pesan otomatis yang disertakan saat membuka WhatsApp admin.
     */
    public function whatsappMessage(): string
    {
        $message = trim((string) config('help.admin.whatsapp_message'));
        $serviceHours = trim((string) config('help.admin.service_hours'));

        if ($serviceHours !== '') {
            $message .= "\n\nJam layanan admin: {$serviceHours}.";
        }

        return trim($message);
    }

    /**
     * Tautan langsung ke percakapan WhatsApp admin.
     */
    public function whatsappUrl(): string
    {
        return 'https://wa.me/'.$this->whatsappNumber().'?text='.rawurlencode($this->whatsappMessage());
    }
}
