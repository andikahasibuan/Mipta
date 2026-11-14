@extends('layouts.kepala-dinas')

@section('title', 'Dashboard')

@section('content')
    @include('partials.dashboard-content', ['area' => 'kepala-dinas'])
@endsection
