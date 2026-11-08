@extends('layouts.admin')

@section('title', 'Laporan Penyaluran')

@section('page-title', 'Laporan Penyaluran')

@section('content')
    <x-admin.page-heading
        title="Laporan Penyaluran"
        subtitle="Cari, filter, dan kelola data sistem secara terpusat"
    />

    @php
        $formatNumber = fn (int $value): string => number_format($value, 0, ',', '.');
    @endphp

    <x-admin.flash-messages />

    <form
        method="GET"
        action="{{ route('admin.distribution.index') }}"
        class="rounded-xl border border-slate-200 bg-white p-3"
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
            <table class="w-full min-w-[64rem] table-fixed divide-y divide-slate-200 text-sm">
                <thead class="text-center text-[11px] uppercase tracking-widest text-slate-500">
                    <tr>
                        <th scope="col" class="w-[8%] px-5 py-3 align-middle font-semibold">ID Data</th>
                        <th scope="col" class="w-[14%] px-5 py-3 align-middle font-semibold">Tanggal</th>
                        <th scope="col" class="w-[18%] px-5 py-3 align-middle font-semibold">Pasar</th>
                        <th scope="col" class="w-[16%] px-5 py-3 align-middle font-semibold">Nama Toko</th>
                        <th scope="col" class="w-[19%] px-5 py-3 align-middle font-semibold">Distributor</th>
                        <th scope="col" class="w-[15%] whitespace-nowrap px-5 py-3 align-middle font-semibold">Jumlah Karton</th>
                        <th scope="col" class="w-[10%] px-5 py-3 align-middle font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-center">
                    @forelse ($distributions as $distribution)
                        <tr>
                            <td class="whitespace-nowrap px-5 py-4 text-center align-middle font-mono text-xs text-slate-500">
                                {{ $distribution->code }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 align-middle text-slate-600">
                                {{ $distribution->distribution_date->format('d/m/Y') }}
                            </td>
                            <td class="px-5 py-4 align-middle text-slate-700">
                                {{ $distribution->market->name }}
                            </td>
                            <td class="px-5 py-4 align-middle text-slate-700">
                                {{ $distribution->store?->name ?? '—' }}
                            </td>
                            <td class="px-5 py-4 align-middle text-slate-900">
                                {{ $distribution->distributor->name }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-center align-middle font-semibold text-slate-900">
                                {{ $formatNumber($distribution->cartons) }} karton
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-center align-middle">
                                <form
                                    method="POST"
                                    action="{{ route('admin.distribution.destroy', $distribution) }}"
                                    onsubmit="return confirm('Yakin ingin menghapus data penyaluran ini?')"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        aria-label="Hapus"
                                        title="Hapus"
                                        class="inline-flex h-9 w-9 cursor-pointer items-center justify-center rounded-lg border border-slate-200 bg-white text-red-500 transition hover:border-red-300 hover:bg-red-50"
                                    >
                                        <x-heroicon-o-trash class="h-4 w-4" />
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-sm text-slate-500">
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
