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
                ->orWhere('lokasi', 'like', "%{$search}%")))
            ->when($status === 'aktif', fn ($query) => $query->where('is_active', true))
            ->when($status === 'nonaktif', fn ($query) => $query->where('is_active', false))
            ->orderByDesc('id')
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
     * Tampilkan formulir tambah pasar.
     */
    public function create(): View
    {
        return view('admin.markets.create');
    }

    /**
     * Simpan pasar baru.
     */
    public function store(StoreMarketRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        Market::create([
            'name' => $validated['name'],
            'lokasi' => $validated['lokasi'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.markets.index')->with('success', 'Pasar berhasil ditambahkan.');
    }

    /**
     * Tampilkan formulir edit pasar.
     */
    public function edit(Market $market): View
    {
        return view('admin.markets.edit', [
            'market' => $market,
        ]);
    }

    /**
     * Perbarui data pasar.
     */
    public function update(UpdateMarketRequest $request, Market $market): RedirectResponse
    {
        $validated = $request->validated();

        $market->update([
            'name' => $validated['name'],
            'lokasi' => $validated['lokasi'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.markets.index')->with('success', 'Pasar berhasil diperbarui.');
    }

    /**
     * Hapus pasar.
     */
    public function destroy(Market $market): RedirectResponse
    {
        if ($market->stores()->exists() || $market->distributions()->exists() || $market->fieldReports()->exists() || $market->uptdContributors()->exists()) {
            return back()->withErrors(['delete' => 'Pasar tidak dapat dihapus karena masih memiliki kontributor UPTD, toko penerima, laporan pengawasan, atau data penyaluran.']);
        }

        $market->delete();

        return redirect()->route('admin.markets.index')->with('success', 'Pasar berhasil dihapus.');
    }
}
