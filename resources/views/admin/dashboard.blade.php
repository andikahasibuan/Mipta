@extends('layouts.admin')

@section('title', 'Dashboard Admin')

@section('page-title', 'Dashboard Monitoring')

@section('content')
    @php
        $formatNumber = fn (int $value): string => number_format($value, 0, ',', '.');

        $change = $summary['reports_change'];
        $changeLabel = $change === null
            ? 'Belum ada data laporan'
            : ($change > 0 ? '+' : '').number_format($change, 1, ',', '.').'% dari bulan lalu';
        $changeColor = match ($summary['reports_change_direction']) {
            'up' => 'text-emerald-600',
            'down' => 'text-red-600',
            default => 'text-slate-400',
        };
    @endphp

    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <h2 class="text-2xl font-semibold text-slate-900">
                Selamat datang, {{ auth()->user()->name }}
            </h2>
            <p class="mt-1 text-sm text-slate-500">
                Pantau penyaluran Minyakita secara terintegrasi
            </p>
        </div>

        <button
            type="button"
            disabled
            aria-disabled="true"
            title="Akan tersedia setelah modul laporan selesai"
            class="shrink-0 cursor-not-allowed rounded-lg bg-[#2D507E] px-4 py-2.5 text-sm font-semibold text-white opacity-60"
        >
            Unduh Rekap Bulanan
        </button>
    </div>

    <form method="GET" action="{{ route('admin.dashboard') }}" class="mt-6 rounded-xl border border-slate-200 bg-white p-3">
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
                <label for="filter-market" class="sr-only">Pasar</label>
                <select
                    id="filter-market"
                    name="market"
                    class="w-full cursor-pointer rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 focus:border-[#5191E4] focus:outline-none focus:ring-2 focus:ring-[#5191E4]/30"
                >
                    <option value="">Semua Pasar</option>
                    @foreach ($markets as $market)
                        <option value="{{ $market->id }}" @selected($filters['market'] === $market->id)>{{ $market->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="filter-distributor" class="sr-only">Distributor</label>
                <select
                    id="filter-distributor"
                    name="distributor"
                    class="w-full cursor-pointer rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 focus:border-[#5191E4] focus:outline-none focus:ring-2 focus:ring-[#5191E4]/30"
                >
                    <option value="">Semua Distributor</option>
                    @foreach ($distributors as $distributor)
                        <option value="{{ $distributor->id }}" @selected($filters['distributor'] === $distributor->id)>{{ $distributor->name }}</option>
                    @endforeach
                </select>
            </div>

            <button
                type="submit"
                class="w-full cursor-pointer rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-[#5191E4]/30"
            >
                Terapkan Filter
            </button>
        </div>
    </form>

    <div class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="flex items-center gap-4 rounded-xl border border-slate-200 bg-white p-5">
            <div aria-hidden="true" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#E8F0FB] text-[#2D507E]">
                <x-heroicon-o-document-text class="h-6 w-6" />
            </div>
            <div class="min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-500">Total Laporan</p>
                <p class="mt-1 text-3xl font-bold text-slate-900">{{ $formatNumber($summary['reports']) }}</p>
                <p class="mt-1 text-xs font-medium {{ $changeColor }}">{{ $changeLabel }}</p>
            </div>
        </div>

        <div class="flex items-center gap-4 rounded-xl border border-slate-200 bg-white p-5">
            <div aria-hidden="true" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#E8F0FB] text-[#2D507E]">
                <x-heroicon-o-truck class="h-6 w-6" />
            </div>
            <div class="min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-500">Total Penyaluran</p>
                <p class="mt-1 text-3xl font-bold text-slate-900">{{ $formatNumber($summary['cartons']) }}</p>
                <p class="mt-1 text-xs text-slate-500">karton pada {{ $summary['month_label'] }}</p>
            </div>
        </div>

        <div class="flex items-center gap-4 rounded-xl border border-slate-200 bg-white p-5">
            <div aria-hidden="true" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#E8F0FB] text-[#2D507E]">
                <x-heroicon-o-building-office-2 class="h-6 w-6" />
            </div>
            <div class="min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-500">Distributor Aktif</p>
                <p class="mt-1 text-3xl font-bold text-slate-900">{{ $formatNumber($summary['active_distributors']) }}</p>
                <p class="mt-1 text-xs text-slate-500">
                    @if ($summary['total_distributors'] === 0)
                        Belum ada data distributor
                    @elseif ($summary['active_distributors'] === $summary['total_distributors'])
                        Seluruhnya aktif
                    @else
                        {{ $summary['active_distributors'] }} dari {{ $summary['total_distributors'] }} distributor aktif
                    @endif
                </p>
            </div>
        </div>

        <div class="flex items-center gap-4 rounded-xl border border-slate-200 bg-white p-5">
            <div aria-hidden="true" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#E8F0FB] text-[#2D507E]">
                <x-heroicon-o-map-pin class="h-6 w-6" />
            </div>
            <div class="min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-500">Pasar Terpantau</p>
                <p class="mt-1 text-3xl font-bold text-slate-900">{{ $formatNumber($summary['monitored_markets']) }}</p>
                <p class="mt-1 text-xs text-slate-500">
                    @if ($summary['total_markets'] === 0)
                        Belum ada data pasar
                    @else
                        {{ $summary['coverage_percent'] }}% wilayah terpantau
                    @endif
                </p>
            </div>
        </div>
    </div>

    @php
        $maxCartons = collect($breakdown)->max('cartons') ?: 0;
        $breakdownTotal = collect($breakdown)->sum('cartons');
    @endphp

    <div class="mt-4 grid gap-4 xl:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-5 xl:col-span-2">
            <div class="flex items-baseline justify-between gap-3">
                <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-500">Perkembangan Bulanan</p>
                <p class="text-xs text-slate-400">{{ $summary['month_label'] }}</p>
            </div>

            <div class="mt-4 h-80">
                <canvas id="monthly-chart"></canvas>
            </div>
        </div>

        <div class="flex flex-col rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-500">Perbandingan</p>
            <h3 class="mt-1 text-base font-semibold text-slate-900">Penyaluran per Distributor</h3>

            <div class="mt-4 flex flex-1 flex-col gap-4">
                @forelse ($breakdown as $row)
                    @php
                        $width = $maxCartons > 0
                            ? max(2, (int) round($row['cartons'] / $maxCartons * 100))
                            : 0;
                    @endphp

                    <div>
                        <div class="flex items-start justify-between gap-3">
                            <p class="min-w-0 text-sm text-slate-700">{{ $row['name'] }}</p>
                            <p class="shrink-0 text-sm font-semibold text-slate-900">
                                {{ $formatNumber($row['cartons']) }}
                                <span class="font-normal text-slate-500">karton</span>
                            </p>
                        </div>
                        <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-200">
                            <div class="h-full rounded-full bg-[#5191E4]" style="width: {{ $width }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Belum ada data distributor</p>
                @endforelse
            </div>

            <div class="mt-5 flex items-baseline justify-between gap-3 border-t border-slate-200 pt-4">
                <p class="text-xs text-slate-500">Total Penyaluran {{ $summary['month_label'] }}</p>
                <p class="text-sm font-semibold text-slate-900">{{ $formatNumber($breakdownTotal) }} karton</p>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const canvas = document.getElementById('monthly-chart');

            if (! canvas || typeof Chart === 'undefined') {
                return;
            }

            const chartData = @json($chart);
            const numberFormat = new Intl.NumberFormat('id-ID');
            const tickColor = '#94a3b8';
            const tickFont = { family: 'Instrument Sans', size: 12 };

            new Chart(canvas, {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: 'Penyaluran Minyakita',
                        data: chartData.values,
                        borderColor: '#5191E4',
                        backgroundColor: 'rgba(81, 145, 228, 0.12)',
                        pointBackgroundColor: '#5191E4',
                        pointBorderColor: '#5191E4',
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        borderWidth: 2,
                        tension: 0.35,
                        fill: true,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                boxWidth: 8,
                                color: '#64748b',
                                font: tickFont,
                            },
                        },
                        tooltip: {
                            callbacks: {
                                label: (context) => ' ' + numberFormat.format(context.parsed.y) + ' karton',
                            },
                        },
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: {
                                color: tickColor,
                                maxTicksLimit: 7,
                                font: tickFont,
                            },
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(148, 163, 184, 0.2)' },
                            ticks: {
                                color: tickColor,
                                font: tickFont,
                                callback: (value) => numberFormat.format(value),
                            },
                        },
                    },
                },
            });
        });
    </script>
@endpush
