<?php

namespace App\Http\Controllers\Distributor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Distributor\StoreRecipientStoreRequest;
use App\Http\Requests\Distributor\UpdateRecipientStoreRequest;
use App\Models\Market;
use App\Models\RecipientStore;
use App\Notifications\RecipientStoreCreated;
use App\Notifications\RecipientStoreUpdated;
use App\Services\NotificationDispatcher;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class StoreController extends Controller
{
    public function __construct(private readonly NotificationDispatcher $notifications) {}

    /**
     * Tampilkan daftar toko penerima milik distributor.
     */
    public function index(Request $request): View
    {
        $search = trim($request->string('q')->toString());
        $marketId = $request->integer('market');
        $marketId = $marketId > 0 && Market::whereKey($marketId)->exists() ? $marketId : null;

        $stores = RecipientStore::query()
            ->where('distributor_id', $request->user()->distributor_id)
            ->with('market:id,name')
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('alamat', 'like', "%{$search}%")
                ->orWhere('nib', 'like', "%{$search}%")
                ->orWhereHas('market', fn ($marketQuery) => $marketQuery->where('name', 'like', "%{$search}%"))))
            ->when($marketId, fn ($query, int $id) => $query->where('market_id', $id))
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('distributor.stores.index', [
            'stores' => $stores,
            'markets' => Market::orderBy('name')->get(['id', 'name']),
            'filters' => [
                'q' => $search,
                'market' => $marketId,
            ],
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
    public function store(StoreRecipientStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $distributorName = $request->user()->distributor->name;
        $marketName = Market::query()->whereKey($validated['market_id'])->value('name') ?? '—';

        DB::transaction(function () use ($request, $validated, $distributorName, $marketName): void {
            $store = RecipientStore::create([
                'distributor_id' => $request->user()->distributor_id,
                'market_id' => $validated['market_id'],
                'name' => $validated['name'],
                'alamat' => $validated['alamat'] ?? null,
                'nib' => $validated['nib'] ?? null,
                'is_active' => true,
            ]);

            $this->notifications->sendToAdmin(
                new RecipientStoreCreated(
                    $store->name,
                    $marketName,
                    $distributorName,
                    $store->id,
                ),
            );
        });

        return redirect()->route('distributor.stores.index')
            ->with('success', 'Toko penerima berhasil ditambahkan.');
    }

    /**
     * Tampilkan formulir edit toko penerima.
     */
    public function edit(Request $request, RecipientStore $store): View
    {
        $this->ensureOwnedBy($store);

        return view('distributor.stores.edit', [
            'store' => $store,
            'markets' => Market::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Perbarui data toko penerima milik distributor.
     */
    public function update(UpdateRecipientStoreRequest $request, RecipientStore $store): RedirectResponse
    {
        $validated = $request->validated();

        if ($store->distributions()->exists() && (int) $validated['market_id'] !== $store->market_id) {
            return back()->withErrors([
                'market_id' => 'Pasar toko tidak dapat diubah karena toko sudah memiliki riwayat penyaluran.',
            ])->withInput();
        }

        $store->fill([
            'market_id' => $validated['market_id'],
            'name' => $validated['name'],
            'alamat' => $validated['alamat'] ?? null,
            'nib' => $validated['nib'] ?? null,
        ]);

        if ($store->isDirty()) {
            $distributorName = $request->user()->distributor->name;
            $marketName = Market::query()->whereKey($validated['market_id'])->value('name') ?? '—';

            DB::transaction(function () use ($store, $distributorName, $marketName): void {
                $store->save();

                $this->notifications->sendToAdmin(
                    new RecipientStoreUpdated(
                        $store->name,
                        $marketName,
                        $distributorName,
                        $store->id,
                    ),
                );
            });
        }

        return redirect()->route('distributor.stores.index')
            ->with('success', 'Toko penerima berhasil diperbarui.');
    }

    /**
     * Pastikan toko benar-benar milik distributor yang sedang login.
     */
    private function ensureOwnedBy(RecipientStore $store): void
    {
        abort_unless(Gate::allows('update', $store), 404);
    }
}
