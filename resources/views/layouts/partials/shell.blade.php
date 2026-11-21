@php
    $activeMenu = collect($menus)->first(fn (array $menu) => request()->routeIs(...($menu['active'] ?? [$menu['route']])));
    $activeMenuLabel = trim($__env->yieldContent('active-menu-label'))
        ?: data_get($activeMenu, 'label', 'Dashboard Monitoring');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', 'MIPTA')</title>

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="h-dvh overflow-hidden bg-slate-50 text-slate-800 antialiased">
        <div class="flex h-dvh overflow-hidden">
            <div
                data-sidebar-backdrop
                aria-hidden="true"
                class="fixed inset-0 z-40 hidden bg-slate-900/40 backdrop-blur-sm lg:hidden"
            ></div>

            @include('layouts.partials.sidebar', ['menus' => $menus])

            <main class="flex min-h-0 min-w-0 flex-1 flex-col">
                @include('layouts.partials.header', ['activeMenuLabel' => $activeMenuLabel])

                <div id="app-content" class="flex-1 overflow-y-auto bg-slate-50 p-4 sm:p-6 lg:p-8">
                    @yield('content')
                </div>
            </main>
        </div>

        @stack('scripts')

        {{-- Popup konfirmasi custom MIPTA --}}
        <div
            id="confirmation-modal"
            class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-900/40 px-4 py-6 backdrop-blur-sm"
            aria-hidden="true"
        >
            <div
                class="w-full max-w-md rounded-2xl border border-slate-100 bg-white p-6 shadow-2xl shadow-slate-900/10"
                role="dialog"
                aria-modal="true"
                aria-labelledby="confirmation-title"
                aria-describedby="confirmation-message"
            >
                <div class="flex flex-col items-center text-center">
                    <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-[#E0F2F3] text-[#018F98]">
                        <x-heroicon-o-exclamation-triangle class="h-7 w-7" />
                    </div>

                    <h2
                        id="confirmation-title"
                        class="text-lg font-bold text-slate-800"
                    >
                        Konfirmasi Penghapusan
                    </h2>

                    <p
                        id="confirmation-message"
                        class="mt-2 text-sm leading-6 text-slate-500"
                    >
                        Apakah kamu yakin ingin menghapus data ini?
                    </p>
                </div>

                <div class="mt-7 flex items-center justify-center gap-3">
                    <button
                        type="button"
                        id="confirmation-cancel"
                        class="inline-flex flex-1 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
                    >
                        Batal
                    </button>

                    <button
                        type="button"
                        id="confirmation-accept"
                        class="inline-flex flex-1 items-center justify-center rounded-xl bg-red-500 px-4 py-3 text-sm font-semibold text-white transition hover:bg-red-600"
                    >
                        Ya, Hapus
                    </button>
                </div>
            </div>
        </div>

        {{-- Popup notifikasi berhasil dan gagal --}}
        <div
            id="notification-modal"
            class="fixed inset-0 z-[110] hidden items-center justify-center bg-slate-900/40 px-4 py-6 backdrop-blur-sm"
            aria-hidden="true"
        >
            <div
                class="w-full max-w-md rounded-2xl border border-slate-100 bg-white p-7 shadow-2xl shadow-slate-900/10"
                role="alertdialog"
                aria-modal="true"
                aria-labelledby="notification-title"
                aria-describedby="notification-message"
            >
                <div class="flex flex-col items-center text-center">
                    <div
                        id="notification-icon-wrapper"
                        class="mb-5 flex h-20 w-20 items-center justify-center rounded-full bg-[#E0F2F3] text-[#018F98]"
                    >
                        <svg
                            id="notification-success-icon"
                            class="h-12 w-12"
                            viewBox="0 0 52 52"
                            fill="none"
                            aria-hidden="true"
                        >
                            <circle
                                cx="26"
                                cy="26"
                                r="23"
                                stroke="currentColor"
                                stroke-width="2.5"
                            />
                            <path
                                class="notification-check-path"
                                d="M15 27L22 34L37 19"
                                stroke="currentColor"
                                stroke-width="3"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                fill="none"
                            />
                        </svg>

                        <svg
                            id="notification-error-icon"
                            class="hidden h-12 w-12"
                            viewBox="0 0 52 52"
                            fill="none"
                            aria-hidden="true"
                        >
                            <circle cx="26" cy="26" r="23" stroke="currentColor" stroke-width="2.5"/>
                            <path d="M18 18L34 34M34 18L18 34" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                        </svg>
                    </div>

                    <h2
                        id="notification-title"
                        class="text-xl font-bold text-slate-800"
                    >
                        Berhasil!
                    </h2>

                    <p
                        id="notification-message"
                        class="mt-2 text-sm leading-6 text-slate-500"
                    >
                        Data berhasil disimpan.
                    </p>
                </div>

                <div class="mt-7">
                    <button
                        type="button"
                        id="notification-close"
                        class="inline-flex w-full items-center justify-center rounded-xl bg-[#018F98] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#01777E] focus:outline-none focus:ring-2 focus:ring-[#018F98]/40"
                    >
                        Oke
                    </button>
                </div>
            </div>
        </div>
    </body>
</html>
