@php
    // Definisi menu sidebar admin.
    $menus = [
        ['label' => 'Dashboard Monitoring', 'route' => 'admin.dashboard', 'icon' => 'squares-2x2'],
        ['label' => 'Pengguna & Data Master', 'route' => 'admin.users.index', 'icon' => 'users'],
        ['label' => 'Laporan Penyaluran', 'route' => 'admin.distribution.index', 'icon' => 'document-text'],
        ['label' => 'Monitoring & Analisis', 'route' => 'admin.monitoring.index', 'icon' => 'chart-bar'],
        ['label' => 'Pengawasan & Tindak Lanjut', 'route' => 'admin.supervision.index', 'icon' => 'shield-check'],
        ['label' => 'Rekap & Ekspor Laporan', 'route' => 'admin.reports.index', 'icon' => 'arrow-down-tray'],
        ['label' => 'Riwayat Aktivitas Sistem', 'route' => 'admin.activity.index', 'icon' => 'clock'],
        ['label' => 'Manajemen Profil', 'route' => 'admin.profile.edit', 'icon' => 'user-circle'],
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('title', 'MIPTA')</title>

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="h-screen overflow-hidden bg-slate-50 text-slate-800 antialiased">
        <div class="flex h-screen overflow-hidden">
            <aside class="flex h-full w-64 shrink-0 flex-col overflow-y-auto border-r border-blue-200 bg-[linear-gradient(to_bottom,#5191E4_3%,#2D507E_13%)]">
                <div class="px-6 py-6">
                    <div class="mx-auto w-30">
                        <img
                            src="{{ asset('images/mipta-logo.png') }}"
                            alt="MIPTA"
                            class="block h-auto w-full drop-shadow-[0_2px_4px_rgba(0,0,0,0.35)]"
                        >
                    </div>
                </div>

                <nav class="flex-1 px-3 pb-6" aria-label="Menu utama">
                    <ul class="flex flex-col gap-1">
                        @foreach ($menus as $menu)
                            <li>
                                <a
                                    href="{{ route($menu['route']) }}"
                                    @if (request()->routeIs($menu['route'])) aria-current="page" @endif
                                    @class([
                                        'flex items-center gap-3 rounded-md px-3 py-2 text-sm transition',
                                        'bg-[#FDBA12]/40 font-semibold text-white' => request()->routeIs($menu['route']),
                                        'text-[#A9C0D6] hover:bg-[#FDBA12]/40 hover:font-semibold hover:text-white' => ! request()->routeIs($menu['route']),
                                    ])
                                >
                                    <x-dynamic-component
                                        :component="'heroicon-o-'.$menu['icon']"
                                        class="h-5 w-5 shrink-0"
                                        data-icon="{{ $menu['icon'] }}"
                                    />
                                    <span class="min-w-0 truncate">{{ $menu['label'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </nav>

                <div class="border-t border-blue-200 px-3 py-4">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button
                            type="submit"
                            class="w-full cursor-pointer rounded-md px-3 py-2 text-left text-sm font-medium text-[#A9C0D6] transition hover:bg-[#FDBA12]/40 hover:text-white"
                        >
                            Keluar dari Sistem
                        </button>
                    </form>
                </div>
            </aside>

            <main class="flex min-h-0 min-w-0 flex-1 flex-col">
                <header class="sticky top-0 z-10 flex items-center justify-between gap-4 border-b border-slate-200 bg-white px-6 py-5 lg:px-8">
                    <div class="min-w-0">
                        <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-400">
                            Sistem Monitoring Distribusi
                        </p>
                        <h1 class="mt-1 truncate text-2xl font-semibold text-slate-900">
                            @hasSection('page-title')
                                @yield('page-title')
                            @else
                                @yield('title', 'Dashboard Monitoring')
                            @endif
                        </h1>
                    </div>

                    @auth
                        <details class="relative shrink-0" data-profile-menu>
                            <summary
                                aria-label="Menu profil"
                                class="flex cursor-pointer list-none items-center gap-3 rounded-lg border border-slate-200 bg-white px-3 py-2 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#5191E4]/50 [&::-webkit-details-marker]:hidden"
                            >
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[#2D507E] text-sm font-semibold text-white">
                                    {{ auth()->user()->initials() }}
                                </span>
                                <span class="hidden min-w-0 text-left sm:block">
                                    <span class="block max-w-[9rem] truncate text-sm font-semibold text-slate-900">{{ auth()->user()->name }}</span>
                                    <span class="block max-w-[9rem] truncate text-xs text-slate-500">{{ auth()->user()->role->label() }}</span>
                                </span>
                                <svg class="h-4 w-4 shrink-0 text-slate-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                                </svg>
                            </summary>

                            <div class="absolute right-0 z-20 mt-2 w-64 rounded-lg border border-slate-200 bg-white p-4 shadow-lg">
                                <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-400">Masuk sebagai</p>
                                <p class="mt-1 truncate text-sm font-medium text-slate-900">{{ auth()->user()->email }}</p>

                                <form method="POST" action="{{ route('logout') }}" class="mt-4">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="w-full cursor-pointer rounded-md bg-[#2D507E] px-3 py-2 text-sm font-medium text-white transition hover:bg-[#2D507E]/90"
                                    >
                                        Keluar
                                    </button>
                                </form>
                            </div>
                        </details>
                    @endauth
                </header>

                <div class="flex-1 overflow-y-auto no-scrollbar bg-slate-50 p-6 lg:p-8">
                    @yield('content')
                </div>
            </main>
        </div>

        @stack('scripts')
    </body>
</html>
