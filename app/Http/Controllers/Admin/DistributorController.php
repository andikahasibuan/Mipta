<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Distributor;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
    public function store(Request $request): RedirectResponse
    {
        $request->merge(['username' => Str::lower($request->string('username')->toString())]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9\s.\'-]+$/', Rule::unique('distributors', 'name')],
            'username' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9._-]+$/', Rule::unique('users', 'username')],
            'penanggung_jawab' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z\s.\'-]+$/'],
            'telepon' => ['required', 'string', 'regex:/^[0-9]{10,13}$/'],
            'alamat_kantor' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z0-9\s.,\/\'-]+$/'],
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8'],
        ], $this->accountMessages());

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
    public function update(Request $request, Distributor $distributor): RedirectResponse
    {
        $account = $distributor->account;

        $request->merge(['username' => Str::lower($request->string('username')->toString())]);

        $emailRule = Rule::unique('users', 'email');
        $usernameRule = Rule::unique('users', 'username');

        if ($account !== null) {
            $emailRule->ignore($account->id);
            $usernameRule->ignore($account->id);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9\s.\'-]+$/', Rule::unique('distributors', 'name')->ignore($distributor->id)],
            'username' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9._-]+$/', $usernameRule],
            'penanggung_jawab' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z\s.\'-]+$/'],
            'telepon' => ['required', 'string', 'regex:/^[0-9]{10,13}$/'],
            'alamat_kantor' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z0-9\s.,\/\'-]+$/'],
            'email' => ['nullable', 'string', 'email', 'max:255', $emailRule],
            'password' => [$account === null ? 'required' : 'nullable', 'string', 'min:8'],
        ], $this->accountMessages());

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
            'name.required' => 'Nama perusahaan distributor wajib diisi.',
            'name.regex' => 'Nama perusahaan hanya boleh berisi huruf, angka, spasi, titik, apostrof, dan tanda hubung.',
            'name.unique' => 'Nama distributor sudah digunakan.',
            'username.required' => 'Username distributor wajib diisi.',
            'username.regex' => 'Username tidak boleh mengandung spasi dan hanya boleh berisi huruf, angka, titik, garis bawah, dan tanda hubung.',
            'username.unique' => 'Username sudah digunakan.',
            'penanggung_jawab.required' => 'Nama penanggung jawab wajib diisi.',
            'penanggung_jawab.regex' => 'Nama penanggung jawab hanya boleh berisi huruf, spasi, titik, apostrof, dan tanda hubung.',
            'telepon.required' => 'No. handphone penanggung jawab wajib diisi.',
            'telepon.regex' => 'No. handphone hanya boleh berisi angka (10 sampai 13 digit).',
            'alamat_kantor.regex' => 'Alamat hanya boleh berisi huruf, angka, spasi, dan tanda baca (.,/\'-).',
            'email.unique' => 'Email perusahaan sudah digunakan akun lain.',
            'password.required' => 'Password wajib diisi untuk membuat akun distributor.',
            'password.min' => 'Password minimal 8 karakter.',
        ];
    }
}
