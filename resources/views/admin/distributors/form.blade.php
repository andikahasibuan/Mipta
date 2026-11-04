@extends('layouts.admin')

@php
    $isEditing = $distributor !== null;
@endphp

@section('title', $isEditing ? 'Ubah Distributor' : 'Tambah Distributor')

@section('page-title', $isEditing ? 'Ubah Distributor' : 'Tambah Distributor')

@section('content')
    <div class="max-w-3xl">
        <a
            href="{{ route('admin.distributors.index') }}"
            class="inline-flex items-center gap-1 text-sm text-slate-500 transition hover:text-slate-700"
        >
            <x-heroicon-o-arrow-left class="h-4 w-4" />
            Kembali ke daftar distributor
        </a>

        <form
            method="POST"
            action="{{ $isEditing ? route('admin.distributors.update', $distributor) : route('admin.distributors.store') }}"
            class="mt-4 rounded-xl border border-slate-200 bg-white p-5 lg:p-6"
        >
            @csrf
            @if ($isEditing)
                @method('PUT')
            @endif

            <h2 class="text-lg font-semibold text-slate-900">Data distributor</h2>
            <p class="mt-1 text-sm text-slate-500">
                {{ $isEditing ? 'Ubah data distributor '.$distributor->name.'.' : 'Isi data distributor yang akan ditambahkan.' }}
            </p>

            <div class="mt-5 grid gap-5 md:grid-cols-2">
                <div>
                    <label for="code" class="block text-sm font-medium text-slate-700">Kode Distributor</label>
                    @if ($isEditing)
                        <input
                            type="text"
                            id="code"
                            value="{{ $distributor->code }}"
                            disabled
                            class="mt-1.5 w-full cursor-not-allowed rounded-lg border border-slate-200 bg-slate-100 px-3 py-2.5 font-mono text-sm text-slate-600"
                        >
                        <p class="mt-1.5 text-xs text-slate-500">Kode tidak dapat diubah setelah distributor dibuat.</p>
                    @else
                        <input
                            type="text"
                            id="code"
                            name="code"
                            value="{{ old('code') }}"
                            required
                            autofocus
                            placeholder="Contoh: DIST-001"
                            class="mt-1.5 w-full rounded-lg border bg-white px-3 py-2.5 font-mono text-sm text-slate-800 placeholder:font-sans focus:outline-none focus:ring-2 focus:ring-[#5191E4]/30 @error('code') border-rose-400 focus:border-rose-500 @else border-slate-300 focus:border-[#5191E4] @enderror"
                        >
                        @error('code')
                            <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    @endif
                </div>

                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700">Nama Distributor</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', $distributor?->name) }}"
                        required
                        class="mt-1.5 w-full rounded-lg border bg-white px-3 py-2.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#5191E4]/30 @error('name') border-rose-400 focus:border-rose-500 @else border-slate-300 focus:border-[#5191E4] @enderror"
                    >
                    @error('name')
                        <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <span class="block text-sm font-medium text-slate-700">Status</span>
                    <div class="mt-2 flex items-center gap-5">
                        <label class="inline-flex cursor-pointer items-center gap-2 text-sm text-slate-700">
                            <input
                                type="radio"
                                name="is_active"
                                value="1"
                                @checked((string) old('is_active', $isEditing ? ($distributor->is_active ? '1' : '0') : '1') === '1')
                                class="h-4 w-4 border-slate-300 text-[#2D507E] focus:ring-[#5191E4]"
                            >
                            Aktif
                        </label>
                        <label class="inline-flex cursor-pointer items-center gap-2 text-sm text-slate-700">
                            <input
                                type="radio"
                                name="is_active"
                                value="0"
                                @checked((string) old('is_active', $isEditing ? ($distributor->is_active ? '1' : '0') : '1') === '0')
                                class="h-4 w-4 border-slate-300 text-[#2D507E] focus:ring-[#5191E4]"
                            >
                            Nonaktif
                        </label>
                    </div>
                    @error('is_active')
                        <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6 flex flex-wrap items-center justify-end gap-3 border-t border-slate-200 pt-5">
                <a
                    href="{{ route('admin.distributors.index') }}"
                    class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                >
                    Batal
                </a>
                <button
                    type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-[#2D507E] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#2D507E]/90"
                >
                    {{ $isEditing ? 'Simpan Perubahan' : 'Simpan' }}
                </button>
            </div>
        </form>
    </div>
@endsection
