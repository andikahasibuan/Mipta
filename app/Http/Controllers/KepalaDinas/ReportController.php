<?php

namespace App\Http\Controllers\KepalaDinas;

use App\Http\Controllers\Controller;
use App\Services\ReportRecapService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Tampilkan halaman rekapitulasi laporan kepala dinas.
     */
    public function index(Request $request, ReportRecapService $recap): View
    {
        return view('kepala-dinas.reports.index', $recap->build($request));
    }
}
