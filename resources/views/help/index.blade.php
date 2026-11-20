@extends('layouts.portal')

@section('title', 'Pusat Bantuan')

@section('content')
    <header class="w-full border-b border-slate-200 bg-white">
        <div class="flex w-full items-center justify-between gap-4 px-6 py-4 lg:px-8">
            <div class="flex items-center">
                <img
                    src="{{ asset('images/mipta-logo.png') }}"
                    alt="MIPTA"
                    class="h-10 w-10 shrink-0 object-contain"
                >
            </div>

            <a
                href="{{ route($backRoute) }}"
                class="inline-flex shrink-0 cursor-pointer items-center gap-2 rounded-lg bg-red-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500/50"
            >
                <x-heroicon-o-arrow-left class="h-4 w-4" />
                {{ $backLabel }}
            </a>
        </div>
    </header>

    <main class="mx-auto max-w-4xl px-4 py-6 sm:px-6 sm:py-8">
        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-[0_2px_12px_rgba(15,23,42,0.06)]">
            <div class="px-5 py-5 sm:px-6 sm:py-6">
                <h1 class="text-2xl font-semibold text-slate-900">Pusat Bantuan</h1>

                <div class="mt-4 grid grid-cols-1 gap-5 lg:grid-cols-2">
                    <article class="flex h-full flex-col justify-between rounded-lg border border-[#B3E5E8] bg-[#E0F2F3] p-5">
                        <div>
                            <span class="inline-flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-[#018F98]/10 text-[#018F98] ring-1 ring-inset ring-[#018F98]/20">
                                <x-heroicon-o-chat-bubble-left-right class="h-6 w-6" />
                            </span>
                            <h2 class="mt-3 text-base font-semibold text-slate-900">WhatsApp Admin</h2>
                            <p class="mt-1 text-sm text-slate-600">Balasan diberikan pada jam layanan.</p>
                            <p class="mt-3 text-lg font-semibold text-[#016B72]">{{ $helpWhatsappDisplay }}</p>
                        </div>

                        <a
                            href="{{ $helpWhatsappUrl }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-md bg-[#018F98] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#017A81] focus:outline-none focus:ring-2 focus:ring-[#018F98]/50"
                        >
                            <x-heroicon-o-chat-bubble-left-right class="h-4 w-4" />
                            Hubungi via WhatsApp
                        </a>
                    </article>

                    <div class="flex h-full flex-col rounded-lg border border-slate-200 bg-white p-5">
                        <h2 class="text-base font-semibold text-slate-900">Panduan</h2>
                        <p class="mt-3 text-justify text-sm leading-6 text-slate-600">
                            Login MIPTA dapat menggunakan email atau username yang terdaftar. Jika lupa kata sandi,
                            akun nonaktif, atau mengalami kendala lain, silakan hubungi admin melalui WhatsApp.
                            Saat menghubungi admin, sebutkan email atau username dan peran akun Anda agar
                            penanganan dapat dilakukan lebih cepat.
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection
