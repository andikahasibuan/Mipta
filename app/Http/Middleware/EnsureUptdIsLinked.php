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

        if (! $user || $user->role !== Role::UptdPasar || $user->market_id === null) {
            abort(403, 'Akun kontributor UPTD belum dikaitkan ke pasar.');
        }

        return $next($request);
    }
}
