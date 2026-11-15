@php
    $formatNumber = fn (int $value): string => number_format($value, 0, ',', '.');
@endphp

<x-ui.flash-messages />

<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
    <div class="rounded-xl border border-slate-200 bg-white p-5">
        <div class="min-w-0">
            <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-500">Jumlah Laporan</p>
            <p class="mt-1 text-3xl font-bold text-[#018F98]">{{ $formatNumber($summary['reports']) }}</p>
            <p class="mt-1 text-xs text-slate-500">{{ $month_label }}</p>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-5">
        <div class="min-w-0">
            <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-500">Total Penyaluran</p>
            <p class="mt-1 text-3xl font-bold text-[#018F98]">{{ $formatNumber($summary['cartons']) }}</p>
            <p class="mt-1 text-xs text-slate-500">Karton Pada {{ $month_label }}</p>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-5">
        <div class="min-w-0">
            <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-500">Jumlah Toko Penerima</p>
            <p class="mt-1 text-3xl font-bold text-[#018F98]">{{ $formatNumber($summary['recipient_stores']) }}</p>
        </div>
    </div>
</div>

<div class="mt-4 overflow-hidden rounded-xl border border-slate-200 bg-white">
    <div class="px-5 pt-5">
        <h2 class="text-lg font-semibold text-slate-900">Laporan Penyaluran</h2>
    </div>

    <form
        method="GET"
        action="{{ route($area.'.distribution.index') }}"
        class="mt-4 flex flex-col gap-3 px-5 lg:flex-row lg:flex-wrap lg:items-center"
    >
        <div class="min-w-0 flex-1">
            <label for="filter-search" class="sr-only">Cari laporan penyaluran</label>
            <input
                type="search"
                id="filter-search"
                name="search"
                value="{{ $filters['search'] }}"
                placeholder="Cari toko, distributor, atau pasar..."
                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 placeholder:text-slate-400 focus:border-[#018F98] focus:outline-none focus:ring-2 focus:ring-[#018F98]/30"
            >
        </div>

        <div class="relative min-w-0 lg:w-48">
            <label for="filter-month" class="sr-only">Bulan</label>
            <select
                id="filter-month"
                name="month"
                class="w-full cursor-pointer appearance-none rounded-lg border border-slate-300 bg-white py-2.5 pl-3.5 pr-11 text-sm text-slate-700 focus:border-[#018F98] focus:outline-none focus:ring-2 focus:ring-[#018F98]/30"
            >
                @foreach ($months as $option)
                    <option value="{{ $option['value'] }}" @selected($filters['month'] === $option['value'])>
                        {{ $option['label'] }}
                    </option>
                @endforeach
            </select>
            <x-heroicon-o-chevron-down class="pointer-events-none absolute right-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
        </div>

        <div class="relative min-w-0 lg:w-48">
            <label for="filter-distributor" class="sr-only">Distributor</label>
            <select
                id="filter-distributor"
                name="distributor"
                class="w-full cursor-pointer appearance-none rounded-lg border border-slate-300 bg-white py-2.5 pl-3.5 pr-11 text-sm text-slate-700 focus:border-[#018F98] focus:outline-none focus:ring-2 focus:ring-[#018F98]/30"
            >
                <option value="">Semua Distributor</option>
                @foreach ($distributors as $distributor)
                    <option value="{{ $distributor->id }}" @selected($filters['distributor'] === $distributor->id)>
                        {{ $distributor->name }}
                    </option>
                @endforeach
            </select>
            <x-heroicon-o-chevron-down class="pointer-events-none absolute right-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
        </div>

        <div class="relative min-w-0 lg:w-48">
            <label for="filter-market" class="sr-only">Pasar</label>
            <select
                id="filter-market"
                name="market"
                class="w-full cursor-pointer appearance-none rounded-lg border border-slate-300 bg-white py-2.5 pl-3.5 pr-11 text-sm text-slate-700 focus:border-[#018F98] focus:outline-none focus:ring-2 focus:ring-[#018F98]/30"
            >
                <option value="">Semua Pasar</option>
                @foreach ($markets as $market)
                    <option value="{{ $market->id }}" @selected($filters['market'] === $market->id)>
                        {{ $market->name }}
                    </option>
                @endforeach
            </select>
            <x-heroicon-o-chevron-down class="pointer-events-none absolute right-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
        </div>

        <button
            type="submit"
            class="w-full shrink-0 cursor-pointer rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-[#018F98]/30 lg:w-auto"
        >
            Terapkan Filter
        </button>
    </form>

    <div class="mt-4 overflow-x-auto">
        <table class="w-full min-w-[64rem] table-fixed divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-200 text-center text-[11px] uppercase tracking-widest text-slate-700">
                <tr>
                    <th scope="col" class="w-[8%] px-5 py-3 align-middle font-semibold">NO</th>
                    <th scope="col" class="w-[14%] px-5 py-3 align-middle font-semibold">Tanggal</th>
                    <th scope="col" class="w-[18%] px-5 py-3 align-middle font-semibold">Pasar</th>
                    <th scope="col" class="w-[16%] px-5 py-3 align-middle font-semibold">Nama Toko</th>
                    <th scope="col" class="w-[19%] px-5 py-3 align-middle font-semibold">Distributor</th>
                    <th scope="col" class="w-[15%] whitespace-nowrap px-5 py-3 align-middle font-semibold">Jumlah Karton</th>
                    @unless ($readOnly)
                        <th scope="col" class="w-[10%] px-5 py-3 align-middle font-semibold">Aksi</th>
                    @endunless
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-center">
                @forelse ($distributions as $distribution)
                    <tr>
                        <td class="whitespace-nowrap px-5 py-4 text-center align-middle font-mono text-xs text-slate-500">
                            {{ $loop->iteration }}
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
                        @unless ($readOnly)
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
                        @endunless
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $readOnly ? 6 : 7 }}" class="px-5 py-8 text-center text-sm text-slate-500">
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
