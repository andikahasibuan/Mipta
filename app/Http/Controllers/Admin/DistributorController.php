<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDistributorRequest;
use App\Http\Requests\Admin\UpdateDistributorRequest;
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
                ->orWhere('code', 'like', "%{$search}%")))
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
     * Tampilkan form tambah distributor.
     */
    public function create(): View
    {
        return view('admin.distributors.form', [
            'distributor' => null,
        ]);
    }

    /**
     * Simpan distributor baru.
     */
    public function store(StoreDistributorRequest $request): RedirectResponse
    {
        $distributor = Distributor::create($request->validated());

        return redirect()
            ->route('admin.distributors.index')
            ->with('status', 'Distributor '.$distributor->name.' berhasil ditambahkan.');
    }

    /**
     * Tampilkan form ubah distributor.
     */
    public function edit(Distributor $distributor): View
    {
        return view('admin.distributors.form', [
            'distributor' => $distributor,
        ]);
    }

    /**
     * Perbarui data distributor.
     */
    public function update(UpdateDistributorRequest $request, Distributor $distributor): RedirectResponse
    {
        $distributor->update($request->validated());

        return redirect()
            ->route('admin.distributors.index')
            ->with('status', 'Distributor '.$distributor->name.' berhasil diperbarui.');
    }
}
