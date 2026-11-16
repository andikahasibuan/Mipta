<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DistributionReportService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DistributionController extends Controller
{
    /**
     * Tampilkan halaman laporan penyaluran.
     */
    public function index(Request $request, DistributionReportService $distribution): View
    {
        return view('admin.distribution.index', $distribution->paginate($request));
    }
}
