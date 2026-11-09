@extends('layouts.uptd')

@section('title', 'Input Laporan')

@section('content')
    <x-ui.page-heading
        title="Input Laporan Kondisi Lapangan"
        subtitle="Catat kondisi distribusi Minyakita pada pasar yang menjadi tanggung jawab Anda"
    />

    @include('partials.uptd-report-form', ['report' => null])
@endsection
