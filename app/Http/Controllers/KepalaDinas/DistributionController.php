<?php

namespace App\Http\Controllers\KepalaDinas;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class DistributionController extends Controller
{
    /**
     * Tampilkan halaman monitoring laporan penyaluran.
     */
    public function index(): View
    {
        return view('kepala-dinas.distribution.index');
    }
}
