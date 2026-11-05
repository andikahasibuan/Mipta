<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Market;
use App\Models\RecipientStore;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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

    /**
     * Tampilkan formulir tambah toko penerima.
     */
    public function create(): View
    {
        return view('admin.recipient-stores.create', [
            'markets' => Market::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Simpan toko penerima baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'market_id' => ['required', 'integer', 'exists:markets,id'],
            'code' => ['required', 'string', 'max:255', Rule::unique('recipient_stores', 'code')],
            'name' => ['required', 'string', 'max:255'],
            'nib' => ['nullable', 'string', 'max:20'],
        ]);

        RecipientStore::create([
            'market_id' => $validated['market_id'],
            'code' => $validated['code'],
            'name' => $validated['name'],
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
        ]);
    }

    /**
     * Perbarui data toko penerima.
     */
    public function update(Request $request, RecipientStore $store): RedirectResponse
    {
        $validated = $request->validate([
            'market_id' => ['required', 'integer', 'exists:markets,id'],
            'code' => ['required', 'string', 'max:255', Rule::unique('recipient_stores', 'code')->ignore($store->id)],
            'name' => ['required', 'string', 'max:255'],
            'nib' => ['nullable', 'string', 'max:20'],
        ]);

        $store->update([
            'market_id' => $validated['market_id'],
            'code' => $validated['code'],
            'name' => $validated['name'],
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
}
