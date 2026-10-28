<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class ActivityController extends Controller
{
    /**
     * Tampilkan halaman riwayat aktivitas sistem.
     */
    public function index(): View
    {
        return view('admin.activity.index');
    }
}
