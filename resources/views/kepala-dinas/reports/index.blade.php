@extends('layouts.kepala-dinas')

@section('title', 'Rekapitulasi Laporan')

@section('content')
    @include('partials.report-recap-content', [
        'area' => 'kepala-dinas',
        'title' => 'Rekapitulasi Laporan',
        'subtitle' => 'Cari, filter, dan kelola data sistem secara terpusat',
        'exports' => [
            ['label' => 'Ekspor Excel', 'icon' => 'document-arrow-down'],
            ['label' => 'Ekspor PDF', 'icon' => 'document-text'],
        ],
    ])
@endsection
