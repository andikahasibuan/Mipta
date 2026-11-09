@php
    // Definisi menu sidebar kontributor UPTD Pasar.
    $menus = [
        ['label' => 'Dashboard UPTD Pasar', 'route' => 'uptd.dashboard', 'icon' => 'squares-2x2'],
        ['label' => 'Input Laporan', 'route' => 'uptd.reports.create', 'icon' => 'document-plus'],
        ['label' => 'Riwayat Laporan', 'route' => 'uptd.reports.index', 'icon' => 'clock'],
        ['label' => 'Ganti Kontributor', 'route' => 'uptd.contributors.index', 'icon' => 'arrows-right-left'],
    ];
@endphp

@include('layouts.partials.shell')
