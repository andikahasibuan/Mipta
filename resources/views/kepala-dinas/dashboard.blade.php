@extends('layouts.kepala-dinas')

@section('title', 'Dashboard Monitoring')

@section('content')
    @include('partials.dashboard-content', ['area' => 'kepala-dinas'])
@endsection
