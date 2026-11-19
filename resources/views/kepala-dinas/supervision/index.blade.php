@extends('layouts.kepala-dinas')

@section('title', 'Pengawasan')

@section('content')
    @include('partials.supervision-content', [
        'area' => 'kepala-dinas',
    ])
@endsection
