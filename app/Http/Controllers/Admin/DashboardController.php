<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Distributor;
use App\Models\Market;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
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
     * Tampilkan dashboard monitoring admin.
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
        $days = $start->daysInMonth;

        $totalDistributors = Distributor::query()
            ->when($distributorId, fn ($query, int $id) => $query->whereKey($id))
            ->count();

        $activeDistributors = Distributor::query()
            ->when($distributorId, fn ($query, int $id) => $query->whereKey($id))
            ->where('is_active', true)
            ->count();

        $totalMarkets = Market::query()
            ->when($marketId, fn ($query, int $id) => $query->whereKey($id))
            ->count();

        $monitoredMarkets = Market::query()
            ->when($marketId, fn ($query, int $id) => $query->whereKey($id))
            ->where('is_active', true)
            ->count();

        $breakdown = Distributor::query()
            ->when($distributorId, fn ($query, int $id) => $query->whereKey($id))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Distributor $distributor): array => [
                'name' => $distributor->name,
                'cartons' => 0,
            ])
            ->all();

        return view('admin.dashboard', [
            'summary' => [
                'month' => $month,
                'month_label' => $this->monthLabel($month),
                'cartons' => 0,
                'reports' => 0,
                'reports_change' => null,
                'reports_change_direction' => 'none',
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
                'values' => array_fill(0, $days, 0),
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
