@extends('layouts.admin')

@section('title', 'Pengawasan')

@section('content')
    @include('partials.supervision-content', [
        'area' => 'admin',
    ])
@endsection
