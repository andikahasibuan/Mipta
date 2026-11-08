@extends('layouts.admin')

@section('title', 'Pengawasan & Tindak Lanjut')

@section('content')
    @include('partials.supervision-content', [
        'area' => 'admin',
        'title' => 'Pengawasan & Tindak Lanjut',
        'subtitle' => 'Kelola pemeriksaan, dan tindak lanjut secara terstruktur',
    ])
@endsection
