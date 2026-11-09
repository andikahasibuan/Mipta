<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Distributor;
use App\Models\Market;
use App\Models\RecipientStore;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
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

        $distributorId = $request->integer('distributor');
        $distributorId = $distributorId > 0 && Distributor::whereKey($distributorId)->exists() ? $distributorId : null;

        $searchId = preg_match('/^TKS-(\d+)$/i', $search, $matches) === 1 ? (int) $matches[1] : null;

        $stores = RecipientStore::with(['market:id,name', 'distributor:id,name'])
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('alamat', 'like', "%{$search}%")
                ->when($searchId !== null, fn ($query) => $query->orWhereKey($searchId))))
            ->when($status === 'aktif', fn ($query) => $query->where('is_active', true))
            ->when($status === 'nonaktif', fn ($query) => $query->where('is_active', false))
            ->when($marketId, fn ($query, int $id) => $query->where('market_id', $id))
            ->when($distributorId, fn ($query, int $id) => $query->where('distributor_id', $id))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.recipient-stores.index', [
            'stores' => $stores,
            'markets' => Market::orderBy('name')->get(['id', 'name']),
            'distributors' => Distributor::orderBy('name')->get(['id', 'name']),
            'filters' => [
                'q' => $search,
                'market' => $marketId,
                'distributor' => $distributorId,
                'status' => $status,
            ],
        ]);
    }

    /**
     * Tampilkan formulir tambah toko penerima.
     */
    public function create(): View
    {
        return view('admin.recipient-stores.create', [
            'markets' => Market::orderBy('name')->get(['id', 'name']),
            'distributors' => Distributor::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Simpan toko penerima baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'market_id' => ['required', 'integer', 'exists:markets,id'],
            'distributor_id' => ['nullable', 'integer', 'exists:distributors,id'],
            'name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9 ]+$/'],
            'alamat' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9 .,\/-]+$/'],
            'nib' => ['required', 'digits:13'],
        ], $this->storeValidationMessages());

        RecipientStore::create([
            'market_id' => $validated['market_id'],
            'distributor_id' => $validated['distributor_id'] ?? null,
            'name' => $validated['name'],
            'alamat' => $validated['alamat'] ?? null,
            'nib' => $validated['nib'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.recipient-stores.index')->with('success', 'Toko penerima berhasil ditambahkan.');
    }

    /**
     * Tampilkan formulir edit toko penerima.
     */
    public function edit(RecipientStore $store): View
    {
        return view('admin.recipient-stores.edit', [
            'store' => $store,
            'markets' => Market::orderBy('name')->get(['id', 'name']),
            'distributors' => Distributor::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Perbarui data toko penerima.
     */
    public function update(Request $request, RecipientStore $store): RedirectResponse
    {
        $validated = $request->validate([
            'market_id' => ['required', 'integer', 'exists:markets,id'],
            'distributor_id' => ['nullable', 'integer', 'exists:distributors,id'],
            'name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9 ]+$/'],
            'alamat' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9 .,\/-]+$/'],
            'nib' => ['required', 'digits:13'],
        ], $this->storeValidationMessages());

        $store->update([
            'market_id' => $validated['market_id'],
            'distributor_id' => $validated['distributor_id'] ?? null,
            'name' => $validated['name'],
            'alamat' => $validated['alamat'] ?? null,
            'nib' => $validated['nib'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.recipient-stores.index')->with('success', 'Toko penerima berhasil diperbarui.');
    }

    /**
     * Hapus toko penerima.
     */
    public function destroy(RecipientStore $store): RedirectResponse
    {
        $store->delete();

        return redirect()->route('admin.recipient-stores.index')->with('success', 'Toko penerima berhasil dihapus.');
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
            'alamat.required' => 'Alamat toko wajib diisi.',
            'alamat.regex' => 'Alamat toko hanya boleh berisi huruf, angka, spasi, dan tanda baca (.,/-).',
            'nib.digits' => 'NIB harus terdiri dari tepat 13 angka.',
        ];
    }
}
