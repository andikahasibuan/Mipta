<?php

namespace App\Http\Controllers\KepalaDinas;

use App\Exports\DistributionExport;
use App\Http\Controllers\Controller;
use App\Services\DistributionReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DistributionController extends Controller
{
    public function index(Request $request, DistributionReportService $distribution): View
    {
        return view('kepala-dinas.distribution.index', $distribution->paginate($request));
    }

    public function exportExcel(Request $request, DistributionReportService $distribution): BinaryFileResponse
    {
        $data = $distribution->export($request);
        $filename = 'laporan-penyaluran-'.Str::slug($data['month_label']).'.xlsx';

        return Excel::download(new DistributionExport($data['distributions']), $filename);
    }

    public function exportPdf(Request $request, DistributionReportService $distribution): Response
    {
        $data = $distribution->export($request);
        $filename = 'laporan-penyaluran-'.Str::slug($data['month_label']).'.pdf';

        return Pdf::loadView('exports.distribution-pdf', $data)
            ->setPaper('a4', 'landscape')
            ->download($filename);
    }
}