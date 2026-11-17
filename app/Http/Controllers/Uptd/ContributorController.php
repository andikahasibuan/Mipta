<?php

namespace App\Http\Controllers\Uptd;

use App\Http\Controllers\Controller;
use App\Models\UptdContributor;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ContributorController extends Controller
{
    /**
     * Jumlah maksimal percobaan login identitas sebelum dikunci.
     */
    private const MAX_LOGIN_ATTEMPTS = 3;

    /**
     * Durasi penguncian login identitas dalam detik (10 menit).
     */
    private const LOGIN_LOCKOUT_SECONDS = 600;

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

    public function select(Request $request): RedirectResponse
    {
        $this->ensureSharedAccount($request);

        $validated = $request->validate([
            'contributor_id' => ['required', 'integer'],
        ], [
            'contributor_id.required' => 'Pilih salah satu identitas kontributor.',
            'contributor_id.integer' => 'Identitas kontributor tidak valid.',
        ]);

        $contributor = $this->marketContributors()
            ->whereKey($validated['contributor_id'])
            ->first();

        if (! $contributor) {
            throw ValidationException::withMessages([
                'contributor_id' => 'Identitas kontributor tidak valid atau sudah tidak aktif.',
            ]);
        }

        $selected = $request->session()->get('uptd_selected_contributor');

        if (is_array($selected) && (int) ($selected['id'] ?? 0) === $contributor->id) {
            $request->session()->forget('uptd_pending_contributor');

            return redirect()->route('uptd.dashboard');
        }

        $request->session()->put('uptd_pending_contributor', $contributor->id);

        return redirect()->route('uptd.contributors.login', $contributor);
    }

    /**
     * Tampilkan formulir login untuk identitas kontributor yang dipilih.
     */
    public function showLogin(Request $request, UptdContributor $contributor): View|RedirectResponse
    {
        $this->ensureSharedAccount($request);

        $contributor = $this->activeContributor($contributor);

        if (! $this->hasPendingSelection($request, $contributor)) {
            return redirect()->route('uptd.contributors.index')
                ->withErrors(['contributor_id' => 'Pilih identitas kontributor terlebih dahulu.']);
        }

        return view('uptd.contributors.login', [
            'contributor' => $contributor->loadMissing('market:id,name'),
        ]);
    }

    /**
     * Verifikasi username dan password identitas kontributor yang dipilih.
     */
    public function login(Request $request, UptdContributor $contributor): RedirectResponse
    {
        $this->ensureSharedAccount($request);

        $contributor = $this->activeContributor($contributor);

        if (! $this->hasPendingSelection($request, $contributor)) {
            return redirect()->route('uptd.contributors.index')
                ->withErrors(['contributor_id' => 'Pilih identitas kontributor terlebih dahulu.']);
        }

        $validated = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $throttleKey = $this->accountThrottleKey($contributor);

        $this->ensureIsNotRateLimited($request, $throttleKey);

        $username = Str::lower(trim($validated['username']));

        if ($username !== $contributor->username || ! is_string($contributor->password) || ! Hash::check($validated['password'], $contributor->password)) {
            RateLimiter::hit($throttleKey, self::LOGIN_LOCKOUT_SECONDS);

            throw ValidationException::withMessages([
                'username' => 'Username atau password tidak sesuai.',
            ]);
        }

        RateLimiter::clear($throttleKey);

        $request->session()->forget('uptd_pending_contributor');
        $request->session()->regenerate();
        $request->session()->put('uptd_selected_contributor', [
            'id' => $contributor->id,
            'username' => $contributor->username,
            'name' => $contributor->name,
            'market' => $contributor->market?->name,
        ]);

        return redirect()->route('uptd.dashboard')
            ->with('success', 'Identitas kontributor berhasil dipilih.');
    }

    /**
     * Pastikan pengguna adalah akun bersama UPTD Pasar.
     */
    private function ensureSharedAccount(Request $request): void
    {
        abort_unless($request->user()?->isSharedUptdAccount(), 403);
    }

    /**
     * Pastikan identitas kontributor dan pasarnya masih aktif.
     */
    private function activeContributor(UptdContributor $contributor): UptdContributor
    {
        $contributor->loadMissing('market:id,name,is_active');

        abort_unless($contributor->is_active && $contributor->market?->is_active, 404);

        return $contributor;
    }

    /**
     * Pastikan akun bersama sudah memilih identitas yang sedang dituju.
     */
    private function hasPendingSelection(Request $request, UptdContributor $contributor): bool
    {
        return (int) $request->session()->get('uptd_pending_contributor') === $contributor->id;
    }

    /**
     * Pastikan percobaan login identitas belum melewati batas.
     */
    private function ensureIsNotRateLimited(Request $request, string $throttleKey): void
    {
        if (! RateLimiter::tooManyAttempts($throttleKey, self::MAX_LOGIN_ATTEMPTS)) {
            return;
        }

        event(new Lockout($request));

        $seconds = RateLimiter::availableIn($throttleKey);
        $minutes = max(1, (int) ceil($seconds / 60));

        throw ValidationException::withMessages([
            'username' => "Terlalu banyak percobaan masuk. Coba lagi dalam {$minutes} menit.",
        ]);
    }

    /**
     * Kunci pembatas percobaan login untuk identitas kontributor.
     */
    private function accountThrottleKey(UptdContributor $contributor): string
    {
        return 'uptd-contributor-account|'.$contributor->getKey();
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
