@php
    // Definisi menu sidebar distributor.
    $menus = [
        ['label' => 'Dashboard', 'route' => 'distributor.dashboard', 'icon' => 'squares-2x2'],
        ['label' => 'Data Toko', 'route' => 'distributor.stores.index', 'icon' => 'building-storefront', 'active' => ['distributor.stores.index', 'distributor.stores.create', 'distributor.stores.edit']],
        ['label' => 'Laporan', 'route' => 'distributor.distribution-reports.create', 'icon' => 'document-text', 'active' => ['distributor.distribution-reports.create', 'distributor.distribution-reports.edit']],
        ['label' => 'Riwayat', 'route' => 'distributor.report-history.index', 'icon' => 'clock'],
        ['label' => 'Profil', 'route' => 'distributor.profile.edit', 'icon' => 'user-circle'],
    ];
@endphp

@include('layouts.partials.shell')
