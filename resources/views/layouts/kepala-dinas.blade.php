@php
    // Definisi menu sidebar kepala dinas.
    $menus = [
        ['label' => 'Dashboard Monitoring', 'route' => 'kepala-dinas.dashboard', 'icon' => 'squares-2x2'],
        ['label' => 'Laporan Penyaluran', 'route' => 'kepala-dinas.distribution.index', 'icon' => 'document-text'],
        ['label' => 'Monitoring Kondisi Pasar', 'route' => 'kepala-dinas.supervision.index', 'icon' => 'shield-check'],
        ['label' => 'Rekapitulasi Laporan', 'route' => 'kepala-dinas.reports.index', 'icon' => 'arrow-down-tray'],
    ];
@endphp

@include('layouts.partials.shell')
