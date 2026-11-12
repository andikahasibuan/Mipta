@extends('layouts.admin')

@section('title', 'Pengawasan & Tindak Lanjut')

@section('content')
    @include('partials.supervision-content', [
        'area' => 'admin',
    ])
@endsection
