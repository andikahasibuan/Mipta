<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class ReportController extends Controller
{
    /**
     * Tampilkan halaman rekap dan ekspor laporan.
     */
    public function index(): View
    {
        return view('admin.reports.index');
    }
}
