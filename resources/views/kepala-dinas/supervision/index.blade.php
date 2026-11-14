@extends('layouts.kepala-dinas')

@section('title', 'Kondisi Pasar')

@section('content')
    @include('partials.supervision-content', [
        'area' => 'kepala-dinas',
    ])
@endsection
