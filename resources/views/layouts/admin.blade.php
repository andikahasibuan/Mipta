@php
    // Definisi menu sidebar admin.
    $menus = [
        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'squares-2x2'],
        ['label' => 'Pengguna', 'route' => 'admin.users.index', 'icon' => 'users', 'active' => ['admin.users.*', 'admin.distributors.*']],
        ['label' => 'Data Master', 'route' => 'admin.markets.index', 'icon' => 'table-cells', 'active' => ['admin.markets.*', 'admin.recipient-stores.*', 'admin.uptd-contributors.*']],
        ['label' => 'Penyaluran', 'route' => 'admin.distribution.index', 'icon' => 'document-text'],
        ['label' => 'Kondisi Pasar', 'route' => 'admin.supervision.index', 'icon' => 'shield-check'],
        ['label' => 'Rekap Laporan', 'route' => 'admin.reports.index', 'icon' => 'arrow-down-tray'],
    ];
@endphp

@include('layouts.partials.shell')
