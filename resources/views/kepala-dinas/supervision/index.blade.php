@extends('layouts.kepala-dinas')

@section('title', 'Monitoring Kondisi Pasar')

@section('content')
    @include('partials.supervision-content', [
        'area' => 'kepala-dinas',
        'title' => 'Monitoring Kondisi Pasar',
        'subtitle' => 'Laporan Kondisi Lapangan',
    ])
@endsection
