<?php

namespace App\Http\Controllers\Distributor;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Distribution;
use App\Models\User;
use App\Notifications\DistributionReportDeleted;
use App\Services\DistributorReportHistoryService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class ReportHistoryController extends Controller
{
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
        abort_unless($distribution->distributor_id === $request->user()->distributor_id, 404);

        $reportCode = $distribution->code;
        $distributionDate = $distribution->distribution_date->format('d/m/Y');
        $distributorName = $request->user()->distributor->name;

        $distribution->delete();

        $recipients = User::query()
            ->whereIn('role', [Role::Admin->value, Role::KepalaDinas->value])
            ->where('is_active', true)
            ->get();

        if ($recipients->isNotEmpty()) {
            Notification::send(
                $recipients,
                new DistributionReportDeleted($reportCode, $distributionDate, $distributorName),
            );
        }

        return redirect()->route('distributor.report-history.index')
            ->with('success', 'Riwayat laporan berhasil dihapus.');
    }
}
