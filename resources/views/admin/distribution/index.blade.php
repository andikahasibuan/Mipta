@extends('layouts.admin')

@section('title', 'Laporan')

@section('content')
    @include('partials.distribution-content', [
        'area' => 'admin',
        'readOnly' => false,
    ])
@endsection
