<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Market;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class MarketController extends Controller
{
    /**
     * Tampilkan daftar pasar.
     */
    public function index(Request $request): View
    {
        $search = trim($request->string('q')->toString());
        $status = $request->string('status')->toString();
        $status = in_array($status, ['aktif', 'nonaktif'], true) ? $status : null;

        $markets = Market::query()
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")))
            ->when($status === 'aktif', fn ($query) => $query->where('is_active', true))
            ->when($status === 'nonaktif', fn ($query) => $query->where('is_active', false))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.markets.index', [
            'markets' => $markets,
            'filters' => [
                'q' => $search,
                'status' => $status,
            ],
        ]);
    }
}
