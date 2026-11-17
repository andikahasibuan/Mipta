<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUptdContributorRequest;
use App\Http\Requests\Admin\UpdateUptdContributorRequest;
use App\Models\Market;
use App\Models\UptdContributor;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UptdContributorController extends Controller
{
    /**
     * Tampilkan daftar profil kontributor UPTD Pasar.
     */
    public function index(Request $request): View
    {
        $search = trim($request->string('q')->toString());
        $status = $request->string('status')->toString();
        $status = in_array($status, ['aktif', 'nonaktif'], true) ? $status : null;

        $contributors = UptdContributor::query()
            ->with('market:id,name')
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('username', 'like', "%{$search}%")))
            ->when($status === 'aktif', fn ($query) => $query->where('is_active', true))
            ->when($status === 'nonaktif', fn ($query) => $query->where('is_active', false))
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.uptd-contributors.index', [
            'contributors' => $contributors,
            'filters' => [
                'q' => $search,
                'status' => $status,
            ],
        ]);
    }

    /**
     * Tampilkan formulir tambah profil kontributor UPTD Pasar.
     */
    public function create(): View
    {
        return view('admin.users.uptd-contributors.create', [
            'markets' => Market::whereDoesntHave('uptdContributors')
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    /**
     * Simpan profil kontributor UPTD Pasar baru.
     */
    public function store(StoreUptdContributorRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        UptdContributor::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'password' => $validated['password'],
            'market_id' => $validated['market_id'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.users.uptd-contributors.index')->with('success', 'Kontributor UPTD berhasil ditambahkan.');
    }

    /**
     * Tampilkan formulir edit profil kontributor UPTD Pasar.
     */
    public function edit(UptdContributor $contributor): View
    {
        return view('admin.users.uptd-contributors.edit', [
            'contributor' => $contributor,
            'markets' => Market::where(fn ($query) => $query
                ->whereDoesntHave('uptdContributors')
                ->orWhere('id', $contributor->market_id))
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    /**
     * Perbarui profil kontributor UPTD Pasar.
     */
    public function update(UpdateUptdContributorRequest $request, UptdContributor $contributor): RedirectResponse
    {
        $validated = $request->validated();

        $attributes = [
            'name' => $validated['name'],
            'username' => $validated['username'],
            'market_id' => $validated['market_id'],
            'is_active' => $request->boolean('is_active'),
        ];

        if (filled($validated['password'] ?? null)) {
            $attributes['password'] = $validated['password'];
        }

        $contributor->update($attributes);

        return redirect()->route('admin.users.uptd-contributors.index')->with('success', 'Kontributor UPTD berhasil diperbarui.');
    }

    /**
     * Hapus profil kontributor UPTD Pasar.
     */
    public function destroy(UptdContributor $contributor): RedirectResponse
    {
        $contributor->delete();

        return redirect()->route('admin.users.uptd-contributors.index')->with('success', 'Kontributor UPTD berhasil dihapus.');
    }
}
