@php
    // Definisi menu sidebar kepala dinas.
    $menus = [
        ['label' => 'Dashboard', 'route' => 'kepala-dinas.dashboard', 'icon' => 'squares-2x2'],
        ['label' => 'Laporan Penyaluran', 'route' => 'kepala-dinas.distribution.index', 'icon' => 'document-text'],
        ['label' => 'Kondisi Pasar', 'route' => 'kepala-dinas.supervision.index', 'icon' => 'shield-check'],
        ['label' => 'Rekap Laporan', 'route' => 'kepala-dinas.reports.index', 'icon' => 'arrow-down-tray'],
    ];
@endphp

@include('layouts.partials.shell')
