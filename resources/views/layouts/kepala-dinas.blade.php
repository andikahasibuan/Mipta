@php
    // Definisi menu sidebar kepala dinas.
    $menus = [
        ['label' => 'Dashboard', 'route' => 'kepala-dinas.dashboard', 'icon' => 'squares-2x2'],
        ['label' => 'Penyaluran', 'route' => 'kepala-dinas.distribution.index', 'icon' => 'document-text'],
        ['label' => 'Pengawasan', 'route' => 'kepala-dinas.supervision.index', 'icon' => 'shield-check'],
    ];
@endphp

@include('layouts.partials.shell')
