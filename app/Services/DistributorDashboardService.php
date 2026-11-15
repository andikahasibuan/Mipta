<?php

namespace App\Services;

use App\Enums\DistributionStatus;
use App\Models\Distribution;
use App\Models\Distributor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DistributorDashboardService
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
     * Bangun data dashboard monitoring untuk distributor yang sedang login.
     *
     * @return array{
     *     distributor: Distributor,
     *     summary: array{month: string, month_label: string, reports: int, cartons: int},
     *     filters: array{month: string, search: string},
     *     months: array<int, array{value: string, label: string}>,
     *     reports: LengthAwarePaginator
     * }
     */
    public function build(Request $request, Distributor $distributor): array
    {
        $month = $request->string('month')->toString();

        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            $month = now()->format('Y-m');
        }

        $search = trim($request->string('q')->toString());

        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $periodStart = $start->toDateString();
        $periodEnd = $start->copy()->endOfMonth()->toDateString();

        $baseQuery = Distribution::query()
            ->where('distributor_id', $distributor->id)
            ->where('status', DistributionStatus::Submitted->value)
            ->whereBetween('distribution_date', [$periodStart, $periodEnd])
            ->when($search !== '', fn ($query) => $query->whereHas(
                'store',
                fn ($storeQuery) => $storeQuery->where('name', 'like', "%{$search}%"),
            ));

        $reports = (clone $baseQuery)->count();
        $cartons = (int) (clone $baseQuery)->sum('cartons');

        $paginatedReports = (clone $baseQuery)
            ->with(['market:id,name', 'store:id,name'])
            ->orderBy('id')
            ->paginate(5)
            ->withQueryString();

        return [
            'distributor' => $distributor,
            'summary' => [
                'month' => $month,
                'month_label' => $this->monthLabel($month),
                'reports' => $reports,
                'cartons' => $cartons,
            ],
            'filters' => [
                'month' => $month,
                'search' => $search,
            ],
            'months' => $this->monthOptions($month),
            'reports' => $paginatedReports,
        ];
    }

    /**
     * Label bulan dalam bahasa Indonesia, mis. "November 2026".
     */
    private function monthLabel(string $month): string
    {
        $date = Carbon::createFromFormat('Y-m', $month)->startOfMonth();

        return self::MONTH_NAMES[$date->month].' '.$date->year;
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    private function monthOptions(string $selectedMonth): array
    {
        $months = [];
        $cursor = Carbon::now()->startOfMonth();

        for ($index = 0; $index < 12; $index++) {
            $value = $cursor->format('Y-m');

            $months[] = [
                'value' => $value,
                'label' => $this->monthLabel($value),
            ];

            $cursor = $cursor->subMonthNoOverflow();
        }

        if (! in_array($selectedMonth, array_column($months, 'value'), true)) {
            array_unshift($months, [
                'value' => $selectedMonth,
                'label' => $this->monthLabel($selectedMonth),
            ]);
        }

        return $months;
    }
}
