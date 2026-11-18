@extends('layouts.portal')

@section('title', 'Pusat Bantuan')

@section('content')
    @php
        $authUser = auth()->user();
        $backRoute = $authUser?->isSharedUptdAccount()
            ? 'uptd.contributors.index'
            : ($authUser?->role?->homeRoute() ?? 'login');
        $backLabel = $authUser ? 'Kembali ke Dashboard' : 'Kembali ke Login';

        $adminWhatsapp = (string) config('mipta.admin_whatsapp');
        $adminEmail = (string) config('mipta.admin_email');
        $adminWhatsappDigits = preg_replace('/\D+/', '', $adminWhatsapp) ?? '';

        if (str_starts_with($adminWhatsappDigits, '0')) {
            $adminWhatsappDigits = '62'.substr($adminWhatsappDigits, 1);
        }

        $adminWhatsappDisplay = '+'.$adminWhatsappDigits;

        if (str_starts_with($adminWhatsappDigits, '62')) {
            $localNumber = substr($adminWhatsappDigits, 2);
            $numberGroups = array_filter([
                substr($localNumber, 0, 3),
                substr($localNumber, 3, 4),
                substr($localNumber, 7),
            ], fn (string $group) => $group !== '');

            $adminWhatsappDisplay = '+62 '.implode('-', $numberGroups);
        }

        $helpMessage = 'Halo Admin MIPTA, saya butuh bantuan. Username: ... Peran akun: ... Kendala: ...';
        $whatsappUrl = 'https://wa.me/'.$adminWhatsappDigits.'?text='.rawurlencode($helpMessage);
        $emailUrl = 'mailto:'.$adminEmail.'?subject='.rawurlencode('Bantuan Akun MIPTA').'&body='.rawurlencode($helpMessage);
    @endphp

    <header class="w-full border-b border-slate-200 bg-white">
        <div class="flex w-full items-center justify-between gap-4 px-6 py-4 lg:px-8">
            <div class="flex items-center gap-3">
                <img
                    src="{{ asset('images/mipta-logo.png') }}"
                    alt="MIPTA"
                    class="h-10 w-10 shrink-0 object-contain"
                >
                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-slate-900">MIPTA</p>
                    <p class="truncate text-xs text-slate-500">Dinas Perdagangan Kota Padang</p>
                </div>
            </div>

            <a
                href="{{ route($backRoute) }}"
                class="inline-flex shrink-0 items-center gap-2 rounded-lg border border-[#B3E5E8] bg-[#E0F2F3] px-3.5 py-2.5 text-sm font-semibold text-[#018F98] transition hover:bg-[#B3E5E8] focus:outline-none focus:ring-2 focus:ring-[#018F98]/50"
            >
                <x-heroicon-o-arrow-left class="h-4 w-4" />
                {{ $backLabel }}
            </a>
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-4 py-6 sm:px-6 sm:py-8">
        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-[0_2px_12px_rgba(15,23,42,0.06)]">
            <div class="border-b border-slate-100 px-5 py-5 sm:px-6 sm:py-6">
                <h1 class="text-2xl font-semibold text-slate-900">Pusat Bantuan</h1>
                <p class="mt-2 max-w-2xl text-sm text-slate-600">
                    Login MIPTA menggunakan username, bukan email. Jika lupa kata sandi, akun nonaktif,
                    atau mengalami kendala lain, silakan hubungi admin melalui kontak di bawah ini.
                    Saat menghubungi admin, sebutkan username dan peran akun Anda agar pengecekan lebih cepat.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-5 px-5 py-6 sm:px-6 sm:py-7 lg:grid-cols-2">
                <article class="flex h-full flex-col justify-between rounded-lg border border-[#B3E5E8] bg-[#E0F2F3] p-5">
                    <div>
                        <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-[#018F98]/10 text-[#018F98] ring-1 ring-inset ring-[#018F98]/20">
                            <x-heroicon-o-chat-bubble-left-right class="h-6 w-6" />
                        </span>
                        <h2 class="mt-3 text-base font-semibold text-slate-900">WhatsApp Admin</h2>
                        <p class="mt-1 text-sm text-slate-600">Balasan diberikan pada jam layanan.</p>
                        <p class="mt-3 text-lg font-semibold text-[#016B72]">{{ $adminWhatsappDisplay }}</p>
                    </div>

                    <a
                        href="{{ $whatsappUrl }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-md bg-[#018F98] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#017A81] focus:outline-none focus:ring-2 focus:ring-[#018F98]/50"
                    >
                        <x-heroicon-o-chat-bubble-left-right class="h-4 w-4" />
                        Hubungi via WhatsApp
                    </a>
                </article>

                <article class="flex h-full flex-col justify-between rounded-lg border border-slate-200 bg-white p-5">
                    <div>
                        <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-[#FDBA12]/15 text-[#B45309] ring-1 ring-inset ring-[#FDBA12]/40">
                            <x-heroicon-o-envelope class="h-6 w-6" />
                        </span>
                        <h2 class="mt-3 text-base font-semibold text-slate-900">Email Admin</h2>
                        <p class="mt-1 text-sm text-slate-600">Cocok untuk kendala yang butuh lampiran data.</p>
                        <p class="mt-3 break-all text-lg font-semibold text-[#016B72]">{{ $adminEmail }}</p>
                    </div>

                    <a
                        href="{{ $emailUrl }}"
                        class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-md border border-[#018F98] bg-white px-4 py-2.5 text-sm font-semibold text-[#018F98] transition hover:bg-[#E0F2F3] focus:outline-none focus:ring-2 focus:ring-[#018F98]/50"
                    >
                        <x-heroicon-o-envelope class="h-4 w-4" />
                        Kirim Email
                    </a>
                </article>

                <article class="rounded-lg border border-slate-200 bg-white p-5 lg:col-span-2">
                    <div class="flex items-start gap-3">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-[#FDBA12]/20 text-[#B45309]">
                            <x-heroicon-o-clock class="h-6 w-6" />
                        </span>
                        <div class="min-w-0">
                            <h2 class="text-base font-semibold text-slate-900">Jam Layanan</h2>
                            <p class="mt-1 text-sm font-medium text-slate-700">{{ $serviceHours }}</p>
                            <p class="mt-1 text-sm text-slate-500">
                                Pesan di luar jam layanan akan dibalas pada hari kerja berikutnya.
                            </p>
                        </div>
                    </div>
                </article>
            </div>
        </section>
    </main>
@endsection
