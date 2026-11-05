@extends('layouts.admin')

@section('title', 'Laporan Penyaluran')

@section('page-title', 'Laporan Penyaluran')

@section('content')
    @php
        $formatNumber = fn (int $value): string => number_format($value, 0, ',', '.');
    @endphp

    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <h2 class="text-2xl font-semibold text-slate-900">Laporan Penyaluran</h2>
            <p class="mt-1 text-sm text-slate-500">
                Pantau penyaluran Minyakita per distributor dan pasar
            </p>
        </div>

        <button
            type="button"
            disabled
            aria-disabled="true"
            title="Menunggu modul input UPTD Pasar"
            class="inline-flex shrink-0 cursor-not-allowed items-center gap-2 rounded-lg bg-[#2D507E] px-4 py-2.5 text-sm font-semibold text-white opacity-60"
        >
            <x-heroicon-o-plus class="h-4 w-4" />
            Tambah Data
        </button>
    </div>

    <form
        method="GET"
        action="{{ route('admin.distribution.index') }}"
        class="mt-6 rounded-xl border border-slate-200 bg-white p-3"
    >
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div>
                <label for="filter-month" class="sr-only">Bulan</label>
                <input
                    type="month"
                    id="filter-month"
                    name="month"
                    value="{{ $filters['month'] }}"
                    class="w-full cursor-pointer rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 focus:border-[#5191E4] focus:outline-none focus:ring-2 focus:ring-[#5191E4]/30"
                >
            </div>

            <div>
                <label for="filter-distributor" class="sr-only">Distributor</label>
                <select
                    id="filter-distributor"
                    name="distributor"
                    class="w-full cursor-pointer rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 focus:border-[#5191E4] focus:outline-none focus:ring-2 focus:ring-[#5191E4]/30"
                >
                    <option value="">Semua Distributor</option>
                    @foreach ($distributors as $distributor)
                        <option value="{{ $distributor->id }}" @selected($filters['distributor'] === $distributor->id)>
                            {{ $distributor->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="filter-market" class="sr-only">Pasar</label>
                <select
                    id="filter-market"
                    name="market"
                    class="w-full cursor-pointer rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 focus:border-[#5191E4] focus:outline-none focus:ring-2 focus:ring-[#5191E4]/30"
                >
                    <option value="">Semua Pasar</option>
                    @foreach ($markets as $market)
                        <option value="{{ $market->id }}" @selected($filters['market'] === $market->id)>
                            {{ $market->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button
                type="submit"
                class="w-full cursor-pointer rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-[#5191E4]/30"
            >
                Terapkan Filter
            </button>
        </div>
    </form>

    <div class="mt-4 overflow-hidden rounded-xl border border-slate-200 bg-white">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[54rem] table-fixed divide-y divide-slate-200 text-sm">
                <thead class="text-left text-[11px] uppercase tracking-widest text-slate-500">
                    <tr>
                        <th scope="col" class="w-[14%] px-5 py-3 align-middle font-semibold">Tanggal</th>
                        <th scope="col" class="w-[25%] px-5 py-3 align-middle font-semibold">Distributor</th>
                        <th scope="col" class="w-[22%] px-5 py-3 align-middle font-semibold">Pasar</th>
                        <th scope="col" class="w-[21%] px-5 py-3 align-middle font-semibold">Petugas UPTD</th>
                        <th scope="col" class="w-[18%] px-5 py-3 text-right align-middle font-semibold">Penyaluran</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($distributions as $distribution)
                        <tr>
                            <td class="whitespace-nowrap px-5 py-4 align-middle text-slate-600">
                                {{ $distribution->distribution_date->format('d/m/Y') }}
                            </td>
                            <td class="px-5 py-4 align-middle text-slate-900">
                                {{ $distribution->distributor->name }}
                            </td>
                            <td class="px-5 py-4 align-middle text-slate-700">
                                {{ $distribution->market->name }}
                            </td>
                            <td class="px-5 py-4 align-middle text-slate-700">
                                {{ $distribution->uptdUser?->name ?? '—' }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-right align-middle font-semibold text-slate-900">
                                {{ $formatNumber($distribution->cartons) }} karton
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-sm text-slate-500">
                                Belum ada laporan penyaluran pada periode ini
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($distributions->isNotEmpty())
            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 px-5 py-3">
                <p class="text-xs text-slate-500">
                    Menampilkan {{ $formatNumber($distributions->count()) }} dari {{ $formatNumber($distributions->total()) }} laporan
                </p>

                @if ($distributions->hasPages())
                    {{ $distributions->links() }}
                @endif
            </div>
        @endif
    </div>
@endsection
