<?php

namespace App\Http\Controllers\Uptd;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class ContributorController extends Controller
{
    /**
     * Tampilkan halaman ganti kontributor UPTD Pasar.
     */
    public function index(): View
    {
        return view('uptd.contributors.index');
    }
}
