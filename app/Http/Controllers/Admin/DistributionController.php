<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Distribution;
use App\Models\Distributor;
use App\Models\Market;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DistributionController extends Controller
{
    /**
     * Tampilkan halaman laporan penyaluran.
     */
    public function index(Request $request): View
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

        return view('admin.distribution.index', [
            'distributions' => $distributions,
            'filters' => [
                'month' => $month,
                'distributor' => $distributorId,
                'market' => $marketId,
            ],
            'distributors' => Distributor::orderBy('name')->get(['id', 'name']),
            'markets' => Market::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Hapus data penyaluran.
     */
    public function destroy(Distribution $distribution): RedirectResponse
    {
        $distribution->delete();

        return redirect()->route('admin.distribution.index')->with('success', 'Data penyaluran berhasil dihapus.');
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
