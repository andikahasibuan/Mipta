<?php

namespace App\Http\Controllers\KepalaDinas;

use App\Http\Controllers\MonitoringDashboardController;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends MonitoringDashboardController
{
    /**
     * Tampilkan dashboard monitoring kepala dinas.
     */
    public function index(Request $request): View
    {
        return view('dashboard.monitoring', array_merge($this->dashboardData($request), [
            'dashboardTitle' => 'Dashboard Kepala Dinas',
            'dashboardPageTitle' => 'Dashboard Monitoring',
            'dashboardRoute' => 'kepala-dinas.dashboard',
        ]));
    }
}
