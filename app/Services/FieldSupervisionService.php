<?php

namespace App\Services;

use App\Enums\FieldCondition;
use App\Models\FieldDistributionReport;
use App\Models\Market;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;

class FieldSupervisionService
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
     * Ambil daftar laporan pengawasan sesuai filter yang dipilih.
     *
     * @return array{
     *     reports: LengthAwarePaginator,
     *     summary: array{reports: int, reported_markets: int, total_markets: int, needs_attention_markets: int},
     *     month_label: string,
     *     filters: array{q: string, month: string, condition: string|null, highlight: int|null},
     *     conditions: array<int, FieldCondition>
     * }
     */
    public function paginate(Request $request): array
    {
        $search = trim($request->string('q')->toString());
        $month = $request->string('month')->toString();

        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            $month = now()->format('Y-m');
        }

        $condition = FieldCondition::tryFrom($request->string('condition')->toString());
        $highlightId = $request->integer('highlight');
        $highlightId = $highlightId > 0 && FieldDistributionReport::whereKey($highlightId)->exists()
            ? $highlightId
            : null;

        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $periodStart = $start->toDateString();
        $periodEnd = $start->copy()->endOfMonth()->toDateString();

        $baseQuery = FieldDistributionReport::query()
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->whereHas('contributor', fn ($query) => $query->where('name', 'like', "%{$search}%"))
                ->orWhereHas('market', fn ($query) => $query->where('name', 'like', "%{$search}%"))))
            ->when($condition, fn ($query, FieldCondition $condition) => $query->where('condition', $condition->value))
            ->whereBetween('report_date', [$periodStart, $periodEnd]);

        $reports = $baseQuery
            ->with(['market:id,name', 'contributor:id,name'])
            ->orderByDesc('report_date')
            ->orderByDesc('id')
            ->paginate(10, ['*'], 'page', $this->highlightPage($baseQuery, $highlightId))
            ->withQueryString();

        return [
            'reports' => $reports,
            'summary' => [
                'reports' => (clone $baseQuery)->count(),
                'reported_markets' => (clone $baseQuery)->distinct()->count('market_id'),
                'total_markets' => Market::count(),
                'needs_attention_markets' => $this->needsAttentionMarkets($periodStart, $periodEnd),
            ],
            'month_label' => $this->monthLabel($month),
            'filters' => [
                'q' => $search,
                'month' => $month,
                'condition' => $condition?->value,
                'highlight' => $highlightId,
            ],
            'conditions' => FieldCondition::cases(),
        ];
    }

    /**
     * Ambil seluruh data laporan pengawasan untuk keperluan ekspor (tanpa paginasi).
     *
     * @return array{
     *     reports: Collection<int, FieldDistributionReport>,
     *     summary: array{reports: int, reported_markets: int, total_markets: int, needs_attention_markets: int},
     *     month_label: string
     * }
     */
    public function export(Request $request): array
    {
        $search = trim($request->string('q')->toString());
        $month = $request->string('month')->toString();

        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            $month = now()->format('Y-m');
        }

        $condition = FieldCondition::tryFrom($request->string('condition')->toString());

        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $periodStart = $start->toDateString();
        $periodEnd = $start->copy()->endOfMonth()->toDateString();

        $baseQuery = FieldDistributionReport::query()
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->whereHas('contributor', fn ($query) => $query->where('name', 'like', "%{$search}%"))
                ->orWhereHas('market', fn ($query) => $query->where('name', 'like', "%{$search}%"))))
            ->when($condition, fn ($query, FieldCondition $condition) => $query->where('condition', $condition->value))
            ->whereBetween('report_date', [$periodStart, $periodEnd]);

        $summary = [
            'reports' => (clone $baseQuery)->count(),
            'reported_markets' => (clone $baseQuery)->distinct()->count('market_id'),
            'total_markets' => Market::count(),
            'needs_attention_markets' => $this->needsAttentionMarkets($periodStart, $periodEnd),
        ];

        $reports = $baseQuery
            ->with(['market:id,name', 'contributor:id,name'])
            ->orderByDesc('report_date')
            ->orderByDesc('id')
            ->get();

        return [
            'reports' => $reports,
            'summary' => $summary,
            'month_label' => $this->monthLabel($month),
        ];
    }

    /**
     * Tentukan halaman yang memuat baris yang perlu di-highlight.
     *
     * @param  Builder<FieldDistributionReport>  $baseQuery
     */
    private function highlightPage(Builder $baseQuery, ?int $highlightId): int
    {
        if ($highlightId === null) {
            return Paginator::resolveCurrentPage();
        }

        $highlighted = (clone $baseQuery)->whereKey($highlightId)->first();

        if ($highlighted === null) {
            return Paginator::resolveCurrentPage();
        }

        $before = (clone $baseQuery)
            ->where(function (Builder $query) use ($highlighted): void {
                $query->where('report_date', '>', $highlighted->report_date->toDateString())
                    ->orWhere(function (Builder $query) use ($highlighted): void {
                        $query->where('report_date', $highlighted->report_date->toDateString())
                            ->where('id', '>', $highlighted->id);
                    });
            })
            ->count();

        return intdiv($before, 10) + 1;
    }

    /**
     * Jumlah pasar dengan laporan terbaru pada periode berstatus perlu perhatian.
     */
    private function needsAttentionMarkets(string $periodStart, string $periodEnd): int
    {
        $latestReports = FieldDistributionReport::query()
            ->whereBetween('report_date', [$periodStart, $periodEnd])
            ->orderByDesc('report_date')
            ->orderByDesc('id')
            ->get(['market_id', 'condition'])
            ->unique('market_id');

        return $latestReports
            ->filter(fn (FieldDistributionReport $report): bool => $report->condition === FieldCondition::PerluPerhatian)
            ->count();
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
