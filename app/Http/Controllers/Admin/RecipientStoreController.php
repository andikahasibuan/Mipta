<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRecipientStoreRequest;
use App\Http\Requests\Admin\UpdateRecipientStoreRequest;
use App\Models\Distributor;
use App\Models\Market;
use App\Models\RecipientStore;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;

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

        $highlightId = $request->integer('highlight');
        $highlightId = $highlightId > 0 && RecipientStore::whereKey($highlightId)->exists() ? $highlightId : null;

        $query = RecipientStore::with(['market:id,name', 'distributor:id,name'])
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('alamat', 'like', "%{$search}%")
                ->orWhereHas('market', fn ($marketQuery) => $marketQuery->where('name', 'like', "%{$search}%"))))
            ->when($status === 'aktif', fn ($query) => $query->where('is_active', true))
            ->when($status === 'nonaktif', fn ($query) => $query->where('is_active', false))
            ->when($marketId, fn ($query, int $id) => $query->where('market_id', $id))
            ->when($distributorId, fn ($query, int $id) => $query->where('distributor_id', $id))
            ->orderByDesc('id');

        $stores = $query
            ->paginate(10, ['*'], 'page', $this->highlightPage($query, $highlightId))
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
                'highlight' => $highlightId,
            ],
        ]);
    }

    /**
     * Tentukan halaman yang memuat baris yang perlu di-highlight.
     *
     * @param  Builder<RecipientStore>  $query
     */
    private function highlightPage(Builder $query, ?int $highlightId): int
    {
        if ($highlightId === null) {
            return Paginator::resolveCurrentPage();
        }

        $highlighted = (clone $query)->whereKey($highlightId)->first();

        if ($highlighted === null) {
            return Paginator::resolveCurrentPage();
        }

        return intdiv((clone $query)->where('id', '>', $highlighted->id)->count(), 10) + 1;
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
    public function store(StoreRecipientStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();

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
    public function update(UpdateRecipientStoreRequest $request, RecipientStore $store): RedirectResponse
    {
        $validated = $request->validated();

        if ($store->distributions()->exists() && $this->movesStore($store, $validated)) {
            return back()->withErrors([
                'market_id' => 'Pasar atau distributor toko tidak dapat diubah karena toko sudah memiliki riwayat penyaluran.',
            ])->withInput();
        }

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
        if ($store->distributions()->exists()) {
            return back()->withErrors([
                'delete' => 'Toko penerima tidak dapat dihapus karena masih memiliki riwayat penyaluran.',
            ]);
        }

        $store->delete();

        return redirect()->route('admin.recipient-stores.index')->with('success', 'Toko penerima berhasil dihapus.');
    }

    /**
     * Periksa apakah pasar atau distributor toko berubah.
     *
     * @param  array<string, mixed>  $validated
     */
    private function movesStore(RecipientStore $store, array $validated): bool
    {
        return (int) $validated['market_id'] !== $store->market_id
            || (int) ($validated['distributor_id'] ?? 0) !== (int) ($store->distributor_id ?? 0);
    }
}
