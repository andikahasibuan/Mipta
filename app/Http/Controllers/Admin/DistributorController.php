<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Distributor;
use App\Models\User;
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

        $searchId = preg_match('/^DST-(\d+)$/i', $search, $matches) === 1 ? (int) $matches[1] : null;

        $distributors = Distributor::query()
            ->with('account:id,name,email,distributor_id')
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('wilayah', 'like', "%{$search}%")
                ->when($searchId !== null, fn ($query) => $query->orWhereKey($searchId))))
            ->when($status === 'aktif', fn ($query) => $query->where('is_active', true))
            ->when($status === 'nonaktif', fn ($query) => $query->where('is_active', false))
            ->orderByDesc('created_at')
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
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9\s.\'-]+$/'],
            'penanggung_jawab' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z\s.\'-]+$/'],
            'telepon' => ['required', 'string', 'regex:/^[0-9]{10,15}$/'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8'],
        ], $this->accountMessages());

        DB::transaction(function () use ($validated): void {
            $distributor = Distributor::create([
                'name' => $validated['name'],
                'wilayah' => 'Padang',
                'penanggung_jawab' => $validated['penanggung_jawab'],
                'telepon' => $validated['telepon'],
                'alamat_kantor' => null,
                'is_active' => true,
            ]);

            $distributor->account()->create([
                'name' => $distributor->name,
                'username' => $this->generateUsername($distributor->name),
                'email' => $validated['email'],
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

        $emailRule = Rule::unique('users', 'email');

        if ($account !== null) {
            $emailRule->ignore($account->id);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9\s.\'-]+$/'],
            'penanggung_jawab' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z\s.\'-]+$/'],
            'telepon' => ['required', 'string', 'regex:/^[0-9]{10,15}$/'],
            'email' => ['required', 'string', 'email', 'max:255', $emailRule],
            'password' => [$account === null ? 'required' : 'nullable', 'string', 'min:8'],
        ], $this->accountMessages());

        DB::transaction(function () use ($distributor, $validated, $account): void {
            $distributor->update([
                'name' => $validated['name'],
                'penanggung_jawab' => $validated['penanggung_jawab'],
                'telepon' => $validated['telepon'],
            ]);

            $attributes = [
                'name' => $distributor->name,
                'email' => $validated['email'],
            ];

            if (! empty($validated['password'])) {
                $attributes['password'] = $validated['password'];
            }

            if ($account !== null) {
                $account->update($attributes);
            } else {
                $distributor->account()->create([
                    ...$attributes,
                    'username' => $this->generateUsername($distributor->name),
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
     * Buat username unik dari nama perusahaan distributor.
     */
    private function generateUsername(string $name): string
    {
        $base = Str::slug($name);

        if ($base === '') {
            $base = 'distributor';
        }

        $username = $base;
        $suffix = 2;

        while (User::where('username', $username)->exists()) {
            $username = "{$base}-{$suffix}";
            $suffix++;
        }

        return $username;
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
            'penanggung_jawab.required' => 'Nama penanggung jawab wajib diisi.',
            'penanggung_jawab.regex' => 'Nama penanggung jawab hanya boleh berisi huruf, spasi, titik, apostrof, dan tanda hubung.',
            'telepon.required' => 'No. handphone penanggung jawab wajib diisi.',
            'telepon.regex' => 'No. handphone hanya boleh berisi angka (10 sampai 15 digit).',
            'email.required' => 'Email perusahaan wajib diisi.',
            'email.unique' => 'Email perusahaan sudah digunakan akun lain.',
            'password.required' => 'Password wajib diisi untuk membuat akun distributor.',
            'password.min' => 'Password minimal 8 karakter.',
        ];
    }
}
