@php
    $formatNumber = fn (int $value): string => number_format($value, 0, ',', '.');
@endphp

<div class="min-w-0">
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="flex h-28 flex-col justify-center rounded-xl bg-[#018F98] px-6 py-2.5 text-white shadow-sm sm:col-span-2 sm:px-8">
            <p class="text-[11px] font-semibold uppercase tracking-widest text-[#E6F7F8]">
                Periode {{ $summary['month_label'] }}
            </p>
            <h2 class="mt-1 text-xl font-semibold sm:text-2xl">
                Hai, {{ $distributor->name }}
            </h2>
            <p class="mt-1 max-w-3xl text-sm text-[#E6F7F8]">
                Pastikan setiap penyaluran Minyakita tercatat sesuai toko penerima.
            </p>
        </div>

        <div class="flex h-28 flex-col justify-center gap-1 rounded-xl border border-slate-200 bg-white p-4">
            <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-500">Total Laporan</p>
            <p class="text-4xl font-bold text-[#018F98]">{{ $formatNumber($summary['reports']) }}</p>
        </div>

        <div class="flex h-28 flex-col justify-center gap-1 rounded-xl border border-slate-200 bg-white p-4">
            <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-500">Jumlah Penyaluran</p>
            <p class="text-4xl font-bold text-[#018F98]">{{ $formatNumber($summary['cartons']) }}</p>
        </div>
    </div>

    <div class="mt-4 overflow-hidden rounded-xl border border-slate-200 bg-white">
        <div class="px-5 pt-5">
            <h3 class="text-lg font-semibold text-slate-900">Laporan Penyaluran</h3>
        </div>

        <form
            method="GET"
            action="{{ route('distributor.dashboard') }}"
            class="mt-4 flex flex-col gap-3 px-5 lg:flex-row lg:items-center"
        >
            <div class="relative min-w-0 flex-1">
                <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" />
                <input
                    type="search"
                    name="q"
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
                class="shrink-0 cursor-pointer rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-[#018F98]/30"
            >
                Terapkan Filter
            </button>
        </form>

        <div class="mt-4 overflow-x-auto">
            <table class="w-full min-w-[56rem] table-fixed divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-200 text-center text-[11px] uppercase tracking-widest text-slate-700">
                    <tr>
                        <th scope="col" class="w-[12%] px-5 py-3 align-middle font-semibold">NO</th>
                        <th scope="col" class="w-[18%] px-5 py-3 align-middle font-semibold">Tanggal</th>
                        <th scope="col" class="w-[24%] px-5 py-3 align-middle font-semibold">Pasar</th>
                        <th scope="col" class="w-[24%] px-5 py-3 align-middle font-semibold">Nama Toko</th>
                        <th scope="col" class="w-[22%] px-5 py-3 align-middle font-semibold">Jumlah Karton</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-center">
                    @forelse ($reports as $report)
                        <tr>
                            <td class="whitespace-nowrap px-5 py-4 align-middle font-mono text-xs text-slate-500">
                                {{ $loop->iteration }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 align-middle text-slate-600">
                                {{ $report->distribution_date->format('d/m/Y') }}
                            </td>
                            <td class="px-5 py-4 align-middle text-slate-700">
                                {{ $report->market->name }}
                            </td>
                            <td class="px-5 py-4 align-middle text-slate-700">
                                {{ $report->store?->name ?? '—' }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 align-middle font-semibold text-slate-900">
                                {{ $formatNumber($report->cartons) }} karton
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
</div>
