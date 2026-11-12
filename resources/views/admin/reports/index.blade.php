@extends('layouts.admin')

@section('title', 'Rekap & Ekspor Laporan')

@section('content')
    @include('partials.report-recap-content', [
        'area' => 'admin',
        'exports' => [
            ['label' => 'Ekspor Data', 'icon' => 'arrow-down-tray'],
        ],
    ])
@endsection
