<?php

namespace App\Http\Controllers\Auth;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
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
        return view('auth.login', [
            'selectedContributor' => session('uptd_selected_contributor'),
        ]);
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

        if ($user->isSharedUptdAccount()) {
            $request->session()->forget('uptd_selected_contributor');

            return redirect()->route('uptd.contributors.index');
        }

        if ($user->isMarketUptdContributor() && ! $this->hasSelectedContributor($request, $user)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'identifier' => 'Silakan masuk melalui akun bersama UPTD Pasar, lalu pilih identitas kontributor.',
            ])->onlyInput('identifier');
        }

        if ($user->role === Role::Distributor && $user->distributor_id === null) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'identifier' => 'Akun distributor belum dikaitkan ke PT. Hubungi administrator.',
            ])->onlyInput('identifier');
        }

        $request->session()->forget('uptd_selected_contributor');

        return redirect()->to($this->intendedUrl(route($home), $user->role->routePrefix()));
    }

    /**
     * Pastikan akun kontributor pasar yang login sama dengan identitas yang dipilih.
     */
    private function hasSelectedContributor(Request $request, User $user): bool
    {
        $selected = $request->session()->get('uptd_selected_contributor');

        return is_array($selected)
            && (int) ($selected['id'] ?? 0) === $user->id;
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
