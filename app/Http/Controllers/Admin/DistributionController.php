<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class DistributionController extends Controller
{
    /**
     * Tampilkan halaman laporan penyaluran.
     */
    public function index(): View
    {
        return view('admin.distribution.index');
    }
}
