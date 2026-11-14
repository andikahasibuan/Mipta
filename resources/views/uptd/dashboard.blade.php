@extends('layouts.uptd')

@section('title', 'Dashboard')

@section('content')
    @php
        $formatNumber = fn (int $value): string => number_format($value, 0, ',', '.');

        $distributorNote = $summary['active_distributors'] === 0
            ? 'Belum ada penyaluran ke pasar'
            : 'Melakukan penyaluran ke pasar ini';
    @endphp

    <div class="min-w-0">
        <x-ui.flash-messages />

        <div class="mt-4 min-w-0 rounded-xl bg-[#018F98] px-5 py-6 text-white shadow-sm">
            <div class="min-w-0">
                <p class="truncate text-xl font-semibold text-white">{{ $contributor->name }}</p>
                <p class="mt-1 truncate text-sm text-[#E6F7F8]">{{ $contributor->market?->name ?? '—' }}</p>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <div class="min-w-0">
                    <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-500">Total Laporan</p>
                    <p class="mt-1 text-3xl font-bold text-slate-900">{{ $formatNumber($summary['reports']) }}</p>
                    <p class="mt-1 text-xs text-slate-500">Laporan kontributor aktif</p>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <div class="min-w-0">
                    <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-500">Kondisi normal</p>
                    <p class="mt-1 text-3xl font-bold text-slate-900">{{ $formatNumber($summary['normal_reports']) }}</p>
                    <p class="mt-1 text-xs text-slate-500">Berdasarkan riwayat laporan</p>
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <div class="min-w-0">
                    <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-500">Distributor Aktif</p>
                    <p class="mt-1 text-3xl font-bold text-slate-900">{{ $formatNumber($summary['active_distributors']) }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ $distributorNote }}</p>
                </div>
            </div>
        </div>

        <div class="mt-4 overflow-hidden rounded-xl border border-slate-200 bg-white">
            <div class="px-5 py-5">
                <h3 class="text-lg font-semibold text-slate-900">Laporan kondisi pasar terbaru</h3>
            </div>

            <div class="overflow-x-auto border-t border-slate-200">
                <table class="w-full min-w-[44rem] table-fixed divide-y divide-slate-200 text-sm">
                    <thead class="bg-[#E0F2F3] text-center text-[11px] uppercase tracking-widest text-[#018F98]">
                        <tr>
                            <th scope="col" class="w-[32%] px-5 py-3 font-semibold">Pasar</th>
                            <th scope="col" class="w-[28%] px-5 py-3 font-semibold">Kontributor</th>
                            <th scope="col" class="w-[24%] px-5 py-3 font-semibold">Tanggal</th>
                            <th scope="col" class="w-[16%] px-5 py-3 text-center font-semibold">Kondisi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($reports as $report)
                            <tr>
                                <td class="px-5 py-4 align-middle font-medium text-slate-900">
                                    {{ $contributor->market?->name ?? '—' }}
                                </td>
                                <td class="px-5 py-4 align-middle text-slate-600">
                                    {{ $report->contributor?->name ?? $contributor->name }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 align-middle text-slate-600">
                                    {{ $report->report_date->locale('id')->translatedFormat('j F Y') }}
                                </td>
                                <td class="px-5 py-4 text-center align-middle">
                                    <span @class([
                                        'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium',
                                        'bg-emerald-100 text-emerald-700' => $report->condition === \App\Enums\FieldCondition::Normal,
                                        'bg-amber-100 text-amber-700' => $report->condition === \App\Enums\FieldCondition::PerluPerhatian,
                                    ])>
                                        {{ $report->condition->label() }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-10 text-center text-sm text-slate-500">
                                    Belum ada laporan kondisi pasar
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
@endsection
