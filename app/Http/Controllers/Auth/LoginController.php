<?php

namespace App\Http\Controllers\Auth;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Tampilkan halaman login.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Autentikasi pengguna dan arahkan ke dashboard sesuai peran.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $role = Auth::user()?->role;

        if (! in_array($role, [Role::Admin, Role::KepalaDinas], true)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'identifier' => 'Akun ini tidak memiliki akses ke aplikasi ini.',
            ])->onlyInput('identifier');
        }

        $request->session()->regenerate();

        return redirect()->intended($this->dashboardRoute($role));
    }

    /**
     * Nama rute dashboard tujuan untuk sebuah peran.
     */
    private function dashboardRoute(Role $role): string
    {
        return match ($role) {
            Role::KepalaDinas => route('kepala-dinas.dashboard'),
            default => route('admin.dashboard'),
        };
    }

    /**
     * Akhiri sesi pengguna.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
