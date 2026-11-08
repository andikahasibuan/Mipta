<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\FieldSupervisionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SupervisionController extends Controller
{
    /**
     * Tampilkan halaman pengawasan dan tindak lanjut.
     */
    public function index(Request $request, FieldSupervisionService $supervision): View
    {
        return view('admin.supervision.index', $supervision->paginate($request));
    }
}
