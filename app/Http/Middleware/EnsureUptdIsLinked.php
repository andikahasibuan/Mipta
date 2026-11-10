<?php

namespace App\Http\Middleware;

use App\Enums\Role;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUptdIsLinked
{
    /**
     * Pastikan akun kontributor UPTD sudah dikaitkan ke satu pasar.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->role !== Role::UptdPasar) {
            abort(403, 'Akun ini bukan kontributor UPTD Pasar.');
        }

        if ($user->isSharedUptdAccount()) {
            return redirect()->route('uptd.contributors.index');
        }

        return $next($request);
    }
}
