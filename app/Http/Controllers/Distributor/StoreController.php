<?php

namespace App\Http\Controllers\Distributor;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class StoreController extends Controller
{
    /**
     * Tampilkan halaman manajemen data toko distributor.
     */
    public function index(): View
    {
        return view('distributor.stores.index');
    }
}
