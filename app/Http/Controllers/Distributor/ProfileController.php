<?php

namespace App\Http\Controllers\Distributor;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
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
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'penanggung_jawab' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z\s.\'-]+$/'],
            'telepon' => ['nullable', 'string', 'regex:/^[0-9]{10,13}$/'],
            'alamat_kantor' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z0-9\s.,\/\'-]+$/'],
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($request->user()->id)],
        ], [
            'penanggung_jawab.regex' => 'Nama penanggung jawab hanya boleh berisi huruf, spasi, titik, apostrof, dan tanda hubung.',
            'telepon.regex' => 'Nomor telepon hanya boleh berisi angka (10 sampai 13 digit).',
            'alamat_kantor.regex' => 'Alamat kantor hanya boleh berisi huruf, angka, spasi, dan tanda baca (.,/\'-).',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan akun lain.',
        ]);

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
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ], [
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',
            'current_password.current_password' => 'Kata sandi saat ini tidak sesuai.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
            'password.different' => 'Kata sandi baru harus berbeda dari kata sandi saat ini.',
        ]);

        $request->user()->update([
            'password' => $validated['password'],
        ]);

        return back()->with('success', 'Kata sandi berhasil diperbarui.');
    }
}
