<?php

namespace App\Http\Controllers\KepalaDinas;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class MarketController extends Controller
{
    /**
     * Tampilkan halaman monitoring kondisi pasar.
     */
    public function index(): View
    {
        return view('kepala-dinas.markets.index');
    }
}
