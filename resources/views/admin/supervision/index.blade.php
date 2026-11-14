@extends('layouts.admin')

@section('title', 'Kondisi Pasar')

@section('content')
    @include('partials.supervision-content', [
        'area' => 'admin',
    ])
@endsection
