@php
    $formatNumber = fn (int $value): string => number_format($value, 0, ',', '.');
@endphp

<div class="min-w-0">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <h2 class="text-2xl font-semibold text-[#1F3A5F]">Dashboard Distributor</h2>
            <p class="mt-1 text-sm text-[#2D507E]">
                Pantau laporan penyaluran milik {{ $distributor->name }}
            </p>
        </div>

        <form method="GET" action="{{ route('distributor.dashboard') }}" class="w-44 shrink-0">
            <label for="filter-month" class="block text-[11px] font-semibold uppercase tracking-widest text-slate-400">
                Periode aktif
            </label>
            <div class="relative mt-1">
                <select
                    id="filter-month"
                    name="month"
                    onchange="this.form.submit()"
                    class="w-full cursor-pointer appearance-none rounded-lg border border-slate-200 bg-white py-2.5 pl-3.5 pr-10 text-sm text-slate-700 focus:border-[#5191E4] focus:outline-none focus:ring-2 focus:ring-[#5191E4]/30"
                >
                    @foreach ($months as $option)
                        <option value="{{ $option['value'] }}" @selected($filters['month'] === $option['value'])>
                            {{ $option['label'] }}
                        </option>
                    @endforeach
                </select>
                <x-heroicon-o-chevron-down class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            </div>
        </form>
    </div>

    <div class="mt-5 rounded-xl bg-[#2D507E] px-6 py-6 text-white shadow-sm sm:px-8 sm:py-7">
        <p class="text-[11px] font-semibold uppercase tracking-widest text-[#A9C0D6]">
            Periode {{ $summary['month_label'] }}
        </p>
        <h2 class="mt-2 text-2xl font-semibold sm:text-3xl">
            Selamat datang, {{ $distributor->name }}
        </h2>
        <p class="mt-2 max-w-3xl text-sm text-[#D6E3F2]">
            Pastikan setiap penyaluran Minyakita tercatat sesuai toko penerima.
        </p>
    </div>

    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="flex items-center gap-4 rounded-xl border border-slate-200 bg-white p-5">
            <div aria-hidden="true" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#E8F0FB] text-[#2D507E]">
                <x-heroicon-o-document-text class="h-6 w-6" />
            </div>
            <div class="min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-500">Total Laporan</p>
                <p class="mt-1 text-3xl font-bold text-slate-900">{{ $formatNumber($summary['reports']) }}</p>
                <p class="mt-1 text-xs text-slate-500">Laporan {{ $summary['month_label'] }}</p>
            </div>
        </div>

        <div class="flex items-center gap-4 rounded-xl border border-slate-200 bg-white p-5">
            <div aria-hidden="true" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#E8F0FB] text-[#2D507E]">
                <x-heroicon-o-archive-box class="h-6 w-6" />
            </div>
            <div class="min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-500">Jumlah Penyaluran</p>
                <p class="mt-1 text-3xl font-bold text-slate-900">{{ $formatNumber($summary['cartons']) }}</p>
                <p class="mt-1 text-xs text-slate-500">karton pada {{ $summary['month_label'] }}</p>
            </div>
        </div>

        <div class="flex items-center gap-4 rounded-xl border border-slate-200 bg-white p-5">
            <div aria-hidden="true" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#E8F0FB] text-[#2D507E]">
                <x-heroicon-o-clock class="h-6 w-6" />
            </div>
            <div class="min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-500">Laporan Draf</p>
                <p class="mt-1 text-3xl font-bold text-slate-900">{{ $formatNumber($summary['drafts']) }}</p>
                <p class="mt-1 text-xs text-slate-500">
                    {{ $summary['drafts'] === 0 ? 'Tidak ada laporan draf' : 'Laporan belum dikirim' }}
                </p>
            </div>
        </div>
    </div>

    <div class="mt-4 overflow-hidden rounded-xl border border-slate-200 bg-white">
        <div class="px-5 pt-5">
            <h3 class="text-lg font-semibold text-slate-900">Laporan Penyaluran</h3>
            <p class="mt-1 text-sm text-slate-500">Data terbaru dari distributor</p>
        </div>

        <div class="mt-4 overflow-x-auto">
            <table class="w-full min-w-[56rem] table-fixed divide-y divide-slate-200 text-sm">
                <thead class="bg-[#E8F0FB] text-center text-[11px] uppercase tracking-widest text-[#2D507E]">
                    <tr>
                        <th scope="col" class="w-[12%] px-5 py-3 align-middle font-semibold">ID</th>
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
                                {{ $report->code }}
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
