@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    @include('partials.dashboard-content', ['area' => 'admin'])
@endsection
