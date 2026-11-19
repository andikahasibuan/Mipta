<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardMonitoringService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Tampilkan dashboard monitoring admin.
     */
    public function index(Request $request, DashboardMonitoringService $dashboard): View
    {
        return view('admin.dashboard', $dashboard->build($request));
    }
}
