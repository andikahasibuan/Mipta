<?php

namespace App\Http\Controllers\Distributor;

use App\Http\Controllers\Controller;
use App\Models\Distribution;
use App\Notifications\DistributionReportDeleted;
use App\Services\DistributorReportHistoryService;
use App\Services\NotificationDispatcher;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ReportHistoryController extends Controller
{
    public function __construct(private readonly NotificationDispatcher $notifications) {}

    /**
     * Tampilkan riwayat laporan distributor.
     */
    public function index(Request $request, DistributorReportHistoryService $history): View
    {
        return view('distributor.report-history.index', $history->build(
            $request,
            $request->user()->distributor,
        ));
    }

    /**
     * Hapus laporan penyaluran dari riwayat distributor.
     */
    public function destroy(Request $request, Distribution $distribution): RedirectResponse
    {
        abort_unless(Gate::allows('delete', $distribution), 404);

        $distributionDate = $distribution->distribution_date->format('d/m/Y');
        $distributionId = $distribution->id;
        $distributorName = $request->user()->distributor->name;

        DB::transaction(function () use ($distribution, $distributionDate, $distributionId, $distributorName): void {
            $distribution->delete();

            $this->notifications->sendToAdminAndKepalaDinas(
                new DistributionReportDeleted($distributionDate, $distributorName, $distributionId),
            );
        });

        return redirect()->route('distributor.report-history.index')
            ->with('success', 'Riwayat laporan berhasil dihapus.');
    }
}
