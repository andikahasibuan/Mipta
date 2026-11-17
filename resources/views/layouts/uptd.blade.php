@php
    // Definisi menu sidebar kontributor UPTD Pasar.
    $menus = [
        ['label' => 'Dashboard', 'route' => 'uptd.dashboard', 'icon' => 'squares-2x2'],
        ['label' => 'Laporan', 'route' => 'uptd.reports.create', 'icon' => 'document-text'],
        ['label' => 'Riwayat', 'route' => 'uptd.reports.index', 'icon' => 'clock', 'active' => ['uptd.reports.index', 'uptd.reports.edit']],
        ['label' => 'Ganti Kontributor', 'route' => 'uptd.contributors.index', 'icon' => 'arrows-right-left'],
    ];
@endphp

@include('layouts.partials.shell')
