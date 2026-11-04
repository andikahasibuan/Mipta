<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * Tampilkan halaman pengguna dan data master.
     */
    public function index(Request $request): View
    {
        $search = trim($request->string('q')->toString());
        $role = Role::tryFrom($request->string('role')->toString());
        $status = $request->string('status')->toString();
        $status = in_array($status, ['aktif', 'nonaktif'], true) ? $status : null;

        $users = User::query()
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('username', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")))
            ->when($role, fn ($query, Role $role) => $query->where('role', $role->value))
            ->when($status === 'aktif', fn ($query) => $query->where('is_active', true))
            ->when($status === 'nonaktif', fn ($query) => $query->where('is_active', false))
            ->orderBy('name')
            ->paginate(5)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => Role::cases(),
            'filters' => [
                'q' => $search,
                'role' => $role?->value,
                'status' => $status,
            ],
        ]);
    }

    /**
     * Tampilkan form tambah pengguna.
     */
    public function create(): View
    {
        return view('admin.users.create', [
            'roles' => Role::cases(),
        ]);
    }

    /**
     * Simpan pengguna baru.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = User::create($request->validated());

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Pengguna '.$user->name.' berhasil ditambahkan.');
    }

    /**
     * Tampilkan form ubah pengguna.
     */
    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'user' => $user,
            'roles' => Role::cases(),
        ]);
    }

    /**
     * Perbarui data pengguna.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        if (! (bool) $data['is_active'] && $user->is($request->user())) {
            throw ValidationException::withMessages([
                'is_active' => 'Anda tidak dapat menonaktifkan akun sendiri.',
            ]);
        }

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Pengguna '.$user->name.' berhasil diperbarui.');
    }

    /**
     * Aktifkan atau nonaktifkan akun pengguna.
     */
    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->withErrors([
                'status' => 'Anda tidak dapat mengubah status akun sendiri.',
            ]);
        }

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with(
            'status',
            'Akun '.$user->name.' '.($user->is_active ? 'diaktifkan' : 'dinonaktifkan').'.',
        );
    }
}
