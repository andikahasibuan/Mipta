@php
    // Definisi menu sidebar distributor.
    $menus = [
        ['label' => 'Dashboard Monitoring', 'route' => 'distributor.dashboard', 'icon' => 'squares-2x2'],
        ['label' => 'Manajemen Data Toko', 'route' => 'distributor.stores.index', 'icon' => 'building-storefront'],
        ['label' => 'Laporan Penyaluran', 'route' => 'distributor.distribution-reports.create', 'icon' => 'document-text'],
        ['label' => 'Riwayat Laporan', 'route' => 'distributor.report-history.index', 'icon' => 'clock'],
        ['label' => 'Manajemen Profil', 'route' => 'distributor.profile.edit', 'icon' => 'user-circle'],
    ];
@endphp

@include('layouts.partials.shell')
