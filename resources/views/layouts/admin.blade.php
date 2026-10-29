@php
    // Definisi menu sidebar admin.
    $menus = [
        ['label' => 'Dashboard Monitoring', 'route' => 'admin.dashboard'],
        ['label' => 'Pengguna & Data Master', 'route' => 'admin.users.index'],
        ['label' => 'Laporan Penyaluran', 'route' => 'admin.distribution.index'],
        ['label' => 'Monitoring & Analisis', 'route' => 'admin.monitoring.index'],
        ['label' => 'Pengawasan & Tindak Lanjut', 'route' => 'admin.supervision.index'],
        ['label' => 'Rekap & Ekspor Laporan', 'route' => 'admin.reports.index'],
        ['label' => 'Riwayat Aktivitas Sistem', 'route' => 'admin.activity.index'],
        ['label' => 'Manajemen Profil', 'route' => 'admin.profile.edit'],
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
    <body class="min-h-screen bg-slate-50 text-slate-800 antialiased">
        <div class="flex min-h-screen">
            <aside class="flex w-64 shrink-0 flex-col border-r border-blue-200 bg-[linear-gradient(to_bottom,#5191E4_3%,#2D507E_13%)]">
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
                                        'block rounded-md px-3 py-2 text-sm transition',
                                        'bg-[#FDBA12]/40 font-semibold text-white' => request()->routeIs($menu['route']),
                                        'text-[#A9C0D6] hover:bg-[#FDBA12]/40 hover:font-semibold hover:text-white' => ! request()->routeIs($menu['route']),
                                    ])
                                >
                                    {{ $menu['label'] }}
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

            <main class="min-w-0 flex-1">
                @yield('content')
            </main>
        </div>
    </body>
</html>
