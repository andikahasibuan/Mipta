@php
    $menus = [
        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'squares-2x2'],
        ['label' => 'Pengguna', 'route' => 'admin.users.index', 'icon' => 'users', 'active' => ['admin.users.*', 'admin.distributors.*']],
        ['label' => 'Data Master', 'route' => 'admin.markets.index', 'icon' => 'table-cells', 'active' => ['admin.markets.*', 'admin.recipient-stores.*']],
        ['label' => 'Penyaluran', 'route' => 'admin.distribution.index', 'icon' => 'document-text'],
        ['label' => 'Pengawasan', 'route' => 'admin.supervision.index', 'icon' => 'shield-check'],
    ];
@endphp

@include('layouts.partials.shell')
