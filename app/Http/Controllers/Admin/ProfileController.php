<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class ProfileController extends Controller
{
    /**
     * Tampilkan halaman manajemen profil.
     */
    public function edit(): View
    {
        return view('admin.profile.edit');
    }
}
