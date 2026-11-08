<?php

namespace App\Http\Controllers\Distributor;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan dashboard monitoring distributor.
     */
    public function index(): View
    {
        return view('distributor.dashboard');
    }
}
