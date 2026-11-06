<?php

namespace App\Http\Controllers\Admin;

use App\Enums\FieldCondition;
use App\Http\Controllers\Controller;
use App\Models\FieldDistributionReport;
use App\Models\Market;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
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

        $marketId = $this->validId($request->string('market')->toString(), Market::class);
        $condition = FieldCondition::tryFrom($request->string('condition')->toString());

        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();

        $reports = FieldDistributionReport::query()
            ->with(['market:id,name', 'contributor:id,name'])
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->whereHas('contributor', fn ($query) => $query->where('name', 'like', "%{$search}%"))
                ->orWhereHas('market', fn ($query) => $query->where('name', 'like', "%{$search}%"))))
            ->when($condition, fn ($query, FieldCondition $condition) => $query->where('condition', $condition->value))
            ->when($marketId, fn ($query, int $id) => $query->where('market_id', $id))
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
                'market' => $marketId,
            ],
            'markets' => Market::orderBy('name')->get(['id', 'name']),
            'conditions' => FieldCondition::cases(),
        ]);
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
