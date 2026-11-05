<?php

namespace App\Http\Controllers\KepalaDinas;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class ReportController extends Controller
{
    /**
     * Tampilkan halaman rekapitulasi laporan.
     */
    public function index(): View
    {
        return view('kepala-dinas.reports.index');
    }
}
