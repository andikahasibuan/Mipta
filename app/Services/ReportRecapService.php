<?php

namespace App\Services;

use App\Enums\DistributionStatus;
use App\Enums\FieldCondition;
use App\Models\Distribution;
use App\Models\FieldDistributionReport;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ReportRecapService
{
    /**
     * @var array<int, string>
     */
    private const TABS = ['penyaluran', 'kondisi'];

    /**
     * Bangun data rekap laporan untuk periode yang dipilih.
     *
     * @return array{
     *     summary: array<string, int>,
     *     activeTab: string,
     *     distributionRecap: Collection<int, Distribution>|null,
     *     conditionReports: LengthAwarePaginator<int, FieldDistributionReport>|null,
     *     filters: array{start_date: string, end_date: string}
     * }
     */
    public function build(Request $request): array
    {
        [$startDate, $endDate] = $this->period($request);

        $activeTab = $request->string('tab')->toString();

        if (! in_array($activeTab, self::TABS, true)) {
            $activeTab = 'penyaluran';
        }

        $summary = [
            'distribution_reports' => Distribution::query()
                ->where('status', DistributionStatus::Submitted->value)
                ->whereBetween('distribution_date', [$startDate, $endDate])
                ->count(),
            'distribution_cartons' => (int) Distribution::query()
                ->where('status', DistributionStatus::Submitted->value)
                ->whereBetween('distribution_date', [$startDate, $endDate])
                ->sum('cartons'),
            'condition_reports' => FieldDistributionReport::query()
                ->whereBetween('report_date', [$startDate, $endDate])
                ->count(),
            'needs_attention' => FieldDistributionReport::query()
                ->whereBetween('report_date', [$startDate, $endDate])
                ->where('condition', FieldCondition::PerluPerhatian->value)
                ->count(),
        ];

        $distributionRecap = null;
        $conditionReports = null;

        if ($activeTab === 'penyaluran') {
            $distributionRecap = Distribution::query()
                ->selectRaw('market_id, COUNT(*) as reports_count, SUM(cartons) as cartons_sum, COUNT(DISTINCT distributor_id) as distributors_count')
                ->where('status', DistributionStatus::Submitted->value)
                ->whereBetween('distribution_date', [$startDate, $endDate])
                ->groupBy('market_id')
                ->with('market:id,name')
                ->orderByDesc('cartons_sum')
                ->get();
        } else {
            $conditionReports = FieldDistributionReport::query()
                ->with(['market:id,name', 'contributor:id,name'])
                ->whereBetween('report_date', [$startDate, $endDate])
                ->orderByDesc('report_date')
                ->orderByDesc('id')
                ->paginate(5, ['*'], 'kondisi_page')
                ->withQueryString();
        }

        return [
            'summary' => $summary,
            'activeTab' => $activeTab,
            'distributionRecap' => $distributionRecap,
            'conditionReports' => $conditionReports,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ];
    }

    /**
     * Ambil rentang periode dari request dengan default bulan berjalan.
     *
     * @return array{0: string, 1: string}
     */
    private function period(Request $request): array
    {
        $start = $this->validDate($request->string('start_date')->toString())
            ?? now()->startOfMonth()->toDateString();
        $end = $this->validDate($request->string('end_date')->toString())
            ?? now()->endOfMonth()->toDateString();

        if ($start > $end) {
            [$start, $end] = [$end, $start];
        }

        return [$start, $end];
    }

    /**
     * Validasi tanggal berformat Y-m-d.
     */
    private function validDate(string $value): ?string
    {
        if (! preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $matches)) {
            return null;
        }

        if (! checkdate((int) $matches[2], (int) $matches[3], (int) $matches[1])) {
            return null;
        }

        return $value;
    }
}
