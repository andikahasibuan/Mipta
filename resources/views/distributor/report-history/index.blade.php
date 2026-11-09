@extends('layouts.distributor')

@section('title', 'Riwayat Laporan')

@section('content')
    <x-ui.page-heading
        title="Riwayat Laporan Penyaluran"
        subtitle="Daftar laporan penyaluran yang pernah dibuat oleh distributor"
    />

    <x-ui.flash-messages />

    @php
        $formatNumber = fn (int $value): string => number_format($value, 0, ',', '.');
    @endphp

    <form
        method="GET"
        action="{{ route('distributor.report-history.index') }}"
        class="rounded-xl border border-slate-200 bg-white p-3"
    >
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div>
                <label for="filter-month" class="sr-only">Bulan</label>
                <select
                    id="filter-month"
                    name="month"
                    class="w-full cursor-pointer rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 focus:border-[#5191E4] focus:outline-none focus:ring-2 focus:ring-[#5191E4]/30"
                >
                    @foreach ($months as $option)
                        <option value="{{ $option['value'] }}" @selected($filters['month'] === $option['value'])>
                            {{ $option['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="filter-status" class="sr-only">Status laporan</label>
                <select
                    id="filter-status"
                    name="status"
                    class="w-full cursor-pointer rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 focus:border-[#5191E4] focus:outline-none focus:ring-2 focus:ring-[#5191E4]/30"
                >
                    <option value="">Semua Status</option>
                    <option value="submitted" @selected($filters['status'] === 'submitted')>Terkirim</option>
                    <option value="draft" @selected($filters['status'] === 'draft')>Draf</option>
                </select>
            </div>

            <div>
                <label for="filter-search" class="sr-only">Cari nama toko</label>
                <input
                    type="search"
                    id="filter-search"
                    name="search"
                    value="{{ $filters['search'] }}"
                    placeholder="Cari nama toko"
                    class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 placeholder:text-slate-400 focus:border-[#5191E4] focus:outline-none focus:ring-2 focus:ring-[#5191E4]/30"
                >
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
                        <th scope="col" class="w-[9%] px-5 py-3 align-middle font-semibold">ID Laporan</th>
                        <th scope="col" class="w-[12%] px-5 py-3 align-middle font-semibold">Tanggal</th>
                        <th scope="col" class="w-[20%] px-5 py-3 align-middle font-semibold">Nama Toko</th>
                        <th scope="col" class="w-[18%] px-5 py-3 align-middle font-semibold">Pasar</th>
                        <th scope="col" class="w-[13%] px-5 py-3 align-middle font-semibold">Jumlah Karton</th>
                        <th scope="col" class="w-[15%] px-5 py-3 align-middle font-semibold">Status Laporan</th>
                        <th scope="col" class="w-[9%] px-5 py-3 align-middle font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-center">
                    @forelse ($reports as $report)
                        <tr>
                            <td class="whitespace-nowrap px-5 py-4 align-middle font-mono text-xs text-slate-500">
                                {{ $report->code }}
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
                            <td class="whitespace-nowrap px-5 py-4 align-middle">
                                @if ($report->status === \App\Enums\DistributionStatus::Submitted)
                                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                        Terkirim
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">
                                        Draf
                                    </span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 align-middle">
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
