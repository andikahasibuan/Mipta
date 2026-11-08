<?php

namespace App\Http\Controllers\Distributor;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class DistributionReportController extends Controller
{
    /**
     * Tampilkan formulir laporan penyaluran distributor.
     */
    public function create(): View
    {
        return view('distributor.distribution-reports.create');
    }
}
