@extends('layouts.uptd')

@section('title', 'Edit Laporan')

@section('content')
    @include('partials.uptd-report-form', ['report' => $report])
@endsection
