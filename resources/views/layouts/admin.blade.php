@extends('layouts.app')

@section('sidebar')
    @php
        $menus = [
            ['label' => 'Dashboard Monitoring', 'route' => 'admin.dashboard', 'icon' => 'squares-2x2'],
            ['label' => 'Pengguna & Data Master', 'route' => 'admin.users.index', 'icon' => 'users', 'active' => ['admin.users.*', 'admin.distributors.*']],
            ['label' => 'Laporan Penyaluran', 'route' => 'admin.distribution.index', 'icon' => 'document-text'],
            ['label' => 'Monitoring & Analisis', 'route' => 'admin.monitoring.index', 'icon' => 'chart-bar'],
            ['label' => 'Pengawasan & Tindak Lanjut', 'route' => 'admin.supervision.index', 'icon' => 'shield-check'],
            ['label' => 'Rekap & Ekspor Laporan', 'route' => 'admin.reports.index', 'icon' => 'arrow-down-tray'],
            ['label' => 'Riwayat Aktivitas Sistem', 'route' => 'admin.activity.index', 'icon' => 'clock'],
            ['label' => 'Manajemen Profil', 'route' => 'admin.profile.edit', 'icon' => 'user-circle'],
        ];
    @endphp

    <x-layouts.sidebar :menus="$menus" />
@endsection
