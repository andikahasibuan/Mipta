<?php

namespace App\Http\Controllers\Uptd;

use App\Http\Controllers\Controller;
use App\Models\UptdContributor;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ContributorController extends Controller
{
    /**
     * Tampilkan halaman pemilihan profil kontributor UPTD Pasar.
     */
    public function index(Request $request): View|RedirectResponse
    {
        if (! $request->user()?->isSharedUptdAccount()) {
            return redirect()->route('uptd.dashboard');
        }

        $contributors = $this->marketContributors()
            ->with('market:id,name')
            ->orderBy('username')
            ->get(['id', 'name', 'username', 'market_id']);

        $selected = $request->session()->get('uptd_selected_contributor');

        return view('uptd.contributors.index', [
            'contributors' => $contributors,
            'selectedContributorId' => is_array($selected) ? (int) ($selected['id'] ?? 0) : null,
        ]);
    }

    /**
     * Simpan profil kontributor aktif lalu lanjut ke dashboard.
     */
    public function select(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->isSharedUptdAccount(), 403);

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

        $selectedContributor = [
            'id' => $contributor->id,
            'username' => $contributor->username,
            'name' => $contributor->name,
            'market' => $contributor->market?->name,
        ];

        $request->session()->regenerate();
        $request->session()->put('uptd_selected_contributor', $selectedContributor);

        return redirect()->route('uptd.dashboard')
            ->with('success', 'Identitas kontributor berhasil dipilih.');
    }

    /**
     * Query profil kontributor aktif yang terkait ke pasar aktif.
     *
     * @return Builder<UptdContributor>
     */
    private function marketContributors(): Builder
    {
        return UptdContributor::query()
            ->where('is_active', true)
            ->whereHas('market', fn (Builder $query) => $query->where('is_active', true));
    }
}
