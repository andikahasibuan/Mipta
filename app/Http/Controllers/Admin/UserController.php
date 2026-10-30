<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Distributor;
use App\Models\Market;
use App\Models\RecipientStore;
use App\Models\User;
use Illuminate\Contracts\View\View;

class UserController extends Controller
{
    /**
     * Tampilkan halaman pengguna dan data master.
     */
    public function index(): View
    {
        return view('admin.users.index', [
            'totalUsers' => User::count(),
            'totalDistributors' => Distributor::count(),
            'activeDistributors' => Distributor::where('is_active', true)->count(),
            'totalStores' => RecipientStore::count(),
            'marketsWithStores' => Market::has('stores')->count(),
        ]);
    }
}
