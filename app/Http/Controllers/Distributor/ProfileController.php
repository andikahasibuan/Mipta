<?php

namespace App\Http\Controllers\Distributor;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class ProfileController extends Controller
{
    /**
     * Tampilkan halaman manajemen profil distributor.
     */
    public function edit(): View
    {
        return view('distributor.profile.edit');
    }
}
