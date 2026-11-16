<?php

namespace App\Http\Middleware;

use App\Enums\Role;
use App\Models\UptdContributor;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUptdIsLinked
{
    /**
     * Pastikan area UPTD memiliki profil kontributor pasar yang aktif.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->role !== Role::UptdPasar) {
            abort(403, 'Akun ini bukan kontributor UPTD Pasar.');
        }

        if (! $user->isSharedUptdAccount()) {
            abort(403, 'Akun kontributor harus masuk melalui akun bersama UPTD Pasar.');
        }

        $selected = $request->session()->get('uptd_selected_contributor');

        $contributor = null;

        if (is_array($selected) && ! empty($selected['id'])) {
            $contributor = UptdContributor::query()
                ->whereKey((int) $selected['id'])
                ->where('is_active', true)
                ->whereHas('market', fn ($query) => $query->where('is_active', true))
                ->first();
        }

        if ($contributor === null) {
            $request->session()->forget('uptd_selected_contributor');

            return redirect()->route('uptd.contributors.index');
        }

        $request->attributes->set('uptd_contributor', $contributor);

        return $next($request);
    }
}
