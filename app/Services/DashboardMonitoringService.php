<?php

namespace App\Services;

use App\Models\Distribution;
use App\Models\Distributor;
use App\Models\Market;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardMonitoringService
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
     * Bangun data dashboard monitoring untuk periode yang dipilih.
     *
     * @return array{
     *     summary: array<string, mixed>,
     *     chart: array{labels: array<int, string>, values: array<int, int>},
     *     breakdown: array<int, array{name: string, cartons: int}>,
     *     filters: array{month: string, market: int|null, distributor: int|null},
     *     months: array<int, array{value: string, label: string}>,
     *     markets: Collection<int, Market>,
     *     distributors: Collection<int, Distributor>
     * }
     */
    public function build(Request $request): array
    {
        $month = $request->string('month')->toString();

        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            $month = now()->format('Y-m');
        }

        $distributorId = $this->validId($request->string('distributor')->toString(), Distributor::class);
        $marketId = $this->validId($request->string('market')->toString(), Market::class);

        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $days = $start->daysInMonth;
        $periodStart = $start->toDateString();
        $periodEnd = $start->copy()->endOfMonth()->toDateString();

        $totalDistributors = Distributor::query()
            ->when($distributorId, fn ($query, int $id) => $query->whereKey($id))
            ->count();

        $activeDistributors = Distributor::query()
            ->when($distributorId, fn ($query, int $id) => $query->whereKey($id))
            ->where('is_active', true)
            ->count();

        $totalMarkets = Market::count();

        $monitoredMarkets = Market::query()
            ->whereHas('monitorings', fn ($query) => $query->whereBetween('monitored_at', [$periodStart, $periodEnd]))
            ->count();

        $distributions = Distribution::query()
            ->when($distributorId, fn ($query, int $id) => $query->where('distributor_id', $id))
            ->when($marketId, fn ($query, int $id) => $query->where('market_id', $id))
            ->whereBetween('distribution_date', [$periodStart, $periodEnd])
            ->get(['distributor_id', 'distribution_date', 'cartons']);

        $dailyCartons = array_fill(0, $days, 0);

        foreach ($distributions as $distribution) {
            $dailyCartons[$distribution->distribution_date->day - 1] += $distribution->cartons;
        }

        $cartonsByDistributor = $distributions->groupBy('distributor_id')->map->sum('cartons');
        $totalReports = $distributions->count();
        $totalCartons = $distributions->sum('cartons');

        $previousStart = $start->copy()->subMonthNoOverflow()->startOfMonth();
        $previousReports = Distribution::query()
            ->when($distributorId, fn ($query, int $id) => $query->where('distributor_id', $id))
            ->when($marketId, fn ($query, int $id) => $query->where('market_id', $id))
            ->whereBetween('distribution_date', [
                $previousStart->toDateString(),
                $previousStart->copy()->endOfMonth()->toDateString(),
            ])
            ->count();

        $reportsChange = null;
        $reportsChangeDirection = 'none';

        if ($previousReports > 0) {
            $reportsChange = round((($totalReports - $previousReports) / $previousReports) * 100, 1);
            $reportsChangeDirection = match (true) {
                $reportsChange > 0 => 'up',
                $reportsChange < 0 => 'down',
                default => 'none',
            };
        } elseif ($totalReports > 0) {
            $reportsChangeDirection = 'up';
        }

        $breakdown = Distributor::query()
            ->when($distributorId, fn ($query, int $id) => $query->whereKey($id))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Distributor $distributor): array => [
                'name' => $distributor->name,
                'cartons' => (int) ($cartonsByDistributor[$distributor->id] ?? 0),
            ])
            ->all();

        return [
            'summary' => [
                'month' => $month,
                'month_label' => $this->monthLabel($month),
                'cartons' => $totalCartons,
                'reports' => $totalReports,
                'reports_change' => $reportsChange,
                'reports_change_direction' => $reportsChangeDirection,
                'active_distributors' => $activeDistributors,
                'total_distributors' => $totalDistributors,
                'monitored_markets' => $monitoredMarkets,
                'total_markets' => $totalMarkets,
                'coverage_percent' => $totalMarkets > 0
                    ? (int) round(($monitoredMarkets / $totalMarkets) * 100)
                    : 0,
            ],
            'chart' => [
                'labels' => $this->dayLabels($month),
                'values' => $dailyCartons,
            ],
            'breakdown' => $breakdown,
            'filters' => [
                'month' => $month,
                'market' => $marketId,
                'distributor' => $distributorId,
            ],
            'months' => $this->monthOptions($month),
            'markets' => Market::orderBy('name')->get(['id', 'name']),
            'distributors' => Distributor::orderBy('name')->get(['id', 'name']),
        ];
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

    /**
     * Label tanggal ringkas untuk sumbu grafik, mis. "1 Okt".
     *
     * @return array<int, string>
     */
    private function dayLabels(string $month): array
    {
        $date = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $monthShort = mb_substr(self::MONTH_NAMES[$date->month], 0, 3);

        return array_map(
            fn (int $day): string => $day.' '.$monthShort,
            range(1, $date->daysInMonth),
        );
    }
}
