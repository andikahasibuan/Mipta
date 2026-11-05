@extends('layouts.app')

@section('sidebar')
    @php
        $menus = [
            ['label' => 'Dashboard Monitoring', 'route' => 'kepala-dinas.dashboard', 'icon' => 'squares-2x2'],
            ['label' => 'Monitoring Laporan Penyaluran', 'route' => 'kepala-dinas.distribution', 'icon' => 'document-text'],
            ['label' => 'Monitoring Kondisi Pasar', 'route' => 'kepala-dinas.markets', 'icon' => 'building-storefront'],
            ['label' => 'Rekapitulasi Laporan', 'route' => 'kepala-dinas.reports', 'icon' => 'arrow-down-tray'],
        ];
    @endphp

    <x-layouts.sidebar :menus="$menus" />
@endsection
