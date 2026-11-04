<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMarketRequest;
use App\Http\Requests\Admin\UpdateMarketRequest;
use App\Models\Market;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
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

    /**
     * Tampilkan form tambah pasar.
     */
    public function create(): View
    {
        return view('admin.markets.form', [
            'market' => null,
        ]);
    }

    /**
     * Simpan pasar baru.
     */
    public function store(StoreMarketRequest $request): RedirectResponse
    {
        $market = Market::create($request->validated());

        return redirect()
            ->route('admin.markets.index')
            ->with('status', 'Pasar '.$market->name.' berhasil ditambahkan.');
    }

    /**
     * Tampilkan form ubah pasar.
     */
    public function edit(Market $market): View
    {
        return view('admin.markets.form', [
            'market' => $market,
        ]);
    }

    /**
     * Perbarui data pasar.
     */
    public function update(UpdateMarketRequest $request, Market $market): RedirectResponse
    {
        $market->update($request->validated());

        return redirect()
            ->route('admin.markets.index')
            ->with('status', 'Pasar '.$market->name.' berhasil diperbarui.');
    }
}
