<?php

namespace App\Http\Controllers\Admin;

use App\Exports\SupervisionExport;
use App\Http\Controllers\Controller;
use App\Services\FieldSupervisionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SupervisionController extends Controller
{
    public function index(Request $request, FieldSupervisionService $supervision): View
    {
        return view('admin.supervision.index', $supervision->paginate($request));
    }

    public function exportExcel(Request $request, FieldSupervisionService $supervision): BinaryFileResponse
    {
        $data = $supervision->export($request);
        $filename = 'laporan-pengawasan-'.Str::slug($data['month_label']).'.xlsx';

        return Excel::download(new SupervisionExport($data['reports']), $filename);
    }

    public function exportPdf(Request $request, FieldSupervisionService $supervision): Response
    {
        $data = $supervision->export($request);
        $filename = 'laporan-pengawasan-'.Str::slug($data['month_label']).'.pdf';

        return Pdf::loadView('exports.supervision-pdf', $data)
            ->setPaper('a4', 'portrait')
            ->download($filename);
    }
}
