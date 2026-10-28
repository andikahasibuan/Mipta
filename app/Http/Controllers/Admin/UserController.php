<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class UserController extends Controller
{
    /**
     * Tampilkan halaman pengguna dan data master.
     */
    public function index(): View
    {
        return view('admin.users.index');
    }
}
