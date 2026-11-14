@php
    $formatNumber = fn (int $value): string => number_format($value, 0, ',', '.');
    $isPenyaluran = $activeTab === 'penyaluran';
@endphp

<form
    method="GET"
    action="{{ route($area.'.reports.index') }}"
    class="rounded-xl border border-slate-200 bg-white p-3"
>
    <input type="hidden" name="tab" value="{{ $activeTab }}">

    <div class="flex flex-col gap-3 lg:flex-row lg:items-end">
        <div class="min-w-0 flex-1">
            <label for="filter-start-date" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-widest text-slate-500">
                Periode mulai
            </label>
            <input
                type="date"
                id="filter-start-date"
                name="start_date"
                value="{{ $filters['start_date'] }}"
                class="w-full cursor-pointer rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 focus:border-[#018F98] focus:outline-none focus:ring-2 focus:ring-[#018F98]/30"
            >
        </div>

        <div class="min-w-0 flex-1">
            <label for="filter-end-date" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-widest text-slate-500">
                Sampai
            </label>
            <input
                type="date"
                id="filter-end-date"
                name="end_date"
                value="{{ $filters['end_date'] }}"
                class="w-full cursor-pointer rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 focus:border-[#018F98] focus:outline-none focus:ring-2 focus:ring-[#018F98]/30"
            >
        </div>

        <button
            type="submit"
            class="shrink-0 cursor-pointer rounded-lg bg-[#018F98] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#018F98]/90 focus:outline-none focus:ring-2 focus:ring-[#018F98]/30"
        >
            Tampilkan rekap
        </button>
    </div>
</form>

<div class="mt-4 flex flex-wrap items-center justify-between gap-3">
    <div class="flex flex-wrap gap-2">
        <a
            href="{{ route($area.'.reports.index', ['start_date' => $filters['start_date'], 'end_date' => $filters['end_date'], 'tab' => 'penyaluran']) }}"
            @class([
                'rounded-lg px-4 py-2 text-sm font-semibold transition',
                'bg-[#018F98] text-white' => $isPenyaluran,
                'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50' => ! $isPenyaluran,
            ])
        >
            Rekap Penyaluran
        </a>
        <a
            href="{{ route($area.'.reports.index', ['start_date' => $filters['start_date'], 'end_date' => $filters['end_date'], 'tab' => 'kondisi']) }}"
            @class([
                'rounded-lg px-4 py-2 text-sm font-semibold transition',
                'bg-[#018F98] text-white' => ! $isPenyaluran,
                'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50' => $isPenyaluran,
            ])
        >
            Rekap Kondisi Pasar
        </a>
    </div>

    <div class="ml-auto flex flex-wrap items-center justify-end gap-2">
        @foreach ($exports as $export)
            <button
                type="button"
                disabled
                aria-disabled="true"
                class="inline-flex shrink-0 cursor-not-allowed items-center gap-2 rounded-lg bg-[#018F98] px-4 py-2.5 text-sm font-semibold text-white opacity-60"
            >
                <x-dynamic-component :component="'heroicon-o-'.$export['icon']" class="h-4 w-4" />
                {{ $export['label'] }}
            </button>
        @endforeach
    </div>
</div>

@if ($isPenyaluran)
    <div class="mt-4 overflow-hidden rounded-xl border border-slate-200 bg-white">
        <div class="px-5 pt-5">
            <h2 class="text-lg font-semibold text-slate-900">Rekap Laporan Penyaluran</h2>
        </div>

        <div class="mt-4 overflow-x-auto">
            <table class="w-full min-w-[48rem] table-fixed divide-y divide-slate-200 text-sm">
                <thead class="bg-[#E0F2F3] text-center text-[11px] uppercase tracking-widest text-[#018F98]">
                    <tr>
                        <th scope="col" class="w-[34%] px-5 py-3 align-middle font-semibold">Pasar</th>
                        <th scope="col" class="w-[20%] px-5 py-3 align-middle font-semibold">Jumlah Laporan</th>
                        <th scope="col" class="w-[23%] px-5 py-3 align-middle font-semibold">Jumlah Karton</th>
                        <th scope="col" class="w-[23%] px-5 py-3 align-middle font-semibold">Distributor Terlibat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-center">
                    @forelse ($distributionRecap as $row)
                        <tr>
                            <td class="px-5 py-4 text-left align-middle text-slate-900">
                                {{ $row->market?->name ?? '—' }}
                            </td>
                            <td class="px-5 py-4 align-middle text-slate-700">
                                {{ $formatNumber((int) $row->reports_count) }}
                            </td>
                            <td class="px-5 py-4 align-middle font-semibold text-slate-900">
                                {{ $formatNumber((int) $row->cartons_sum) }}
                            </td>
                            <td class="px-5 py-4 align-middle text-slate-700">
                                {{ $formatNumber((int) $row->distributors_count) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-8 text-center text-sm text-slate-500">
                                Belum ada data penyaluran pada periode ini
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@else
    <div class="mt-4 overflow-hidden rounded-xl border border-slate-200 bg-white">
        <div class="px-5 pt-5">
            <h2 class="text-lg font-semibold text-slate-900">Rekap Laporan Kondisi Pasar</h2>
        </div>

        <div class="mt-4 overflow-x-auto">
            <table class="w-full min-w-[64rem] table-fixed divide-y divide-slate-200 text-sm">
                <thead class="bg-[#E0F2F3] text-center text-[11px] uppercase tracking-widest text-[#018F98]">
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
                    @forelse ($conditionReports as $report)
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
                                Belum ada laporan kondisi pasar pada periode ini
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($conditionReports->isNotEmpty())
            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 px-5 py-3">
                <p class="text-xs text-slate-500">
                    Menampilkan {{ $formatNumber($conditionReports->count()) }} dari {{ $formatNumber($conditionReports->total()) }} laporan
                </p>

                @if ($conditionReports->hasPages())
                    {{ $conditionReports->links() }}
                @endif
            </div>
        @endif
    </div>
@endif
