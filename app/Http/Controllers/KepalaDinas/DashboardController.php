<?php

namespace App\Http\Controllers\KepalaDinas;

use App\Http\Controllers\Controller;
use App\Services\DashboardMonitoringService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Tampilkan dashboard monitoring kepala dinas.
     */
    public function index(Request $request, DashboardMonitoringService $dashboard): View
    {
        return view('kepala-dinas.dashboard', $dashboard->build($request));
    }
}
