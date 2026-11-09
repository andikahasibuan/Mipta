<?php

namespace App\Http\Controllers\Distributor;

use App\Http\Controllers\Controller;
use App\Services\DistributorDashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Tampilkan dashboard monitoring distributor.
     */
    public function index(Request $request, DistributorDashboardService $dashboard): View
    {
        $distributor = $request->user()?->distributor;

        abort_if($distributor === null, 403);

        return view('distributor.dashboard', $dashboard->build($request, $distributor));
    }
}
