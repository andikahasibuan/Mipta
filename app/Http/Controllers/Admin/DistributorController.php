<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Distributor;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DistributorController extends Controller
{
    /**
     * Tampilkan daftar distributor.
     */
    public function index(Request $request): View
    {
        $search = trim($request->string('q')->toString());
        $status = $request->string('status')->toString();
        $status = in_array($status, ['aktif', 'nonaktif'], true) ? $status : null;

        $distributors = Distributor::query()
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('wilayah', 'like', "%{$search}%")))
            ->when($status === 'aktif', fn ($query) => $query->where('is_active', true))
            ->when($status === 'nonaktif', fn ($query) => $query->where('is_active', false))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.distributors.index', [
            'distributors' => $distributors,
            'filters' => [
                'q' => $search,
                'status' => $status,
            ],
        ]);
    }

    /**
     * Tampilkan formulir tambah distributor.
     */
    public function create(): View
    {
        return view('admin.distributors.create');
    }

    /**
     * Simpan distributor baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'wilayah' => ['required', 'string', 'max:255'],
        ]);

        Distributor::create([
            'name' => $validated['name'],
            'wilayah' => $validated['wilayah'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.distributors.index')->with('success', 'Distributor berhasil ditambahkan.');
    }

    /**
     * Tampilkan formulir edit distributor.
     */
    public function edit(Distributor $distributor): View
    {
        return view('admin.distributors.edit', [
            'distributor' => $distributor,
        ]);
    }

    /**
     * Perbarui data distributor.
     */
    public function update(Request $request, Distributor $distributor): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'wilayah' => ['required', 'string', 'max:255'],
        ]);

        $distributor->update([
            'name' => $validated['name'],
            'wilayah' => $validated['wilayah'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.distributors.index')->with('success', 'Distributor berhasil diperbarui.');
    }

    /**
     * Hapus distributor.
     */
    public function destroy(Distributor $distributor): RedirectResponse
    {
        if ($distributor->distributions()->exists()) {
            return back()->withErrors(['delete' => 'Distributor tidak dapat dihapus karena masih memiliki data penyaluran.']);
        }

        $distributor->delete();

        return redirect()->route('admin.distributors.index')->with('success', 'Distributor berhasil dihapus.');
    }
}
