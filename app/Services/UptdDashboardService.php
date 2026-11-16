<?php

namespace App\Services;

use App\Models\FieldDistributionReport;
use App\Models\RecipientStore;
use App\Models\UptdContributor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UptdDashboardService
{
    /**
     * Bangun data dashboard untuk kontributor UPTD Pasar yang sedang login.
     *
     * @return array{
     *     contributor: UptdContributor,
     *     summary: array{reports: int, stores: int},
     *     reports: LengthAwarePaginator
     * }
     */
    public function build(UptdContributor $contributor): array
    {
        $contributor->loadMissing('market');

        $marketId = (int) $contributor->market_id;

        $reportQuery = fn () => FieldDistributionReport::query()
            ->where('market_id', $marketId)
            ->where('uptd_contributor_id', $contributor->id);

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
                'stores' => RecipientStore::query()
                    ->where('market_id', $marketId)
                    ->where('is_active', true)
                    ->count(),
            ],
            'reports' => $reports,
        ];
    }
}
