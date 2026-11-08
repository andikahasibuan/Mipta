<?php

namespace App\Http\Controllers\Distributor;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class ReportHistoryController extends Controller
{
    /**
     * Tampilkan riwayat laporan distributor.
     */
    public function index(): View
    {
        return view('distributor.report-history.index');
    }
}
