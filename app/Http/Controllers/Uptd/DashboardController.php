<?php

namespace App\Http\Controllers\Uptd;

use App\Http\Controllers\Controller;
use App\Models\UptdContributor;
use App\Services\UptdDashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Tampilkan dashboard kontributor UPTD Pasar yang dipilih.
     */
    public function index(Request $request, UptdDashboardService $dashboard): View
    {
        return view('uptd.dashboard', $dashboard->build($this->contributor($request)));
    }

    /**
     * Ambil kontributor aktif yang dipilih pada session.
     */
    private function contributor(Request $request): UptdContributor
    {
        $contributor = $request->attributes->get('uptd_contributor');

        abort_unless($contributor instanceof UptdContributor, 403);

        return $contributor;
    }
}
