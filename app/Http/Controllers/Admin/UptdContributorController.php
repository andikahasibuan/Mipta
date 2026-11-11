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
            ->whereNotNull('market_id')
            ->with('market:id,name')
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('username', 'like', "%{$search}%")))
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
            'markets' => Market::whereDoesntHave('uptdContributors')
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    /**
     * Simpan kontributor UPTD Pasar baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9\s.\'-]+$/'],
            'username' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9._-]+$/', Rule::unique('users', 'username')],
            'password' => ['required', 'string', 'min:8'],
            'market_id' => [
                'required',
                'integer',
                Rule::exists('markets', 'id'),
                Rule::unique('users', 'market_id')
                    ->where(fn ($query) => $query->where('role', Role::UptdPasar->value)),
            ],
        ], $this->validationMessages());

        User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => null,
            'password' => $validated['password'],
            'role' => Role::UptdPasar->value,
            'market_id' => $validated['market_id'],
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
            'markets' => Market::where(fn ($query) => $query
                ->whereDoesntHave('uptdContributors')
                ->orWhere('id', $contributor->market_id))
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    /**
     * Perbarui data kontributor UPTD Pasar.
     */
    public function update(Request $request, User $contributor): RedirectResponse
    {
        $this->ensureUptdContributor($contributor);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9\s.\'-]+$/'],
            'username' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9._-]+$/', Rule::unique('users', 'username')->ignore($contributor->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'market_id' => [
                'required',
                'integer',
                Rule::exists('markets', 'id'),
                Rule::unique('users', 'market_id')
                    ->where(fn ($query) => $query->where('role', Role::UptdPasar->value))
                    ->ignore($contributor->id),
            ],
        ], $this->validationMessages());

        $data = [
            'name' => $validated['name'],
            'username' => $validated['username'],
            'market_id' => $validated['market_id'],
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
            'name.regex' => 'Nama hanya boleh berisi huruf, angka, spasi, titik, apostrof, dan tanda hubung.',
            'username.regex' => 'Username hanya boleh berisi huruf, angka, titik, garis bawah, dan tanda hubung.',
            'market_id.required' => 'Pasar terkait wajib dipilih.',
            'market_id.exists' => 'Pasar yang dipilih tidak valid.',
            'market_id.unique' => 'Pasar ini sudah memiliki kontributor UPTD.',
        ];
    }

    /**
     * Pastikan pengguna yang diakses adalah kontributor UPTD yang terkait ke pasar.
     */
    private function ensureUptdContributor(User $contributor): void
    {
        abort_unless(
            $contributor->role === Role::UptdPasar && $contributor->market_id !== null,
            404,
        );
    }
}
