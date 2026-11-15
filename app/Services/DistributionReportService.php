<?php

namespace App\Services;

use App\Enums\DistributionStatus;
use App\Models\Distribution;
use App\Models\Distributor;
use App\Models\Market;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DistributionReportService
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
     * Ambil daftar laporan penyaluran sesuai filter yang dipilih.
     *
     * @return array{
     *     distributions: LengthAwarePaginator,
     *     summary: array{reports: int, cartons: int, recipient_stores: int},
     *     month_label: string,
     *     filters: array{month: string, search: string, distributor: int|null, market: int|null},
     *     months: array<int, array{value: string, label: string}>,
     *     distributors: Collection<int, Distributor>,
     *     markets: Collection<int, Market>
     * }
     */
    public function paginate(Request $request): array
    {
        $month = $this->validMonth($request->string('month')->toString());
        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();

        $search = trim($request->string('search')->toString());
        $distributorId = $this->validId($request->string('distributor')->toString(), Distributor::class);
        $marketId = $this->validId($request->string('market')->toString(), Market::class);

        $baseQuery = Distribution::query()
            ->where('status', DistributionStatus::Submitted->value)
            ->whereBetween('distribution_date', [
                $start->toDateString(),
                $start->copy()->endOfMonth()->toDateString(),
            ])
            ->when($distributorId, fn ($query, int $id) => $query->where('distributor_id', $id))
            ->when($marketId, fn ($query, int $id) => $query->where('market_id', $id))
            ->when($search !== '', fn ($query) => $this->search($query, $search));

        $summary = [
            'reports' => (clone $baseQuery)->count(),
            'cartons' => (int) (clone $baseQuery)->sum('cartons'),
            'recipient_stores' => (clone $baseQuery)
                ->whereNotNull('recipient_store_id')
                ->distinct()
                ->count('recipient_store_id'),
        ];

        $distributions = $baseQuery
            ->with(['distributor:id,name', 'market:id,name', 'store:id,name,market_id'])
            ->orderByDesc('distribution_date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return [
            'distributions' => $distributions,
            'summary' => $summary,
            'month_label' => $this->monthLabel($month),
            'filters' => [
                'month' => $month,
                'search' => $search,
                'distributor' => $distributorId,
                'market' => $marketId,
            ],
            'months' => $this->monthOptions($month),
            'distributors' => Distributor::orderBy('name')->get(['id', 'name']),
            'markets' => Market::orderBy('name')->get(['id', 'name']),
        ];
    }

    /**
     * Batasi hasil berdasarkan kode laporan atau nama toko, distributor, dan pasar.
     *
     * @param  Builder<Distribution>  $query
     */
    private function search(Builder $query, string $search): void
    {
        $query->where(function (Builder $query) use ($search): void {
            $query->whereHas('store', fn (Builder $storeQuery) => $storeQuery->where('name', 'like', "%{$search}%"))
                ->orWhereHas('distributor', fn (Builder $distributorQuery) => $distributorQuery->where('name', 'like', "%{$search}%"))
                ->orWhereHas('market', fn (Builder $marketQuery) => $marketQuery->where('name', 'like', "%{$search}%"));

            if (preg_match('/^(?:lp-?)?0*(\d+)$/i', $search, $matches)) {
                $query->orWhere('id', (int) $matches[1]);
            }
        });
    }

    /**
     * Bulan valid berformat Y-m atau bulan berjalan.
     */
    private function validMonth(string $month): string
    {
        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            return now()->format('Y-m');
        }

        return $month;
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

    /**
     * Label bulan dalam bahasa Indonesia, mis. "November 2026".
     */
    private function monthLabel(string $month): string
    {
        $date = Carbon::createFromFormat('Y-m', $month)->startOfMonth();

        return self::MONTH_NAMES[$date->month].' '.$date->year;
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
}
