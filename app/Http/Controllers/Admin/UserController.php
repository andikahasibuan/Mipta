<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

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
}
