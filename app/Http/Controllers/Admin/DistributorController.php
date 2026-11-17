<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDistributorRequest;
use App\Http\Requests\Admin\UpdateDistributorRequest;
use App\Models\Distributor;
use App\Services\SessionRevoker;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DistributorController extends Controller
{
    public function __construct(private readonly SessionRevoker $sessions) {}

    /**
     * Tampilkan daftar distributor.
     */
    public function index(Request $request): View
    {
        $search = trim($request->string('q')->toString());
        $status = $request->string('status')->toString();
        $status = in_array($status, ['aktif', 'nonaktif'], true) ? $status : null;

        $distributors = Distributor::query()
            ->with('account:id,name,username,distributor_id')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->when($status === 'aktif', fn ($query) => $query->where('is_active', true))
            ->when($status === 'nonaktif', fn ($query) => $query->where('is_active', false))
            ->orderByDesc('id')
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
     * Tampilkan formulir tambah distributor beserta akun perusahaannya.
     */
    public function create(): View
    {
        return view('admin.distributors.create');
    }

    /**
     * Simpan distributor baru beserta akun login perusahaannya.
     */
    public function store(StoreDistributorRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated): void {
            $distributor = Distributor::create([
                'name' => $validated['name'],
                'penanggung_jawab' => $validated['penanggung_jawab'],
                'telepon' => $validated['telepon'],
                'alamat_kantor' => $validated['alamat_kantor'] ?? null,
                'is_active' => true,
            ]);

            $distributor->account()->create([
                'name' => $distributor->name,
                'username' => $validated['username'],
                'email' => $validated['email'] ?? null,
                'password' => $validated['password'],
                'role' => Role::Distributor->value,
                'is_active' => true,
            ]);
        });

        return redirect()->route('admin.distributors.index')
            ->with('success', 'Distributor dan akun perusahaan berhasil ditambahkan.');
    }

    /**
     * Tampilkan formulir edit distributor.
     */
    public function edit(Distributor $distributor): View
    {
        return view('admin.distributors.edit', [
            'distributor' => $distributor,
            'account' => $distributor->account,
        ]);
    }

    /**
     * Perbarui data distributor beserta akun login perusahaannya.
     */
    public function update(UpdateDistributorRequest $request, Distributor $distributor): RedirectResponse
    {
        $account = $distributor->account;
        $validated = $request->validated();

        DB::transaction(function () use ($distributor, $validated, $account): void {
            $distributor->update([
                'name' => $validated['name'],
                'penanggung_jawab' => $validated['penanggung_jawab'],
                'telepon' => $validated['telepon'],
                'alamat_kantor' => $validated['alamat_kantor'] ?? null,
            ]);

            $attributes = [
                'name' => $distributor->name,
                'username' => $validated['username'],
                'email' => $validated['email'] ?? null,
            ];

            if (! empty($validated['password'])) {
                $attributes['password'] = $validated['password'];
            }

            if ($account !== null) {
                $account->update($attributes);
            } else {
                $distributor->account()->create([
                    ...$attributes,
                    'role' => Role::Distributor->value,
                    'is_active' => $distributor->is_active,
                ]);
            }
        });

        if ($account !== null && ! empty($validated['password'])) {
            $this->sessions->revokeFor($account);
        }

        return redirect()->route('admin.distributors.index')
            ->with('success', 'Distributor dan akun perusahaan berhasil diperbarui.');
    }

    /**
     * Hapus distributor beserta akun login perusahaannya.
     */
    public function destroy(Distributor $distributor): RedirectResponse
    {
        if ($distributor->distributions()->exists()) {
            return back()->withErrors(['delete' => 'Distributor tidak dapat dihapus karena masih memiliki data penyaluran.']);
        }

        DB::transaction(function () use ($distributor): void {
            if ($distributor->account !== null) {
                $this->sessions->revokeFor($distributor->account);
                $distributor->account->delete();
            }

            $distributor->delete();
        });

        return redirect()->route('admin.distributors.index')
            ->with('success', 'Distributor dan akun perusahaan berhasil dihapus.');
    }
}
