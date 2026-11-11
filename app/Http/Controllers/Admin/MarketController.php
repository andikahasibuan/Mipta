<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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

        $searchId = preg_match('/^PSR-(\d+)$/i', $search, $matches) === 1 ? (int) $matches[1] : null;

        $markets = Market::query()
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('lokasi', 'like', "%{$search}%")
                ->when($searchId !== null, fn ($query) => $query->orWhereKey($searchId))))
            ->when($status === 'aktif', fn ($query) => $query->where('is_active', true))
            ->when($status === 'nonaktif', fn ($query) => $query->where('is_active', false))
            ->orderByDesc('created_at')
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
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9\s.\'-]+$/'],
            'lokasi' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9\s.,\/\'-]+$/'],
        ], $this->validationMessages());

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
    public function update(Request $request, Market $market): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9\s.\'-]+$/'],
            'lokasi' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9\s.,\/\'-]+$/'],
        ], $this->validationMessages());

        $market->update([
            'name' => $validated['name'],
            'lokasi' => $validated['lokasi'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.markets.index')->with('success', 'Pasar berhasil diperbarui.');
    }

    /**
     * Pesan validasi data pasar.
     *
     * @return array<string, string>
     */
    private function validationMessages(): array
    {
        return [
            'name.regex' => 'Nama pasar hanya boleh berisi huruf, angka, spasi, titik, apostrof, dan tanda hubung.',
            'lokasi.regex' => 'Lokasi hanya boleh berisi huruf, angka, spasi, dan tanda baca (.,/\'-).',
        ];
    }

    /**
     * Hapus pasar.
     */
    public function destroy(Market $market): RedirectResponse
    {
        if ($market->stores()->exists() || $market->distributions()->exists() || $market->fieldReports()->exists() || $market->uptdContributors()->exists()) {
            return back()->withErrors(['delete' => 'Pasar tidak dapat dihapus karena masih memiliki kontributor UPTD, toko penerima, laporan kondisi, atau data penyaluran.']);
        }

        $market->delete();

        return redirect()->route('admin.markets.index')->with('success', 'Pasar berhasil dihapus.');
    }
}
