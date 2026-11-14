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
     *     reports: LengthAwarePaginator
     * }
     */
    public function build(Request $request, Distributor $distributor): array
    {
        $month = $request->string('month')->toString();

        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            $month = now()->format('Y-m');
        }

        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $periodStart = $start->toDateString();
        $periodEnd = $start->copy()->endOfMonth()->toDateString();

        $submittedQuery = fn () => Distribution::query()
            ->where('distributor_id', $distributor->id)
            ->where('status', DistributionStatus::Submitted->value)
            ->whereBetween('distribution_date', [$periodStart, $periodEnd]);

        $reports = $submittedQuery()->count();
        $cartons = (int) $submittedQuery()->sum('cartons');

        $paginatedReports = $submittedQuery()
            ->with(['market:id,name', 'store:id,name'])
            ->orderByDesc('distribution_date')
            ->orderByDesc('id')
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
}
