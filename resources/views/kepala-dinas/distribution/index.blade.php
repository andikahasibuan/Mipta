@extends('layouts.kepala-dinas')

@section('title', 'Laporan Penyaluran')

@section('content')
    @include('partials.distribution-content', [
        'area' => 'kepala-dinas',
        'readOnly' => true,
        'subtitle' => 'Pantau laporan penyaluran Minyakita',
    ])
@endsection
