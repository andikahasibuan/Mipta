<?php

namespace App\Services;

use App\Enums\DistributionStatus;
use App\Enums\FieldCondition;
use App\Models\Distribution;
use App\Models\FieldDistributionReport;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UptdDashboardService
{
    /**
     * Bangun data dashboard untuk kontributor UPTD Pasar yang sedang login.
     *
     * @return array{
     *     contributor: User,
     *     summary: array{reports: int, normal_reports: int, active_distributors: int},
     *     reports: LengthAwarePaginator
     * }
     */
    public function build(User $contributor): array
    {
        $contributor->loadMissing('market');

        $marketId = (int) $contributor->market_id;

        $reportQuery = fn () => FieldDistributionReport::query()
            ->where('market_id', $marketId)
            ->where('uptd_user_id', $contributor->id);

        $activeDistributors = Distribution::query()
            ->where('market_id', $marketId)
            ->where('status', DistributionStatus::Submitted->value)
            ->distinct()
            ->count('distributor_id');

        $reports = $reportQuery()
            ->with('contributor:id,name')
            ->orderByDesc('report_date')
            ->orderByDesc('id')
            ->paginate(5)
            ->withQueryString();

        return [
            'contributor' => $contributor,
            'summary' => [
                'reports' => $reportQuery()->count(),
                'normal_reports' => $reportQuery()
                    ->where('condition', FieldCondition::Normal->value)
                    ->count(),
                'active_distributors' => $activeDistributors,
            ],
            'reports' => $reports,
        ];
    }
}
