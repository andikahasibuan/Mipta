@extends('layouts.uptd')

@section('title', 'Edit Laporan')

@section('content')
    <x-ui.page-heading
        title="Edit Laporan Kondisi Lapangan"
        subtitle="Perbarui laporan kondisi pasar yang pernah Anda buat"
    />

    @include('partials.uptd-report-form', ['report' => $report])
@endsection
