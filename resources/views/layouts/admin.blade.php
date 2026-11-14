@php
    // Definisi menu sidebar admin.
    $menus = [
        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'squares-2x2'],
        ['label' => 'Data Pengguna', 'route' => 'admin.users.index', 'icon' => 'users', 'active' => ['admin.users.*', 'admin.distributors.*', 'admin.markets.*', 'admin.recipient-stores.*', 'admin.uptd-contributors.*']],
        ['label' => 'Penyaluran', 'route' => 'admin.distribution.index', 'icon' => 'document-text'],
        ['label' => 'Kondisi Pasar', 'route' => 'admin.supervision.index', 'icon' => 'shield-check'],
        ['label' => 'Monitoring', 'route' => 'admin.monitoring.index', 'icon' => 'chart-bar'],
        ['label' => 'Rekap Laporan', 'route' => 'admin.reports.index', 'icon' => 'arrow-down-tray'],
    ];
@endphp

@include('layouts.partials.shell')
