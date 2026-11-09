@extends('layouts.uptd')

@section('title', 'Dashboard UPTD Pasar')

@section('content')
    @php
        $formatNumber = fn (int $value): string => number_format($value, 0, ',', '.');

        $distributorNote = $summary['active_distributors'] === 0
            ? 'Belum ada penyaluran ke pasar'
            : 'Melakukan penyaluran ke pasar ini';
    @endphp

    <div class="min-w-0">
        <div>
            <h2 class="text-2xl font-semibold text-[#1F3A5F]">Dashboard UPTD Pasar</h2>
            <p class="mt-1 text-sm text-[#2D507E]">
                Ringkasan laporan kondisi lapangan sesuai identitas kontributor aktif.
            </p>
        </div>

        <x-ui.flash-messages />

        <div class="mt-5 flex min-w-0 items-center gap-4 rounded-xl bg-[#2D507E] px-5 py-6 text-white shadow-sm">
            <span aria-hidden="true" class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-white text-[#2D507E]">
                <x-heroicon-o-building-storefront class="h-7 w-7" />
            </span>

            <div class="min-w-0">
                <p class="truncate text-xl font-semibold text-white">{{ $contributor->name }}</p>
                <p class="mt-1 truncate text-sm text-[#D6E3F2]">{{ $contributor->market?->name ?? '—' }}</p>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="flex items-center gap-4 rounded-xl border border-slate-200 bg-white p-5">
                <div aria-hidden="true" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#E8F0FB] text-[#2D507E]">
                    <x-heroicon-o-document-text class="h-6 w-6" />
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-500">Total Laporan</p>
                    <p class="mt-1 text-3xl font-bold text-slate-900">{{ $formatNumber($summary['reports']) }}</p>
                    <p class="mt-1 text-xs text-slate-500">Laporan kontributor aktif</p>
                </div>
            </div>

            <div class="flex items-center gap-4 rounded-xl border border-slate-200 bg-white p-5">
                <div aria-hidden="true" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#E8F0FB] text-[#2D507E]">
                    <x-heroicon-o-check-circle class="h-6 w-6" />
                </div>
                <div class="min-w-0">
                    <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-500">Kondisi normal</p>
                    <p class="mt-1 text-3xl font-bold text-slate-900">{{ $formatNumber($summary['normal_reports']) }}</p>
                    <p class="mt-1 text-xs text-slate-500">Berdasarkan riwayat laporan</p>
                </div>
            </div>

            <div class="flex items-center gap-4 rounded-xl border border-slate-200 bg-white p-5">
                <div aria-hidden="true" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#E8F0FB] text-[#2D507E]">
                    <x-heroicon-o-truck class="h-6 w-6" />
                </div>
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
                    <thead class="bg-[#E8F0FB] text-center text-[11px] uppercase tracking-widest text-[#2D507E]">
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
