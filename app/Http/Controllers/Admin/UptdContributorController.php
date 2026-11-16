<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Market;
use App\Models\UptdContributor;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UptdContributorController extends Controller
{
    /**
     * Tampilkan daftar profil kontributor UPTD Pasar.
     */
    public function index(Request $request): View
    {
        $search = trim($request->string('q')->toString());
        $status = $request->string('status')->toString();
        $status = in_array($status, ['aktif', 'nonaktif'], true) ? $status : null;

        $contributors = UptdContributor::query()
            ->with('market:id,name')
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('username', 'like', "%{$search}%")))
            ->when($status === 'aktif', fn ($query) => $query->where('is_active', true))
            ->when($status === 'nonaktif', fn ($query) => $query->where('is_active', false))
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.uptd-contributors.index', [
            'contributors' => $contributors,
            'filters' => [
                'q' => $search,
                'status' => $status,
            ],
        ]);
    }

    /**
     * Tampilkan formulir tambah profil kontributor UPTD Pasar.
     */
    public function create(): View
    {
        return view('admin.uptd-contributors.create', [
            'markets' => Market::whereDoesntHave('uptdContributors')
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    /**
     * Simpan profil kontributor UPTD Pasar baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->merge(['username' => Str::lower($request->string('username')->toString())]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9\s.\'-]+$/'],
            'username' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9._-]+$/', Rule::unique('uptd_contributors', 'username')],
            'market_id' => [
                'required',
                'integer',
                Rule::exists('markets', 'id'),
                Rule::unique('uptd_contributors', 'market_id'),
            ],
        ], $this->validationMessages());

        UptdContributor::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'market_id' => $validated['market_id'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.uptd-contributors.index')->with('success', 'Kontributor UPTD berhasil ditambahkan.');
    }

    /**
     * Tampilkan formulir edit profil kontributor UPTD Pasar.
     */
    public function edit(UptdContributor $contributor): View
    {
        return view('admin.uptd-contributors.edit', [
            'contributor' => $contributor,
            'markets' => Market::where(fn ($query) => $query
                ->whereDoesntHave('uptdContributors')
                ->orWhere('id', $contributor->market_id))
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    /**
     * Perbarui profil kontributor UPTD Pasar.
     */
    public function update(Request $request, UptdContributor $contributor): RedirectResponse
    {
        $request->merge(['username' => Str::lower($request->string('username')->toString())]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9\s.\'-]+$/'],
            'username' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9._-]+$/', Rule::unique('uptd_contributors', 'username')->ignore($contributor->id)],
            'market_id' => [
                'required',
                'integer',
                Rule::exists('markets', 'id'),
                Rule::unique('uptd_contributors', 'market_id')->ignore($contributor->id),
            ],
        ], $this->validationMessages());

        $contributor->update([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'market_id' => $validated['market_id'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.uptd-contributors.index')->with('success', 'Kontributor UPTD berhasil diperbarui.');
    }

    /**
     * Hapus profil kontributor UPTD Pasar.
     */
    public function destroy(UptdContributor $contributor): RedirectResponse
    {
        $contributor->delete();

        return redirect()->route('admin.uptd-contributors.index')->with('success', 'Kontributor UPTD berhasil dihapus.');
    }

    /**
     * Pesan validasi profil kontributor UPTD.
     *
     * @return array<string, string>
     */
    private function validationMessages(): array
    {
        return [
            'name.regex' => 'Nama hanya boleh berisi huruf, angka, spasi, titik, apostrof, dan tanda hubung.',
            'username.regex' => 'Username tidak boleh mengandung spasi dan hanya boleh berisi huruf, angka, titik, garis bawah, dan tanda hubung.',
            'market_id.required' => 'Pasar terkait wajib dipilih.',
            'market_id.exists' => 'Pasar yang dipilih tidak valid.',
            'market_id.unique' => 'Pasar ini sudah memiliki kontributor UPTD.',
        ];
    }
}
