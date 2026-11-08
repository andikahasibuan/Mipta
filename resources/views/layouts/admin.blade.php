@php
    // Definisi menu sidebar admin.
    $menus = [
        ['label' => 'Dashboard Monitoring', 'route' => 'admin.dashboard', 'icon' => 'squares-2x2'],
        ['label' => 'Pengguna & Data Master', 'route' => 'admin.users.index', 'icon' => 'users', 'active' => ['admin.users.*', 'admin.distributors.*', 'admin.markets.*', 'admin.recipient-stores.*', 'admin.uptd-contributors.*']],
        ['label' => 'Laporan Penyaluran', 'route' => 'admin.distribution.index', 'icon' => 'document-text'],
        ['label' => 'Monitoring & Analisis', 'route' => 'admin.monitoring.index', 'icon' => 'chart-bar'],
        ['label' => 'Pengawasan & Tindak Lanjut', 'route' => 'admin.supervision.index', 'icon' => 'shield-check'],
        ['label' => 'Rekap & Ekspor Laporan', 'route' => 'admin.reports.index', 'icon' => 'arrow-down-tray'],
    ];
@endphp

@include('layouts.partials.shell')
