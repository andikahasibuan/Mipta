<?php

namespace App\Services;

use App\Enums\FieldCondition;
use App\Models\FieldDistributionReport;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class UptdReportHistoryService
{
    /**
     * Ambil riwayat laporan kondisi lapangan milik kontributor sesuai filter.
     *
     * @return array{
     *     reports: LengthAwarePaginator,
     *     filters: array{q: string, month: string, condition: string|null},
     *     conditions: array<int, FieldCondition>
     * }
     */
    public function paginate(Request $request, User $contributor): array
    {
        $search = trim($request->string('q')->toString());
        $month = $request->string('month')->toString();

        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            $month = now()->format('Y-m');
        }

        $condition = FieldCondition::tryFrom($request->string('condition')->toString());

        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();

        $reports = FieldDistributionReport::query()
            ->where('uptd_user_id', $contributor->id)
            ->with(['market:id,name', 'contributor:id,name'])
            ->when($search !== '', fn ($query) => $query->where('description', 'like', "%{$search}%"))
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
                'q' => $search,
                'month' => $month,
                'condition' => $condition?->value,
            ],
            'conditions' => FieldCondition::cases(),
        ];
    }
}
