<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Distributor;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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

        $searchId = preg_match('/^DST-(\d+)$/i', $search, $matches) === 1 ? (int) $matches[1] : null;

        $distributors = Distributor::query()
            ->with('account:id,name,email,distributor_id')
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('wilayah', 'like', "%{$search}%")
                ->when($searchId !== null, fn ($query) => $query->orWhereKey($searchId))))
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
     * Tampilkan formulir tambah distributor beserta akun perusahaannya.
     */
    public function create(): View
    {
        return view('admin.distributors.create');
    }

    /**
     * Simpan distributor baru beserta akun login perusahaannya.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'wilayah' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')],
            'password' => ['required', 'string', 'min:8'],
        ], $this->accountMessages());

        DB::transaction(function () use ($validated, $request): void {
            $distributor = Distributor::create([
                'name' => $validated['name'],
                'wilayah' => $validated['wilayah'],
                'is_active' => $request->boolean('is_active'),
            ]);

            $distributor->account()->create([
                'name' => $distributor->name,
                'username' => $validated['username'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => Role::Distributor->value,
                'is_active' => $distributor->is_active,
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
    public function update(Request $request, Distributor $distributor): RedirectResponse
    {
        $account = $distributor->account;

        $emailRule = Rule::unique('users', 'email');
        $usernameRule = Rule::unique('users', 'username');

        if ($account !== null) {
            $emailRule->ignore($account->id);
            $usernameRule->ignore($account->id);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'wilayah' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', $emailRule],
            'username' => ['required', 'string', 'max:255', $usernameRule],
            'password' => [$account === null ? 'required' : 'nullable', 'string', 'min:8'],
        ], $this->accountMessages());

        DB::transaction(function () use ($distributor, $validated, $request, $account): void {
            $distributor->update([
                'name' => $validated['name'],
                'wilayah' => $validated['wilayah'],
                'is_active' => $request->boolean('is_active'),
            ]);

            $attributes = [
                'name' => $distributor->name,
                'username' => $validated['username'],
                'email' => $validated['email'],
                'role' => Role::Distributor->value,
                'is_active' => $distributor->is_active,
            ];

            if (! empty($validated['password'])) {
                $attributes['password'] = $validated['password'];
            }

            if ($account !== null) {
                $account->update($attributes);
            } else {
                $distributor->account()->create($attributes);
            }
        });

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
            $distributor->account?->delete();
            $distributor->delete();
        });

        return redirect()->route('admin.distributors.index')
            ->with('success', 'Distributor dan akun perusahaan berhasil dihapus.');
    }

    /**
     * Pesan validasi untuk akun login distributor.
     *
     * @return array<string, string>
     */
    private function accountMessages(): array
    {
        return [
            'email.required' => 'Email perusahaan wajib diisi.',
            'email.unique' => 'Email perusahaan sudah digunakan akun lain.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah digunakan akun lain.',
            'password.required' => 'Password wajib diisi untuk membuat akun distributor.',
            'password.min' => 'Password minimal 8 karakter.',
        ];
    }
}
