@extends('layouts.admin')

@section('title', 'Monitoring & Analisis')

@section('content')
    <x-ui.page-heading
        title="Monitoring & Analisis"
        subtitle="Cari, filter, dan kelola data sistem secara terpusat"
    />

    @php
        $formatNumber = fn (int $value): string => number_format($value, 0, ',', '.');
        $formatAvg = fn (float $value): string => number_format($value, 1, ',', '.');
    @endphp

    <form method="GET" action="{{ route('admin.monitoring.index') }}" class="rounded-xl border border-slate-200 bg-white p-3">
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div class="relative">
                <label for="filter-month" class="sr-only">Bulan</label>
                <select
                    id="filter-month"
                    name="month"
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

            <div class="relative">
                <label for="filter-distributor" class="sr-only">Distributor</label>
                <select
                    id="filter-distributor"
                    name="distributor"
                    class="w-full cursor-pointer appearance-none rounded-lg border border-slate-200 bg-white py-2.5 pl-3.5 pr-10 text-sm text-slate-700 focus:border-[#5191E4] focus:outline-none focus:ring-2 focus:ring-[#5191E4]/30"
                >
                    <option value="">Semua Distributor</option>
                    @foreach ($distributors as $distributor)
                        <option value="{{ $distributor->id }}" @selected($filters['distributor'] === $distributor->id)>{{ $distributor->name }}</option>
                    @endforeach
                </select>
                <x-heroicon-o-chevron-down class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            </div>

            <div class="relative">
                <label for="filter-market" class="sr-only">Pasar</label>
                <select
                    id="filter-market"
                    name="market"
                    class="w-full cursor-pointer appearance-none rounded-lg border border-slate-200 bg-white py-2.5 pl-3.5 pr-10 text-sm text-slate-700 focus:border-[#5191E4] focus:outline-none focus:ring-2 focus:ring-[#5191E4]/30"
                >
                    <option value="">Semua Pasar</option>
                    @foreach ($markets as $market)
                        <option value="{{ $market->id }}" @selected($filters['market'] === $market->id)>{{ $market->name }}</option>
                    @endforeach
                </select>
                <x-heroicon-o-chevron-down class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
            </div>

            <button
                type="submit"
                class="w-full cursor-pointer rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-[#5191E4]/30"
            >
                Terapkan Filter
            </button>
        </div>
    </form>

    <div class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <div class="flex items-center gap-4 rounded-xl border border-slate-200 bg-white p-5">
            <div aria-hidden="true" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#E8F0FB] text-[#2D507E]">
                <x-heroicon-o-truck class="h-6 w-6" />
            </div>
            <div class="min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-500">Total Penyaluran Periode Ini</p>
                <p class="mt-1 text-3xl font-bold text-slate-900">{{ $formatNumber($summary['cartons']) }}</p>
                <p class="mt-1 text-xs text-slate-500">karton pada {{ $summary['month_label'] }}</p>
            </div>
        </div>

        <div class="flex items-center gap-4 rounded-xl border border-slate-200 bg-white p-5">
            <div aria-hidden="true" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#E8F0FB] text-[#2D507E]">
                <x-heroicon-o-chart-bar class="h-6 w-6" />
            </div>
            <div class="min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-500">Rata-rata per Laporan</p>
                <p class="mt-1 text-3xl font-bold text-slate-900">{{ $formatAvg($summary['avg_per_report']) }}</p>
                <p class="mt-1 text-xs text-slate-500">karton per laporan</p>
            </div>
        </div>

        <div class="flex items-center gap-4 rounded-xl border border-slate-200 bg-white p-5">
            <div aria-hidden="true" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#E8F0FB] text-[#2D507E]">
                <x-heroicon-o-map-pin class="h-6 w-6" />
            </div>
            <div class="min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-500">Pasar Terjangkau</p>
                <p class="mt-1 text-3xl font-bold text-slate-900">{{ $formatNumber($summary['reached_markets']) }}</p>
                <p class="mt-1 text-xs text-slate-500">
                    @if ($summary['total_markets'] === 0)
                        Belum ada data pasar
                    @else
                        dari {{ $formatNumber($summary['total_markets']) }} pasar
                    @endif
                </p>
            </div>
        </div>
    </div>

    <div class="mt-4 grid gap-4 xl:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-5 xl:col-span-2">
            <div class="flex items-baseline justify-between gap-3">
                <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-500">Ringkasan Distribusi per Pasar</p>
                <p class="text-xs text-slate-400">{{ $summary['month_label'] }}</p>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-center text-[11px] uppercase tracking-widest text-slate-500">
                        <tr>
                            <th scope="col" class="px-5 py-3 text-left font-semibold">Pasar</th>
                            <th scope="col" class="px-5 py-3 font-semibold">Jumlah Laporan</th>
                            <th scope="col" class="px-5 py-3 font-semibold">Total Karton</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($perMarket as $row)
                            <tr>
                                <td class="px-5 py-3 text-left text-slate-900">{{ $row['name'] }}</td>
                                <td class="px-5 py-3 text-center text-slate-700">{{ $formatNumber($row['reports']) }}</td>
                                <td class="px-5 py-3 text-center font-semibold text-slate-900">{{ $formatNumber($row['cartons']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-5 py-8 text-center text-sm text-slate-500">Belum ada data distribusi</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex flex-col rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-500">Kontribusi Distributor</p>
            <h3 class="mt-1 text-base font-semibold text-slate-900">Proporsi Penyaluran</h3>

            <div class="mt-4 h-56">
                <canvas id="distributor-donut"></canvas>
            </div>

            <div class="mt-4 flex flex-1 flex-col gap-3">
                @forelse ($perDistributor as $row)
                    @php
                        $percentage = $summary['cartons'] > 0
                            ? round($row['cartons'] / $summary['cartons'] * 100, 1)
                            : 0;
                    @endphp
                    <div class="flex items-start justify-between gap-3">
                        <p class="min-w-0 text-sm text-slate-700">{{ $row['name'] }}</p>
                        <p class="shrink-0 text-sm font-semibold text-slate-900">
                            {{ $formatNumber($row['cartons']) }}
                            <span class="font-normal text-slate-500">karton · {{ $percentage }}%</span>
                        </p>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Belum ada data distributor</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const canvas = document.getElementById('distributor-donut');

            if (! canvas || typeof Chart === 'undefined') {
                return;
            }

            const donutData = @json($donut);
            const palette = ['#2D507E', '#5191E4', '#FDBA12', '#7C3AED', '#10B981', '#F59E0B', '#EF4444', '#3B82F6'];

            new Chart(canvas, {
                type: 'doughnut',
                data: {
                    labels: donutData.labels,
                    datasets: [{
                        data: donutData.values,
                        backgroundColor: palette,
                        borderColor: '#ffffff',
                        borderWidth: 2,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '62%',
                    plugins: {
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            callbacks: {
                                label: (context) => {
                                    const total = context.dataset.data.reduce((sum, value) => sum + value, 0);
                                    const percent = total > 0 ? Math.round((context.parsed / total) * 100) : 0;

                                    return ' ' + context.label + ': ' + context.parsed + ' karton (' + percent + '%)';
                                },
                            },
                        },
                    },
                },
            });
        });
    </script>
@endpush
