@extends('layouts.uptd')

@section('title', 'Input Laporan')

@section('content')
    @include('partials.uptd-report-form', ['report' => null])
@endsection
