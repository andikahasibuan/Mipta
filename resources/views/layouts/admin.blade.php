@php
    // Definisi menu sidebar admin.
    $menus = [
        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'squares-2x2'],
        ['label' => 'Data Pengguna', 'route' => 'admin.users.index', 'icon' => 'users', 'active' => ['admin.users.*', 'admin.distributors.*', 'admin.markets.*', 'admin.recipient-stores.*', 'admin.uptd-contributors.*']],
        ['label' => 'Laporan', 'route' => 'admin.distribution.index', 'icon' => 'document-text'],
        ['label' => 'Monitoring', 'route' => 'admin.monitoring.index', 'icon' => 'chart-bar'],
        ['label' => 'Pengawasan', 'route' => 'admin.supervision.index', 'icon' => 'shield-check'],
        ['label' => 'Ekspor Laporan', 'route' => 'admin.reports.index', 'icon' => 'arrow-down-tray'],
    ];
@endphp

@include('layouts.partials.shell')
