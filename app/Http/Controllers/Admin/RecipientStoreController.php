<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Market;
use App\Models\RecipientStore;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class RecipientStoreController extends Controller
{
    /**
     * Tampilkan daftar toko penerima.
     */
    public function index(Request $request): View
    {
        $search = trim($request->string('q')->toString());
        $status = $request->string('status')->toString();
        $status = in_array($status, ['aktif', 'nonaktif'], true) ? $status : null;

        $marketId = $request->integer('market');
        $marketId = $marketId > 0 && Market::whereKey($marketId)->exists() ? $marketId : null;

        $stores = RecipientStore::with('market:id,name')
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")))
            ->when($status === 'aktif', fn ($query) => $query->where('is_active', true))
            ->when($status === 'nonaktif', fn ($query) => $query->where('is_active', false))
            ->when($marketId, fn ($query, int $id) => $query->where('market_id', $id))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.recipient-stores.index', [
            'stores' => $stores,
            'markets' => Market::orderBy('name')->get(['id', 'name']),
            'filters' => [
                'q' => $search,
                'market' => $marketId,
                'status' => $status,
            ],
        ]);
    }
}
