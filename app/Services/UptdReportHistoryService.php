<?php

namespace App\Services;

use App\Enums\FieldCondition;
use App\Models\FieldDistributionReport;
use App\Models\UptdContributor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class UptdReportHistoryService
{
    /**
     * Ambil riwayat laporan pengawasan milik kontributor sesuai filter.
     *
     * @return array{
     *     reports: LengthAwarePaginator,
     *     filters: array{month: string, condition: string|null},
     *     conditions: array<int, FieldCondition>
     * }
     */
    public function paginate(Request $request, UptdContributor $contributor): array
    {
        $month = $request->string('month')->toString();

        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            $month = now()->format('Y-m');
        }

        $condition = FieldCondition::tryFrom($request->string('condition')->toString());

        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();

        $reports = FieldDistributionReport::query()
            ->where('uptd_contributor_id', $contributor->id)
            ->with(['market:id,name', 'contributor:id,name'])
            ->when($condition, fn ($query, FieldCondition $condition) => $query->where('condition', $condition->value))
            ->whereBetween('report_date', [
                $start->toDateString(),
                $start->copy()->endOfMonth()->toDateString(),
            ])
            ->orderByDesc('report_date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return [
            'reports' => $reports,
            'filters' => [
                'month' => $month,
                'condition' => $condition?->value,
            ],
            'conditions' => FieldCondition::cases(),
        ];
    }
}
