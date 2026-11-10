<?php

namespace App\Http\Controllers\Uptd;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ContributorController extends Controller
{
    /**
     * Tampilkan halaman pemilihan identitas kontributor UPTD Pasar.
     */
    public function index(): View
    {
        $contributors = $this->marketContributors()
            ->with('market:id,name')
            ->orderBy('username')
            ->get(['id', 'name', 'username', 'market_id']);

        return view('uptd.contributors.index', [
            'contributors' => $contributors,
        ]);
    }

    /**
     * Lanjutkan ke dashboard bila identitas sama, atau simpan identitas baru lalu ke login.
     */
    public function select(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'contributor_id' => ['required', 'integer'],
        ], [
            'contributor_id.required' => 'Pilih salah satu identitas kontributor.',
            'contributor_id.integer' => 'Identitas kontributor tidak valid.',
        ]);

        $contributor = $this->marketContributors()
            ->whereKey($validated['contributor_id'])
            ->with('market:id,name')
            ->first(['id', 'name', 'username', 'market_id']);

        if (! $contributor) {
            throw ValidationException::withMessages([
                'contributor_id' => 'Identitas kontributor tidak valid atau sudah tidak aktif.',
            ]);
        }

        if ($request->user()?->is($contributor)) {
            $request->session()->forget('uptd_selected_contributor');

            return redirect()->route('uptd.dashboard');
        }

        $selectedContributor = [
            'id' => $contributor->id,
            'username' => $contributor->username,
            'name' => $contributor->name,
            'market' => $contributor->market?->name,
        ];

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->put('uptd_selected_contributor', $selectedContributor);

        return redirect()->route('login');
    }

    /**
     * Query akun kontributor UPTD aktif yang terkait ke pasar aktif.
     *
     * @return Builder<User>
     */
    private function marketContributors(): Builder
    {
        return User::query()
            ->where('role', Role::UptdPasar->value)
            ->whereNotNull('market_id')
            ->where('is_active', true)
            ->whereHas('market', fn (Builder $query) => $query->where('is_active', true));
    }
}
