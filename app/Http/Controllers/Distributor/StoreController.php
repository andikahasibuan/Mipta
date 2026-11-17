<?php

namespace App\Http\Controllers\Distributor;

use App\Http\Controllers\Controller;
use App\Models\Market;
use App\Models\RecipientStore;
use App\Notifications\RecipientStoreCreated;
use App\Notifications\RecipientStoreUpdated;
use App\Services\NotificationDispatcher;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'market_id' => ['required', 'integer', 'exists:markets,id'],
            'name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9 ]+$/'],
            'alamat' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z0-9 .,\/-]+$/'],
            'nib' => ['required', 'digits:13'],
        ], $this->storeValidationMessages());

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
            'alamat' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z0-9 .,\/-]+$/'],
            'nib' => ['required', 'digits:13'],
        ], $this->storeValidationMessages());

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
            'alamat.regex' => 'Detail alamat hanya boleh berisi huruf, angka, spasi, dan tanda baca (.,/-).',
            'nib.digits' => 'NIB harus terdiri dari tepat 13 angka.',
        ];
    }
}
