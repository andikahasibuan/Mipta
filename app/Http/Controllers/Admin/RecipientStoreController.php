<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRecipientStoreRequest;
use App\Http\Requests\Admin\UpdateRecipientStoreRequest;
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
     * Tampilkan form tambah toko penerima.
     */
    public function create(): View
    {
        return view('admin.recipient-stores.form', [
            'store' => null,
            'markets' => Market::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Simpan toko penerima baru.
     */
    public function store(StoreRecipientStoreRequest $request): RedirectResponse
    {
        $store = RecipientStore::create($request->validated());

        return redirect()
            ->route('admin.recipient-stores.index')
            ->with('status', 'Toko '.$store->name.' berhasil ditambahkan.');
    }

    /**
     * Tampilkan form ubah toko penerima.
     */
    public function edit(RecipientStore $recipientStore): View
    {
        return view('admin.recipient-stores.form', [
            'store' => $recipientStore,
            'markets' => Market::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Perbarui data toko penerima.
     */
    public function update(UpdateRecipientStoreRequest $request, RecipientStore $recipientStore): RedirectResponse
    {
        $recipientStore->update($request->validated());

        return redirect()
            ->route('admin.recipient-stores.index')
            ->with('status', 'Toko '.$recipientStore->name.' berhasil diperbarui.');
    }
}
