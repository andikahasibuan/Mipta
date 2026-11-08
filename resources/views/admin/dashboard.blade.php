@extends('layouts.admin')

@section('title', 'Dashboard Admin')

@section('content')
    @include('partials.dashboard-content', ['area' => 'admin'])
@endsection
