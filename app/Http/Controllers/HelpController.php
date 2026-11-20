<?php

namespace App\Http\Controllers;

use App\Services\HelpCenterService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class HelpController extends Controller
{
    /**
     * Tampilkan halaman pusat bantuan.
     */
    public function __invoke(Request $request, HelpCenterService $helpCenter): View
    {
        $user = $request->user();

        return view('help.index', [
            'helpWhatsappDisplay' => $helpCenter->whatsappDisplay(),
            'helpWhatsappUrl' => $helpCenter->whatsappUrl(),
            'backRoute' => $user?->isSharedUptdAccount()
                ? 'uptd.contributors.index'
                : ($user?->role?->homeRoute() ?? 'login'),
            'backLabel' => 'Kembali',
        ]);
    }
}
