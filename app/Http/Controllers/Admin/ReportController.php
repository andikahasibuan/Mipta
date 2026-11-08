<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportRecapService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Tampilkan halaman rekap dan ekspor laporan.
     */
    public function index(Request $request, ReportRecapService $recap): View
    {
        return view('admin.reports.index', $recap->build($request));
    }
}
