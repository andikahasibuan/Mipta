@extends('layouts.distributor')

@section('title', 'Riwayat')

@section('content')
    <x-ui.flash-messages />

    @php
        $formatNumber = fn (int $value): string => number_format($value, 0, ',', '.');
    @endphp

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
        <div class="px-5 pt-5">
            <h2 class="text-lg font-semibold text-slate-900">Riwayat Laporan</h2>
        </div>

        <form
            method="GET"
            action="{{ route('distributor.report-history.index') }}"
            class="mt-4 flex flex-col gap-3 px-5 lg:flex-row lg:items-center"
        >
            <div class="relative min-w-0 flex-1">
                <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" />
                <input
                    type="search"
                    name="search"
                    value="{{ $filters['search'] }}"
                    placeholder="Cari nama toko..."
                    class="w-full rounded-lg border border-slate-300 bg-white py-2.5 pl-10 pr-3 text-sm text-slate-800 placeholder:text-slate-400 focus:border-[#018F98] focus:outline-none focus:ring-2 focus:ring-[#018F98]/30"
                >
            </div>

            <div class="relative shrink-0">
                <label for="filter-month" class="sr-only">Bulan</label>
                <select
                    id="filter-month"
                    name="month"
                    class="w-full cursor-pointer appearance-none rounded-lg border border-slate-300 bg-white py-2.5 pl-3.5 pr-11 text-sm text-slate-800 focus:border-[#018F98] focus:outline-none focus:ring-2 focus:ring-[#018F98]/30"
                >
                    @foreach ($months as $option)
                        <option value="{{ $option['value'] }}" @selected($filters['month'] === $option['value'])>
                            {{ $option['label'] }}
                        </option>
                    @endforeach
                </select>
                <x-heroicon-o-chevron-down class="pointer-events-none absolute right-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            </div>

            <button
                type="submit"
                class="shrink-0 cursor-pointer rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-[#018F98] hover:bg-[#018F98] hover:text-white active:border-[#018F98] active:bg-[#018F98] active:text-white focus:outline-none focus:ring-2 focus:ring-[#018F98]/30"
            >
                Terapkan Filter
            </button>
        </form>

        <div class="mt-4 overflow-x-auto">
            <table class="w-full min-w-[64rem] table-fixed divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-200 text-center text-[11px] uppercase tracking-widest text-slate-700">
                    <tr>
                        <th scope="col" class="w-[6%] px-3 py-3 align-middle font-semibold">NO</th>
                        <th scope="col" class="w-[12%] px-5 py-3 align-middle font-semibold">Tanggal</th>
                        <th scope="col" class="w-[20%] px-5 py-3 align-middle font-semibold">Nama Toko</th>
                        <th scope="col" class="w-[15%] px-5 py-3 align-middle font-semibold">Pasar</th>
                        <th scope="col" class="w-[13%] px-5 py-3 align-middle font-semibold">Jumlah Karton</th>
                        <th scope="col" class="w-[22%] px-5 py-3 align-middle font-semibold">Keterangan</th>
                        <th scope="col" class="w-[12%] px-5 py-3 align-middle font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-center">
                    @forelse ($reports as $report)
                        <tr>
                            <td class="whitespace-nowrap px-3 py-4 align-middle font-mono text-xs text-slate-500">
                                {{ $loop->iteration }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 align-middle text-slate-600">
                                {{ $report->distribution_date->format('d/m/Y') }}
                            </td>
                            <td class="px-5 py-4 align-middle text-slate-700">
                                {{ $report->store?->name ?? '—' }}
                            </td>
                            <td class="px-5 py-4 align-middle text-slate-700">
                                {{ $report->market->name }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 align-middle font-semibold text-slate-900">
                                {{ $formatNumber($report->cartons) }} karton
                            </td>
                            <td @class([
                                'break-words px-5 py-4 align-middle text-slate-700',
                                'text-left' => filled($report->notes),
                                'text-center' => blank($report->notes),
                            ])>
                                {{ $report->notes ?: '-' }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 align-middle">
                                <div class="flex items-center justify-center gap-2">
                                    <a
                                        href="{{ route('distributor.distribution-reports.edit', $report) }}"
                                        aria-label="Edit"
                                        title="Edit"
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-[#018F98] transition hover:border-[#018F98] hover:bg-[#E0F2F3]"
                                    >
                                        <x-heroicon-o-pencil-square class="h-4 w-4" />
                                    </a>

                                    <form
                                        method="POST"
                                        action="{{ route('distributor.report-history.destroy', $report) }}"
                                        onsubmit="return confirm('Yakin ingin menghapus riwayat laporan ini? Admin dan Kepala Dinas akan menerima notifikasi penghapusan.')"
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
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-sm text-slate-500">
                                Belum ada riwayat laporan penyaluran pada periode ini
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($reports->isNotEmpty())
            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 px-5 py-3">
                <p class="text-xs text-slate-500">
                    Menampilkan {{ $formatNumber($reports->count()) }} dari {{ $formatNumber($reports->total()) }} laporan
                </p>

                @if ($reports->hasPages())
                    {{ $reports->links() }}
                @endif
            </div>
        @endif
    </div>
@endsection
