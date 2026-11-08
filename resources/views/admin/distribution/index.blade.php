@extends('layouts.admin')

@section('title', 'Laporan Penyaluran')

@section('content')
    @include('partials.distribution-content', [
        'area' => 'admin',
        'readOnly' => false,
        'subtitle' => 'Cari, filter, dan kelola data sistem secara terpusat',
    ])
@endsection
