@extends('layouts.admin')

@section('title', 'Ekspor Laporan')

@section('content')
    @include('partials.report-recap-content', [
        'area' => 'admin',
        'exports' => [
            ['label' => 'Ekspor Excel', 'icon' => 'document-arrow-down'],
            ['label' => 'Ekspor PDF', 'icon' => 'document-text'],
        ],
    ])
@endsection
