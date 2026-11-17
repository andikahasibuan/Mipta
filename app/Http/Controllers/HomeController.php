<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Arahkan pengguna ke area sesuai perannya.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->is_active) {
            return $this->logout($request, 'Akun ini dinonaktifkan. Hubungi administrator.');
        }

        if ($user->role === Role::Distributor && $user->distributor_id === null) {
            return $this->logout($request, 'Akun distributor belum dikaitkan ke PT. Hubungi administrator.');
        }

        if ($user->isSharedUptdAccount()) {
            return redirect()->route('uptd.contributors.index');
        }

        $home = $user->role->homeRoute();

        if ($home === null) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login');
        }

        return redirect()->route($home);
    }

    private function logout(Request $request, string $message): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->withErrors(['identifier' => $message]);
    }
}
