<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use App\Services\SessionRevoker;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(private readonly SessionRevoker $sessions) {}

    /**
     * Tampilkan halaman pengguna sesuai tab peran yang dipilih.
     */
    public function index(Request $request): View
    {
        $search = trim($request->string('q')->toString());
        $role = Role::tryFrom($request->string('role')->toString()) ?? Role::Admin;
        $status = $request->string('status')->toString();
        $status = in_array($status, ['aktif', 'nonaktif'], true) ? $status : null;

        if (! in_array($role, [Role::Admin, Role::KepalaDinas, Role::UptdPasar], true)) {
            $role = Role::Admin;
        }

        $users = User::query()
            ->where(fn ($query) => $query
                ->whereIn('role', [Role::Admin->value, Role::KepalaDinas->value])
                ->orWhere(fn ($query) => $query
                    ->where('role', Role::UptdPasar->value)
                    ->whereNull('market_id')))
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('username', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")))
            ->when($role, fn ($query, Role $role) => $query->where('role', $role->value))
            ->when($status === 'aktif', fn ($query) => $query->where('is_active', true))
            ->when($status === 'nonaktif', fn ($query) => $query->where('is_active', false))
            ->orderByDesc('id')
            ->paginate(5)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'filters' => [
                'q' => $search,
                'role' => $role->value,
                'status' => $status,
            ],
        ]);
    }

    /**
     * Tampilkan formulir tambah pengguna.
     */
    public function create(): View
    {
        return view('admin.users.create', [
            'roles' => $this->assignableRoles(),
        ]);
    }

    /**
     * Simpan pengguna baru.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'] ?? null,
            'password' => $validated['password'],
            'role' => $validated['role'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    /**
     * Tampilkan formulir edit pengguna.
     */
    public function edit(User $user): View
    {
        $this->ensureManageableUser($user);

        return view('admin.users.edit', [
            'user' => $user,
            'roles' => $this->assignableRoles(),
        ]);
    }

    /**
     * Perbarui data pengguna.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->ensureManageableUser($user);

        $validated = $request->validated();

        $data = [
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'] ?? null,
            'role' => $validated['role'],
            'is_active' => $request->boolean('is_active'),
        ];

        $newRole = Role::from($validated['role']);

        if ($user->role === Role::Admin && $newRole !== Role::Admin && ! $this->hasOtherAdmin($user)) {
            return back()->withErrors([
                'role' => 'Sistem harus memiliki minimal satu akun admin.',
            ])->withInput();
        }

        if ($user->is($request->user())) {
            if ($newRole !== $user->role) {
                return back()->withErrors([
                    'role' => 'Anda tidak dapat mengubah peran akun yang sedang digunakan untuk login.',
                ])->withInput();
            }

            if (! $data['is_active']) {
                return back()->withErrors([
                    'is_active' => 'Anda tidak dapat menonaktifkan akun yang sedang digunakan untuk login.',
                ])->withInput();
            }
        }

        if (! empty($validated['password'])) {
            $data['password'] = $validated['password'];
        }

        $user->update($data);

        if (! empty($validated['password']) || ! $data['is_active']) {
            $this->sessions->revokeFor($user, $user->is($request->user()) ? $request->session()->getId() : null);
        }

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil diperbarui.');
    }

    /**
     * Hapus pengguna.
     */
    public function destroy(User $user): RedirectResponse
    {
        $this->ensureManageableUser($user);

        if ($user->role === Role::Admin && ! $this->hasOtherAdmin($user)) {
            return back()->withErrors(['delete' => 'Sistem harus memiliki minimal satu akun admin.']);
        }

        if ($user->is(auth()->user())) {
            return back()->withErrors(['delete' => 'Anda tidak dapat menghapus akun yang sedang digunakan untuk login.']);
        }

        $this->sessions->revokeFor($user);
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil dihapus.');
    }

    /**
     * Peran yang dapat dikelola dari halaman pengguna.
     *
     * @return array<int, Role>
     */
    private function assignableRoles(): array
    {
        return array_values(array_filter(
            Role::cases(),
            fn (Role $role): bool => $role !== Role::Distributor,
        ));
    }

    /**
     * Pastikan masih ada akun admin lain selain pengguna yang diperiksa.
     */
    private function hasOtherAdmin(User $user): bool
    {
        return User::query()
            ->where('role', Role::Admin->value)
            ->where('is_active', true)
            ->whereKeyNot($user->getKey())
            ->exists();
    }

    /**
     * Pastikan hanya akun yang dikelola di tab Pengguna yang dapat diakses.
     */
    private function ensureManageableUser(User $user): void
    {
        abort_unless(
            $user->role === Role::Admin
            || $user->role === Role::KepalaDinas
            || $user->isSharedUptdAccount(),
            404,
        );
    }
}
