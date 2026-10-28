<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class SupervisionController extends Controller
{
    /**
     * Tampilkan halaman pengawasan dan tindak lanjut.
     */
    public function index(): View
    {
        return view('admin.supervision.index');
    }
}
