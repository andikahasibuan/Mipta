@extends('layouts.kepala-dinas')

@section('title', 'Penyaluran')

@section('content')
    @include('partials.distribution-content', [
        'area' => 'kepala-dinas',
        'readOnly' => true,
    ])
@endsection
