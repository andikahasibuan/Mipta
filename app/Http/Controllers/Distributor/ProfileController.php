<?php

namespace App\Http\Controllers\Distributor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Distributor\UpdatePasswordRequest;
use App\Http\Requests\Distributor\UpdateProfileRequest;
use App\Services\SessionRevoker;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ProfileController extends Controller
{
    public function __construct(private readonly SessionRevoker $sessions) {}

    /**
     * Tampilkan halaman manajemen profil distributor.
     */
    public function edit(Request $request): View
    {
        return view('distributor.profile.edit', [
            'distributor' => $request->user()->distributor,
            'account' => $request->user(),
        ]);
    }

    /**
     * Perbarui informasi kontak distributor yang dapat diedit sendiri.
     */
    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $request->user()->distributor->update(Arr::only($validated, [
            'penanggung_jawab',
            'telepon',
            'alamat_kantor',
        ]));

        if ($request->has('email')) {
            $request->user()->update([
                'email' => $validated['email'] ?? null,
            ]);
        }

        return back()->with('success', 'Informasi distributor berhasil diperbarui.');
    }

    /**
     * Perbarui kata sandi akun distributor.
     */
    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $request->user()->update([
            'password' => $validated['password'],
        ]);

        $this->sessions->revokeFor($request->user(), $request->session()->getId());

        return back()->with('success', 'Kata sandi berhasil diperbarui.');
    }
}
