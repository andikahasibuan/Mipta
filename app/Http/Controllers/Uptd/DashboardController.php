<?php

namespace App\Http\Controllers\Uptd;

use App\Http\Controllers\Controller;
use App\Services\UptdDashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Tampilkan dashboard kontributor UPTD Pasar.
     */
    public function index(Request $request, UptdDashboardService $dashboard): View
    {
        return view('uptd.dashboard', $dashboard->build($request->user()));
    }
}
