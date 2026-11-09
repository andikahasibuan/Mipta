<?php

namespace App\Services;

use App\Enums\DistributionStatus;
use App\Models\Distribution;
use App\Models\Distributor;
use App\Models\Market;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DistributionReportService
{
    /**
     * Ambil daftar laporan penyaluran sesuai filter yang dipilih.
     *
     * @return array{
     *     distributions: LengthAwarePaginator,
     *     filters: array{month: string, distributor: int|null, market: int|null},
     *     distributors: Collection<int, Distributor>,
     *     markets: Collection<int, Market>
     * }
     */
    public function paginate(Request $request): array
    {
        $month = $request->string('month')->toString();

        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            $month = now()->format('Y-m');
        }

        $distributorId = $this->validId($request->string('distributor')->toString(), Distributor::class);
        $marketId = $this->validId($request->string('market')->toString(), Market::class);

        $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();

        $distributions = Distribution::query()
            ->with(['distributor:id,name', 'market:id,name', 'store:id,name,market_id'])
            ->where('status', DistributionStatus::Submitted->value)
            ->whereBetween('distribution_date', [
                $start->toDateString(),
                $start->copy()->endOfMonth()->toDateString(),
            ])
            ->when($distributorId, fn ($query, int $id) => $query->where('distributor_id', $id))
            ->when($marketId, fn ($query, int $id) => $query->where('market_id', $id))
            ->orderByDesc('distribution_date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return [
            'distributions' => $distributions,
            'filters' => [
                'month' => $month,
                'distributor' => $distributorId,
                'market' => $marketId,
            ],
            'distributors' => Distributor::orderBy('name')->get(['id', 'name']),
            'markets' => Market::orderBy('name')->get(['id', 'name']),
        ];
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
