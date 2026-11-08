<?php

namespace App\Http\Controllers\Auth;

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
     * Autentikasi pengguna dan arahkan ke area sesuai perannya.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = Auth::user();
        $home = $user?->role->homeRoute();

        if (! $user || $home === null) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'identifier' => 'Akun ini tidak memiliki akses ke sistem.',
            ])->onlyInput('identifier');
        }

        $request->session()->regenerate();

        return redirect()->to($this->intendedUrl(route($home), $user->role->routePrefix()));
    }

    /**
     * Ambil URL tujuan awal hanya bila berada di area peran pengguna.
     */
    private function intendedUrl(string $fallback, ?string $areaPrefix): string
    {
        $intended = session()->pull('url.intended');

        if (! is_string($intended) || $areaPrefix === null) {
            return $fallback;
        }

        $path = parse_url($intended, PHP_URL_PATH);
        $area = '/'.$areaPrefix;

        if (! is_string($path) || ($path !== $area && ! str_starts_with($path, $area.'/'))) {
            return $fallback;
        }

        return $intended;
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
