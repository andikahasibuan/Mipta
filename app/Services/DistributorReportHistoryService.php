<?php

namespace App\Services;

use App\Enums\DistributionStatus;
use App\Models\Distribution;
use App\Models\Distributor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DistributorReportHistoryService
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
     * Bangun daftar riwayat laporan penyaluran milik distributor.
     *
     * @return array{
     *     reports: LengthAwarePaginator,
     *     filters: array{month: string, status: string, search: string},
     *     months: array<int, array{value: string, label: string}>
     * }
     */
    public function build(Request $request, Distributor $distributor): array
    {
        $month = $this->validMonth($request->string('month')->toString());
        $status = $this->validStatus($request->string('status')->toString());
        $search = trim($request->string('search')->toString());

        $reports = Distribution::query()
            ->where('distributor_id', $distributor->id)
            ->with(['market:id,name', 'store:id,name'])
            ->when($month !== 'all', function ($query) use ($month): void {
                $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();

                $query->whereBetween('distribution_date', [
                    $start->toDateString(),
                    $start->copy()->endOfMonth()->toDateString(),
                ]);
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($search !== '', fn ($query) => $query->whereHas(
                'store',
                fn ($storeQuery) => $storeQuery->where('name', 'like', '%'.$search.'%'),
            ))
            ->orderByDesc('distribution_date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return [
            'reports' => $reports,
            'filters' => [
                'month' => $month,
                'status' => $status,
                'search' => $search,
            ],
            'months' => $this->monthOptions($month),
        ];
    }

    /**
     * Bulan yang valid atau "all" untuk semua bulan.
     */
    private function validMonth(string $month): string
    {
        if ($month === 'all') {
            return $month;
        }

        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            return now()->format('Y-m');
        }

        return $month;
    }

    /**
     * Status laporan yang valid, atau string kosong untuk semua status.
     */
    private function validStatus(string $status): string
    {
        return in_array($status, [DistributionStatus::Submitted->value, DistributionStatus::Draft->value], true)
            ? $status
            : '';
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    private function monthOptions(string $selectedMonth): array
    {
        $months = [
            ['value' => 'all', 'label' => 'Semua Bulan'],
        ];

        $cursor = Carbon::now()->startOfMonth();

        for ($index = 0; $index < 12; $index++) {
            $value = $cursor->format('Y-m');

            $months[] = [
                'value' => $value,
                'label' => $this->monthLabel($value),
            ];

            $cursor = $cursor->subMonthNoOverflow();
        }

        if ($selectedMonth !== 'all' && ! in_array($selectedMonth, array_column($months, 'value'), true)) {
            array_splice($months, 1, 0, [[
                'value' => $selectedMonth,
                'label' => $this->monthLabel($selectedMonth),
            ]]);
        }

        return $months;
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
