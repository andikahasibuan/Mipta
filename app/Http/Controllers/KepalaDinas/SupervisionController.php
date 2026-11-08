<?php

namespace App\Http\Controllers\KepalaDinas;

use App\Http\Controllers\Controller;
use App\Services\FieldSupervisionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SupervisionController extends Controller
{
    /**
     * Tampilkan halaman monitoring kondisi pasar.
     */
    public function index(Request $request, FieldSupervisionService $supervision): View
    {
        return view('kepala-dinas.supervision.index', $supervision->paginate($request));
    }
}
