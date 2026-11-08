<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Distribution;
use App\Services\DistributionReportService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DistributionController extends Controller
{
    /**
     * Tampilkan halaman laporan penyaluran.
     */
    public function index(Request $request, DistributionReportService $distribution): View
    {
        return view('admin.distribution.index', $distribution->paginate($request));
    }

    /**
     * Hapus data penyaluran.
     */
    public function destroy(Distribution $distribution): RedirectResponse
    {
        $distribution->delete();

        return redirect()->route('admin.distribution.index')->with('success', 'Data penyaluran berhasil dihapus.');
    }
}
