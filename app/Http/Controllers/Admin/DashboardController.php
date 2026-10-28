<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan dashboard admin.
     */
    public function index(): View
    {
        return view('admin.dashboard');
    }
}
