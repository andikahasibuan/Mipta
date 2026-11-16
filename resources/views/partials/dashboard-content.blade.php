@php
    $formatNumber = fn (int $value): string => number_format($value, 0, ',', '.');
@endphp

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <div class="flex h-28 flex-col justify-center rounded-xl bg-[#018F98] px-6 py-2.5 text-white shadow-sm sm:col-span-2 sm:px-8">
        <p class="text-[11px] font-semibold uppercase tracking-widest text-[#E6F7F8]">
            Periode {{ $summary['month_label'] }}
        </p>
        <h2 class="mt-1 text-xl font-semibold sm:text-2xl">
            Hai, {{ auth()->user()->name }}
        </h2>
        <p class="mt-1 max-w-3xl text-sm text-[#E6F7F8]">
            Selamat Datang di MIPTA Monitoring MinyaKita
        </p>
    </div>

    <div class="flex h-28 flex-col justify-center gap-1 rounded-xl border border-slate-200 bg-white p-4">
        <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-500">Total Laporan</p>
        <p class="text-4xl font-bold text-[#018F98]">{{ $formatNumber($summary['reports']) }}</p>
    </div>

    <div class="flex h-28 flex-col justify-center gap-1 rounded-xl border border-slate-200 bg-white p-4">
        <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-500">Total Penyaluran</p>
        <p class="text-4xl font-bold text-[#018F98]">{{ $formatNumber($summary['cartons']) }}</p>
    </div>
</div>

<form method="GET" action="{{ route($area.'.dashboard') }}" class="mt-4 rounded-xl border border-slate-200 bg-white p-3">
    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div class="relative">
            <label for="filter-month" class="sr-only">Bulan</label>
            <select
                id="filter-month"
                name="month"
                class="w-full cursor-pointer appearance-none rounded-lg border border-slate-200 bg-white py-2.5 pl-3.5 pr-10 text-sm text-slate-700 focus:border-[#018F98] focus:outline-none focus:ring-2 focus:ring-[#018F98]/30"
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
            <label for="filter-market" class="sr-only">Pasar</label>
            <select
                id="filter-market"
                name="market"
                class="w-full cursor-pointer appearance-none rounded-lg border border-slate-200 bg-white py-2.5 pl-3.5 pr-10 text-sm text-slate-700 focus:border-[#018F98] focus:outline-none focus:ring-2 focus:ring-[#018F98]/30"
            >
                <option value="">Semua Pasar</option>
                @foreach ($markets as $market)
                    <option value="{{ $market->id }}" @selected($filters['market'] === $market->id)>{{ $market->name }}</option>
                @endforeach
            </select>
            <x-heroicon-o-chevron-down class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
        </div>

        <div class="relative">
            <label for="filter-distributor" class="sr-only">Distributor</label>
            <select
                id="filter-distributor"
                name="distributor"
                class="w-full cursor-pointer appearance-none rounded-lg border border-slate-200 bg-white py-2.5 pl-3.5 pr-10 text-sm text-slate-700 focus:border-[#018F98] focus:outline-none focus:ring-2 focus:ring-[#018F98]/30"
            >
                <option value="">Semua Distributor</option>
                @foreach ($distributors as $distributor)
                    <option value="{{ $distributor->id }}" @selected($filters['distributor'] === $distributor->id)>{{ $distributor->name }}</option>
                @endforeach
            </select>
            <x-heroicon-o-chevron-down class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
        </div>

        <button
            type="submit"
            class="inline-flex w-full cursor-pointer items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-[#018F98] hover:bg-[#018F98] hover:text-white active:border-[#018F98] active:bg-[#018F98] active:text-white focus:outline-none focus:ring-2 focus:ring-[#018F98]/30"
        >
            Terapkan Filter
        </button>
    </div>
</form>

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
                        <div class="h-full rounded-full bg-[#018F98]" style="width: {{ $width }}%"></div>
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
                        borderColor: '#018F98',
                        backgroundColor: 'rgba(81, 145, 228, 0.12)',
                        pointBackgroundColor: '#018F98',
                        pointBorderColor: '#018F98',
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
