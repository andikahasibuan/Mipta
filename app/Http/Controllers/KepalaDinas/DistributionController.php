<?php

namespace App\Http\Controllers\KepalaDinas;

use App\Http\Controllers\Controller;
use App\Services\DistributionReportService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DistributionController extends Controller
{
    /**
     * Tampilkan halaman laporan penyaluran kepala dinas.
     */
    public function index(Request $request, DistributionReportService $distribution): View
    {
        return view('kepala-dinas.distribution.index', $distribution->paginate($request));
    }
}
