<?php

namespace App\Http\Controllers\Admin;

use App\Enums\FieldCondition;
use App\Http\Controllers\Controller;
use App\Models\FieldDistributionReport;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SupervisionController extends Controller
{
    /**
     * Tampilkan halaman pengawasan dan tindak lanjut.
     */
    public function index(Request $request): View
    {
        $search = trim($request->string('q')->toString());
        $month = $request->string('month')->toString();

        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            $month = now()->format('Y-m');
        }

        $condition = FieldCondition::tryFrom($request->string('condition')->toString());

        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();

        $reports = FieldDistributionReport::query()
            ->with(['market:id,name', 'contributor:id,name'])
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->whereHas('contributor', fn ($query) => $query->where('name', 'like', "%{$search}%"))
                ->orWhereHas('market', fn ($query) => $query->where('name', 'like', "%{$search}%"))))
            ->when($condition, fn ($query, FieldCondition $condition) => $query->where('condition', $condition->value))
            ->whereBetween('report_date', [
                $start->toDateString(),
                $start->copy()->endOfMonth()->toDateString(),
            ])
            ->orderByDesc('report_date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.supervision.index', [
            'reports' => $reports,
            'filters' => [
                'q' => $search,
                'month' => $month,
                'condition' => $condition?->value,
            ],
            'conditions' => FieldCondition::cases(),
        ]);
    }
}
