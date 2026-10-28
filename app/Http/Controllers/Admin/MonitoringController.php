<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class MonitoringController extends Controller
{
    /**
     * Tampilkan halaman monitoring dan analisis.
     */
    public function index(): View
    {
        return view('admin.monitoring.index');
    }
}
