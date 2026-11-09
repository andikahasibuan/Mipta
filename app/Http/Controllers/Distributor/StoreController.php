<?php

namespace App\Http\Controllers\Distributor;

use App\Http\Controllers\Controller;
use App\Models\Market;
use App\Models\RecipientStore;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    /**
     * Tampilkan daftar toko penerima milik distributor.
     */
    public function index(Request $request): View
    {
        $stores = RecipientStore::query()
            ->where('distributor_id', $request->user()->distributor_id)
            ->with('market:id,name')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('distributor.stores.index', [
            'stores' => $stores,
        ]);
    }

    /**
     * Tampilkan formulir tambah toko penerima.
     */
    public function create(): View
    {
        return view('distributor.stores.create', [
            'markets' => Market::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Simpan toko penerima baru milik distributor.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'market_id' => ['required', 'integer', 'exists:markets,id'],
            'name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9 ]+$/'],
            'alamat' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9 .,\/-]+$/'],
            'nib' => ['required', 'digits:13'],
        ], $this->storeValidationMessages());

        RecipientStore::create([
            'distributor_id' => $request->user()->distributor_id,
            'market_id' => $validated['market_id'],
            'name' => $validated['name'],
            'alamat' => $validated['alamat'],
            'nib' => $validated['nib'] ?? null,
            'is_active' => true,
        ]);

        return redirect()->route('distributor.stores.index')
            ->with('success', 'Toko penerima berhasil ditambahkan.');
    }

    /**
     * Tampilkan formulir edit toko penerima.
     */
    public function edit(Request $request, RecipientStore $store): View
    {
        $this->ensureOwnedBy($request, $store);

        return view('distributor.stores.edit', [
            'store' => $store,
            'markets' => Market::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Perbarui data toko penerima milik distributor.
     */
    public function update(Request $request, RecipientStore $store): RedirectResponse
    {
        $this->ensureOwnedBy($request, $store);

        $validated = $request->validate([
            'market_id' => ['required', 'integer', 'exists:markets,id'],
            'name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9 ]+$/'],
            'alamat' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9 .,\/-]+$/'],
            'nib' => ['required', 'digits:13'],
        ], $this->storeValidationMessages());

        $store->update([
            'market_id' => $validated['market_id'],
            'name' => $validated['name'],
            'alamat' => $validated['alamat'],
            'nib' => $validated['nib'] ?? null,
        ]);

        return redirect()->route('distributor.stores.index')
            ->with('success', 'Toko penerima berhasil diperbarui.');
    }

    /**
     * Pastikan toko benar-benar milik distributor yang sedang login.
     */
    private function ensureOwnedBy(Request $request, RecipientStore $store): void
    {
        abort_unless($store->distributor_id === $request->user()->distributor_id, 404);
    }

    /**
     * Pesan validasi data toko penerima.
     *
     * @return array<string, string>
     */
    private function storeValidationMessages(): array
    {
        return [
            'name.regex' => 'Nama toko hanya boleh berisi huruf, angka, dan spasi.',
            'alamat.regex' => 'Alamat toko hanya boleh berisi huruf, angka, spasi, dan tanda baca (.,/-).',
            'nib.digits' => 'NIB harus terdiri dari tepat 13 angka.',
        ];
    }
}
