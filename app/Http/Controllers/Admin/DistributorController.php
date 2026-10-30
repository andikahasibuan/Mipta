<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Distributor;
use Illuminate\Contracts\View\View;

class DistributorController extends Controller
{
    /**
     * Tampilkan daftar distributor.
     */
    public function index(): View
    {
        return view('admin.distributors.index', [
            'distributors' => Distributor::orderBy('name')->paginate(10),
        ]);
    }
}
