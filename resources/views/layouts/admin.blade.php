@php
    // Menu masih statis sampai halaman tiap menu dibangun.
    $menus = [
        ['label' => 'Dashboard Monitoring', 'active' => true],
        ['label' => 'Pengguna & Data Master', 'active' => false],
        ['label' => 'Laporan Penyaluran', 'active' => false],
        ['label' => 'Monitoring & Analisis', 'active' => false],
        ['label' => 'Pengawasan & Tindak Lanjut', 'active' => false],
        ['label' => 'Rekap & Ekspor Laporan', 'active' => false],
        ['label' => 'Riwayat Aktivitas Sistem', 'active' => false],
        ['label' => 'Manajemen Profil', 'active' => false],
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
            <aside class="flex w-64 shrink-0 flex-col border-r border-blue-200 bg-blue-100">
                <div class="px-6 py-6">
                    <p class="text-2xl font-bold tracking-[0.2em] text-blue-950">MIPTA</p>
                </div>

                <nav class="flex-1 px-3 pb-6" aria-label="Menu utama">
                    <ul class="flex flex-col gap-1">
                        @foreach ($menus as $menu)
                            <li>
                                <span @class([
                                    'block rounded-md px-3 py-2 text-sm',
                                    'bg-blue-200 font-semibold text-blue-950' => $menu['active'],
                                    'text-blue-900' => ! $menu['active'],
                                ])>
                                    {{ $menu['label'] }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </nav>

                <div class="border-t border-blue-200 px-3 py-4">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button
                            type="submit"
                            class="w-full rounded-md px-3 py-2 text-left text-sm font-medium text-blue-900 transition hover:bg-blue-200"
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
