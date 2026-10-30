@extends('layouts.admin')

@section('title', 'Pengguna & Data Master')

@section('page-title', 'Pengguna & Data Master')

@section('content')
    <x-admin.master-tabs active="summary" />

    @php
        $formatNumber = fn (int $value): string => number_format($value, 0, ',', '.');
    @endphp

    <div class="flex justify-end">
        <button
            type="button"
            disabled
            aria-disabled="true"
            title="Akan tersedia setelah format ekspor disepakati"
            class="inline-flex shrink-0 cursor-not-allowed items-center gap-2 rounded-lg bg-[#2D507E] px-4 py-2.5 text-sm font-semibold text-white opacity-60"
        >
            <x-heroicon-o-arrow-down-tray class="h-5 w-5" />
            Ekspor Data
        </button>
    </div>

    <div class="mt-4 grid gap-4 md:grid-cols-3">
        <div class="flex items-center gap-4 rounded-xl border border-slate-200 bg-white p-5">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#E8F0FB] text-[#2D507E]">
                <x-heroicon-o-users class="h-6 w-6" />
            </div>
            <div class="min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-500">Total Pengguna</p>
                <p class="mt-1 text-3xl font-bold text-slate-900">{{ $formatNumber($totalUsers) }}</p>
                <p class="mt-1 text-xs text-slate-500">Akun terdaftar</p>
            </div>
        </div>

        <div class="flex items-center gap-4 rounded-xl border border-slate-200 bg-white p-5">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#E8F0FB] text-[#2D507E]">
                <x-heroicon-o-building-office-2 class="h-6 w-6" />
            </div>
            <div class="min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-500">Distributor</p>
                <p class="mt-1 text-3xl font-bold text-slate-900">{{ $formatNumber($totalDistributors) }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ $formatNumber($activeDistributors) }} aktif</p>
            </div>
        </div>

        <div class="flex items-center gap-4 rounded-xl border border-slate-200 bg-white p-5">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#E8F0FB] text-[#2D507E]">
                <x-heroicon-o-home-modern class="h-6 w-6" />
            </div>
            <div class="min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-500">Toko Penerima</p>
                <p class="mt-1 text-3xl font-bold text-slate-900">{{ $formatNumber($totalStores) }}</p>
                <p class="mt-1 text-xs text-slate-500">Tersebar di {{ $formatNumber($marketsWithStores) }} pasar</p>
            </div>
        </div>
    </div>
@endsection
