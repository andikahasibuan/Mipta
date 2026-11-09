<x-ui.page-heading
    :title="$title"
    :subtitle="$subtitle"
/>

@php
    $formatNumber = fn (int $value): string => number_format($value, 0, ',', '.');
@endphp

<x-ui.flash-messages />

<form
    method="GET"
    action="{{ route($area.'.supervision.index') }}"
    class="rounded-xl border border-slate-200 bg-white p-3"
>
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
        <div class="relative min-w-0 flex-1">
            <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" />
            <input
                type="search"
                name="q"
                value="{{ $filters['q'] }}"
                placeholder="Cari kontributor atau pasar..."
                class="w-full rounded-lg border border-slate-200 bg-white py-2.5 pl-10 pr-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-[#5191E4] focus:outline-none focus:ring-2 focus:ring-[#5191E4]/30"
            >
        </div>

        <div class="shrink-0">
            <label for="filter-month" class="sr-only">Bulan</label>
            <input
                type="month"
                id="filter-month"
                name="month"
                value="{{ $filters['month'] }}"
                class="w-full cursor-pointer rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 focus:border-[#5191E4] focus:outline-none focus:ring-2 focus:ring-[#5191E4]/30"
            >
        </div>

        <div class="relative shrink-0">
            <label for="filter-condition" class="sr-only">Kondisi</label>
            <select
                id="filter-condition"
                name="condition"
                class="w-full cursor-pointer appearance-none rounded-lg border border-slate-200 bg-white py-2.5 pl-3.5 pr-10 text-sm text-slate-700 focus:border-[#5191E4] focus:outline-none focus:ring-2 focus:ring-[#5191E4]/30"
            >
                <option value="">Semua Kondisi</option>
                @foreach ($conditions as $condition)
                    <option value="{{ $condition->value }}" @selected($filters['condition'] === $condition->value)>
                        {{ $condition->label() }}
                    </option>
                @endforeach
            </select>
            <x-heroicon-o-chevron-down class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
        </div>

        <button
            type="submit"
            class="shrink-0 cursor-pointer rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-[#5191E4]/30"
        >
            Terapkan Filter
        </button>
    </div>
</form>

<div class="mt-4 overflow-hidden rounded-xl border border-slate-200 bg-white">
    <div class="px-5 pt-5">
        <h2 class="text-lg font-semibold text-slate-900">Laporan Kondisi Lapangan</h2>
    </div>

    <div class="mt-4 overflow-x-auto">
        <table class="w-full min-w-[64rem] table-fixed divide-y divide-slate-200 text-sm">
            <thead class="bg-[#E8F0FB] text-center text-[11px] uppercase tracking-widest text-[#2D507E]">
                <tr>
                    <th scope="col" class="w-[8%] px-5 py-3 align-middle font-semibold">ID Laporan</th>
                    <th scope="col" class="w-[12%] px-5 py-3 align-middle font-semibold">Tanggal Laporan</th>
                    <th scope="col" class="w-[18%] px-5 py-3 align-middle font-semibold">Identitas Kontributor</th>
                    <th scope="col" class="w-[16%] px-5 py-3 align-middle font-semibold">Pasar</th>
                    <th scope="col" class="w-[30%] px-5 py-3 align-middle font-semibold">Deskripsi Kondisi Lapangan</th>
                    <th scope="col" class="w-[16%] px-5 py-3 align-middle font-semibold">Kondisi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-center">
                @forelse ($reports as $report)
                    <tr>
                        <td class="whitespace-nowrap px-5 py-4 align-middle font-mono text-xs text-slate-500">
                            {{ $report->code }}
                        </td>
                        <td class="whitespace-nowrap px-5 py-4 align-middle text-slate-600">
                            {{ $report->report_date->format('d/m/Y') }}
                        </td>
                        <td class="px-5 py-4 align-middle text-slate-900">
                            {{ $report->contributor?->name ?? '—' }}
                        </td>
                        <td class="px-5 py-4 align-middle text-slate-700">
                            {{ $report->market?->name ?? '—' }}
                        </td>
                        <td class="px-5 py-4 text-left align-middle text-slate-700">
                            {{ $report->description }}
                        </td>
                        <td class="whitespace-nowrap px-5 py-4 align-middle">
                            <span @class([
                                'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium',
                                'bg-emerald-100 text-emerald-700' => $report->condition->value === 'normal',
                                'bg-amber-100 text-amber-700' => $report->condition->value === 'perlu_perhatian',
                            ])>
                                {{ $report->condition->label() }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-8 text-center text-sm text-slate-500">
                            @if ($filters['q'] !== '' || $filters['condition'] !== null)
                                Tidak ada laporan yang sesuai filter
                            @else
                                Belum ada laporan kondisi lapangan
                            @endif
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
