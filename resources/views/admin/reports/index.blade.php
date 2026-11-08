@extends('layouts.admin')

@section('title', 'Rekap & Ekspor Laporan')

@section('content')
    @include('partials.report-recap-content', [
        'area' => 'admin',
        'title' => 'Rekap & Ekspor Laporan',
        'subtitle' => 'Cari, filter, dan kelola data sistem secara terpusat',
        'exports' => [
            ['label' => 'Ekspor Data', 'icon' => 'arrow-down-tray'],
        ],
    ])
@endsection
