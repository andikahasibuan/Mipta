<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\MonitoringDashboardController;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends MonitoringDashboardController
{
    /**
     * Tampilkan dashboard monitoring admin.
     */
    public function index(Request $request): View
    {
        return view('dashboard.monitoring', array_merge($this->dashboardData($request), [
            'layout' => 'layouts.admin',
            'dashboardTitle' => 'Dashboard Admin',
            'dashboardPageTitle' => 'Dashboard Monitoring',
            'dashboardRoute' => 'admin.dashboard',
        ]));
    }
}
