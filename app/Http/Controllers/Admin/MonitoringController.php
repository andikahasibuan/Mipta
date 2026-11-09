<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DistributionStatus;
use App\Http\Controllers\Controller;
use App\Models\Distribution;
use App\Models\Distributor;
use App\Models\Market;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MonitoringController extends Controller
{
    /**
     * @var array<int, string>
     */
    private const MONTH_NAMES = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    /**
     * Tampilkan halaman monitoring dan analisis.
     */
    public function index(Request $request): View
    {
        $month = $request->string('month')->toString();

        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            $month = now()->format('Y-m');
        }

        $distributorId = $this->validId($request->string('distributor')->toString(), Distributor::class);
        $marketId = $this->validId($request->string('market')->toString(), Market::class);

        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $periodStart = $start->toDateString();
        $periodEnd = $start->copy()->endOfMonth()->toDateString();

        $distributions = Distribution::query()
            ->where('status', DistributionStatus::Submitted->value)
            ->when($distributorId, fn ($query, int $id) => $query->where('distributor_id', $id))
            ->when($marketId, fn ($query, int $id) => $query->where('market_id', $id))
            ->whereBetween('distribution_date', [$periodStart, $periodEnd])
            ->get(['distributor_id', 'market_id', 'cartons']);

        $totalCartons = (int) $distributions->sum('cartons');
        $totalReports = $distributions->count();
        $avgPerReport = $totalReports > 0 ? round($totalCartons / $totalReports, 1) : 0.0;

        $reachedMarkets = $distributions->unique('market_id')->count();

        $cartonsByMarket = $distributions->groupBy('market_id')->map->sum('cartons');
        $reportsByMarket = $distributions->groupBy('market_id')->map->count();

        $perMarket = Market::query()
            ->when($marketId, fn ($query, int $id) => $query->whereKey($id))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Market $market): array => [
                'name' => $market->name,
                'reports' => (int) ($reportsByMarket[$market->id] ?? 0),
                'cartons' => (int) ($cartonsByMarket[$market->id] ?? 0),
            ])
            ->sortByDesc('cartons')
            ->values()
            ->all();

        $cartonsByDistributor = $distributions->groupBy('distributor_id')->map->sum('cartons');

        $perDistributor = Distributor::query()
            ->when($distributorId, fn ($query, int $id) => $query->whereKey($id))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Distributor $distributor): array => [
                'name' => $distributor->name,
                'cartons' => (int) ($cartonsByDistributor[$distributor->id] ?? 0),
            ])
            ->filter(fn (array $row) => $row['cartons'] > 0)
            ->sortByDesc('cartons')
            ->values()
            ->all();

        $donut = [
            'labels' => array_column($perDistributor, 'name'),
            'values' => array_column($perDistributor, 'cartons'),
        ];

        return view('admin.monitoring.index', [
            'summary' => [
                'month' => $month,
                'month_label' => $this->monthLabel($month),
                'cartons' => $totalCartons,
                'reports' => $totalReports,
                'avg_per_report' => $avgPerReport,
                'reached_markets' => $reachedMarkets,
                'total_markets' => Market::count(),
            ],
            'perMarket' => $perMarket,
            'perDistributor' => $perDistributor,
            'donut' => $donut,
            'filters' => [
                'month' => $month,
                'market' => $marketId,
                'distributor' => $distributorId,
            ],
            'months' => $this->monthOptions($month),
            'markets' => Market::orderBy('name')->get(['id', 'name']),
            'distributors' => Distributor::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * @param  class-string<Model>  $model
     */
    private function validId(string $value, string $model): ?int
    {
        if ($value === '' || ! ctype_digit($value)) {
            return null;
        }

        $id = (int) $value;

        return $model::whereKey($id)->exists() ? $id : null;
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    private function monthOptions(string $selectedMonth): array
    {
        $months = [];
        $cursor = Carbon::now()->startOfMonth();

        for ($index = 0; $index < 6; $index++) {
            $value = $cursor->format('Y-m');

            $months[] = [
                'value' => $value,
                'label' => $this->periodLabel($value),
            ];

            $cursor = $cursor->subMonthNoOverflow();
        }

        if (! in_array($selectedMonth, array_column($months, 'value'), true)) {
            array_unshift($months, [
                'value' => $selectedMonth,
                'label' => $this->periodLabel($selectedMonth),
            ]);
        }

        return $months;
    }

    /**
     * Label rentang satu bulan, mis. "01 Oktober – 31 Oktober 2026".
     */
    private function periodLabel(string $month): string
    {
        $date = Carbon::createFromFormat('Y-m', $month)->startOfMonth();

        return sprintf(
            '%02d %s – %02d %s %d',
            $date->day,
            self::MONTH_NAMES[$date->month],
            $date->daysInMonth,
            self::MONTH_NAMES[$date->month],
            $date->year,
        );
    }

    /**
     * Label bulan dalam bahasa Indonesia, mis. "Oktober 2026".
     */
    private function monthLabel(string $month): string
    {
        $date = Carbon::createFromFormat('Y-m', $month)->startOfMonth();

        return self::MONTH_NAMES[$date->month].' '.$date->year;
    }
}
