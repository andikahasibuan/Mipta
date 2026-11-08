<?php

namespace App\Http\Middleware;

use App\Enums\Role;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDistributorIsLinked
{
    /**
     * Pastikan akun distributor sudah dikaitkan ke satu data distributor.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->role !== Role::Distributor || $user->distributor_id === null) {
            abort(403, 'Akun distributor belum dikaitkan ke PT.');
        }

        return $next($request);
    }
}
