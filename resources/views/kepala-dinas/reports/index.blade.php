@extends('layouts.kepala-dinas')

@section('title', 'Rekap Laporan')

@section('content')
    @include('partials.report-recap-content', [
        'area' => 'kepala-dinas',
        'exports' => [
            ['label' => 'Ekspor Excel', 'icon' => 'document-arrow-down'],
            ['label' => 'Ekspor PDF', 'icon' => 'document-text'],
        ],
    ])
@endsection
