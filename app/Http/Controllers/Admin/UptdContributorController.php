<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Market;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UptdContributorController extends Controller
{
    /**
     * Tampilkan daftar akun kontributor UPTD Pasar.
     */
    public function index(Request $request): View
    {
        $search = trim($request->string('q')->toString());
        $status = $request->string('status')->toString();
        $status = in_array($status, ['aktif', 'nonaktif'], true) ? $status : null;

        $contributors = User::where('role', Role::UptdPasar->value)
            ->with('market:id,name')
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('username', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")))
            ->when($status === 'aktif', fn ($query) => $query->where('is_active', true))
            ->when($status === 'nonaktif', fn ($query) => $query->where('is_active', false))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(5)
            ->withQueryString();

        return view('admin.uptd-contributors.index', [
            'contributors' => $contributors,
            'filters' => [
                'q' => $search,
                'status' => $status,
            ],
        ]);
    }

    /**
     * Tampilkan formulir tambah kontributor UPTD Pasar.
     */
    public function create(): View
    {
        return view('admin.uptd-contributors.create', [
            'markets' => Market::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Simpan kontributor UPTD Pasar baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z\s.\'-]+$/'],
            'username' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9._-]+$/', Rule::unique('users', 'username')],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8'],
            'market_id' => ['nullable', 'integer', 'exists:markets,id'],
        ], $this->validationMessages());

        User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => Role::UptdPasar->value,
            'market_id' => $validated['market_id'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.uptd-contributors.index')->with('success', 'Kontributor UPTD berhasil ditambahkan.');
    }

    /**
     * Tampilkan formulir edit kontributor UPTD Pasar.
     */
    public function edit(User $contributor): View
    {
        $this->ensureUptdContributor($contributor);

        return view('admin.uptd-contributors.edit', [
            'contributor' => $contributor,
            'markets' => Market::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Perbarui data kontributor UPTD Pasar.
     */
    public function update(Request $request, User $contributor): RedirectResponse
    {
        $this->ensureUptdContributor($contributor);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z\s.\'-]+$/'],
            'username' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9._-]+$/', Rule::unique('users', 'username')->ignore($contributor->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($contributor->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'market_id' => ['nullable', 'integer', 'exists:markets,id'],
        ], $this->validationMessages());

        $data = [
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'market_id' => $validated['market_id'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ];

        if (! empty($validated['password'])) {
            $data['password'] = $validated['password'];
        }

        $contributor->update($data);

        return redirect()->route('admin.uptd-contributors.index')->with('success', 'Kontributor UPTD berhasil diperbarui.');
    }

    /**
     * Hapus kontributor UPTD Pasar.
     */
    public function destroy(User $contributor): RedirectResponse
    {
        $this->ensureUptdContributor($contributor);

        if ($contributor->is(auth()->user())) {
            return back()->withErrors(['delete' => 'Anda tidak dapat menghapus akun yang sedang digunakan untuk login.']);
        }

        $contributor->delete();

        return redirect()->route('admin.uptd-contributors.index')->with('success', 'Kontributor UPTD berhasil dihapus.');
    }

    /**
     * Pesan validasi akun kontributor UPTD.
     *
     * @return array<string, string>
     */
    private function validationMessages(): array
    {
        return [
            'name.regex' => 'Nama hanya boleh berisi huruf, spasi, titik, apostrof, dan tanda hubung.',
            'username.regex' => 'Username hanya boleh berisi huruf, angka, titik, garis bawah, dan tanda hubung.',
        ];
    }

    /**
     * Pastikan pengguna yang diakses benar-benar berkontribusi sebagai UPTD Pasar.
     */
    private function ensureUptdContributor(User $contributor): void
    {
        abort_unless($contributor->role === Role::UptdPasar, 404);
    }
}
