<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Pastikan pengguna yang sudah login memiliki salah satu peran yang diizinkan.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'identifier' => 'Akun ini dinonaktifkan. Hubungi administrator.',
            ]);
        }

        if (! in_array($user->role?->value, $roles, true)) {
            abort(403);
        }

        return $next($request);
    }
}
