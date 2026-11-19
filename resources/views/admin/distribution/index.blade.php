@extends('layouts.admin')

@section('title', 'Penyaluran')

@section('content')
    @include('partials.distribution-content', [
        'area' => 'admin',
    ])
@endsection
