@extends('layouts.uptd')

@section('title', 'Dashboard')

@section('content')
    @php
        $formatNumber = fn (int $value): string => number_format($value, 0, ',', '.');
    @endphp

    <div class="min-w-0">
        <x-ui.flash-messages />

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="flex h-28 flex-col justify-center rounded-xl bg-[#018F98] px-6 py-2.5 text-white shadow-sm sm:col-span-2 sm:px-8">
                <p class="text-[11px] font-semibold uppercase tracking-widest text-[#E6F7F8]">Kontributor UPTD</p>
                <h2 class="mt-1 truncate text-xl font-semibold sm:text-2xl">{{ $contributor->name }}</h2>
                <p class="mt-1 truncate text-sm text-[#E6F7F8]">{{ $contributor->market?->name ?? '—' }}</p>
            </div>

            <div class="flex h-28 flex-col justify-center gap-1 rounded-xl border border-slate-200 bg-white p-4">
                <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-500">Total Laporan</p>
                <p class="text-4xl font-bold text-[#018F98]">{{ $formatNumber($summary['reports']) }}</p>
            </div>

            <div class="flex h-28 flex-col justify-center gap-1 rounded-xl border border-slate-200 bg-white p-4">
                <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-500">Toko Penerima</p>
                <p class="text-4xl font-bold text-[#018F98]">{{ $formatNumber($summary['stores']) }}</p>
            </div>
        </div>

        <div class="mt-4 overflow-hidden rounded-xl border border-slate-200 bg-white">
            <div class="px-5 py-5">
                <h3 class="text-lg font-semibold text-slate-900">Laporan kondisi pasar</h3>
            </div>

            <div class="overflow-x-auto border-t border-slate-200">
                <table class="w-full min-w-[44rem] table-fixed divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-200 text-center text-[11px] uppercase tracking-widest text-slate-700">
                        <tr>
                            <th scope="col" class="w-[6%] px-3 py-3 font-semibold">NO</th>
                            <th scope="col" class="w-[28%] px-5 py-3 font-semibold">Pasar</th>
                            <th scope="col" class="w-[26%] px-5 py-3 font-semibold">Kontributor</th>
                            <th scope="col" class="w-[23%] px-5 py-3 font-semibold">Tanggal</th>
                            <th scope="col" class="w-[15%] px-5 py-3 text-center font-semibold">Kondisi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($reports as $report)
                            <tr>
                                <td class="px-3 py-4 align-middle font-mono text-xs text-slate-500">
                                    {{ $loop->iteration }}
                                </td>
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
                                <td colspan="5" class="px-5 py-10 text-center text-sm text-slate-500">
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
