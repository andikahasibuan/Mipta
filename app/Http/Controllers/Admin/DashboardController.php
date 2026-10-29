<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardSampleData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardSampleData $sampleData) {}

    /**
     * Tampilkan dashboard monitoring admin.
     */
    public function index(Request $request): View
    {
        $month = $request->string('month')->toString();

        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            $month = now()->format('Y-m');
        }

        $market = $this->validKey($request->string('market')->toString(), DashboardSampleData::MARKETS);
        $distributor = $this->validKey($request->string('distributor')->toString(), DashboardSampleData::DISTRIBUTORS);

        $months = $this->sampleData->months();

        if (! in_array($month, array_column($months, 'value'), true)) {
            array_unshift($months, [
                'value' => $month,
                'label' => $this->sampleData->periodLabel($month),
            ]);
        }

        return view('admin.dashboard', [
            'summary' => $this->sampleData->summary($month, $market, $distributor),
            'chart' => [
                'labels' => $this->sampleData->dayLabels($month),
                'values' => $this->sampleData->dailySeries($month, $market, $distributor),
            ],
            'breakdown' => $this->sampleData->distributorBreakdown($month, $market, $distributor),
            'filters' => [
                'month' => $month,
                'market' => $market,
                'distributor' => $distributor,
            ],
            'months' => $months,
            'markets' => DashboardSampleData::MARKETS,
            'distributors' => DashboardSampleData::DISTRIBUTORS,
        ]);
    }

    /**
     * @param  array<string, string>  $options
     */
    private function validKey(string $value, array $options): ?string
    {
        return array_key_exists($value, $options) ? $value : null;
    }
}
