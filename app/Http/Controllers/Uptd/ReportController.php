<?php

namespace App\Http\Controllers\Uptd;

use App\Enums\FieldCondition;
use App\Http\Controllers\Controller;
use App\Http\Requests\Uptd\ReportRequest;
use App\Models\FieldDistributionReport;
use App\Models\UptdContributor;
use App\Notifications\FieldDistributionReportCreated;
use App\Notifications\FieldDistributionReportDeleted;
use App\Notifications\FieldDistributionReportUpdated;
use App\Services\NotificationDispatcher;
use App\Services\UptdReportHistoryService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ReportController extends Controller
{
    public function __construct(private readonly NotificationDispatcher $notifications) {}

    /**
     * Tampilkan riwayat laporan kontributor UPTD Pasar.
     */
    public function index(Request $request, UptdReportHistoryService $history): View
    {
        return view('uptd.reports.index', $history->paginate($request, $this->contributor($request)));
    }

    /**
     * Tampilkan formulir input laporan kontributor UPTD Pasar.
     */
    public function create(Request $request): View
    {
        $contributor = $this->contributor($request)->loadMissing('market');

        return view('uptd.reports.create', [
            'contributor' => $contributor,
            'conditions' => FieldCondition::cases(),
        ]);
    }

    /**
     * Simpan laporan pengawasan milik kontributor UPTD Pasar.
     */
    public function store(ReportRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $contributor = $this->contributor($request)->loadMissing('market');

        DB::transaction(function () use ($contributor, $validated): void {
            $report = FieldDistributionReport::create([
                'market_id' => $contributor->market_id,
                'uptd_contributor_id' => $contributor->id,
                'report_date' => now()->toDateString(),
                'condition' => $validated['condition'],
                'description' => $validated['description'],
            ]);

            $this->notifications->sendToAdminAndKepalaDinas(
                new FieldDistributionReportCreated(
                    $report->report_date->format('d/m/Y'),
                    $contributor->market?->name ?? '—',
                    $contributor->name,
                    $report->id,
                ),
            );
        });

        return redirect()->route('uptd.dashboard')
            ->with('success', 'Laporan pengawasan berhasil disimpan.');
    }

    /**
     * Tampilkan formulir edit laporan milik kontributor UPTD Pasar.
     */
    public function edit(Request $request, FieldDistributionReport $report): View
    {
        $contributor = $this->contributor($request);

        $this->ensureOwnedBy($contributor, $report, 'update');

        $contributor->loadMissing('market');

        return view('uptd.reports.edit', [
            'report' => $report,
            'contributor' => $contributor,
            'conditions' => FieldCondition::cases(),
        ]);
    }

    /**
     * Perbarui laporan pengawasan milik kontributor UPTD Pasar.
     */
    public function update(ReportRequest $request, FieldDistributionReport $report): RedirectResponse
    {
        $contributor = $this->contributor($request)->loadMissing('market');

        $this->ensureOwnedBy($contributor, $report, 'update');

        $validated = $request->validated();

        $report->fill([
            'condition' => $validated['condition'],
            'description' => $validated['description'],
        ]);

        if ($report->isDirty()) {
            DB::transaction(function () use ($report, $contributor): void {
                $report->save();

                $this->notifications->sendToAdminAndKepalaDinas(
                    new FieldDistributionReportUpdated(
                        $report->report_date->format('d/m/Y'),
                        $contributor->market?->name ?? '—',
                        $contributor->name,
                        $report->id,
                    ),
                );
            });
        }

        return redirect()->route('uptd.reports.index')
            ->with('success', 'Laporan pengawasan berhasil diperbarui.');
    }

    /**
     * Hapus laporan pengawasan dan kirim notifikasi ke admin dan kepala dinas.
     */
    public function destroy(Request $request, FieldDistributionReport $report): RedirectResponse
    {
        $contributor = $this->contributor($request);

        $this->ensureOwnedBy($contributor, $report, 'delete');

        $report->loadMissing(['contributor:id,name', 'market:id,name']);

        $notification = new FieldDistributionReportDeleted(
            $report->report_date->format('d/m/Y'),
            $report->market?->name ?? '—',
            $report->contributor?->name ?? $contributor->name,
            $report->id,
        );

        DB::transaction(function () use ($report, $notification): void {
            $report->delete();

            $this->notifications->sendToAdminAndKepalaDinas($notification);
        });

        return redirect()->route('uptd.reports.index')
            ->with('success', 'Laporan pengawasan berhasil dihapus.');
    }

    /**
     * Pastikan laporan milik kontributor yang sedang dipilih.
     */
    private function ensureOwnedBy(UptdContributor $contributor, FieldDistributionReport $report, string $ability): void
    {
        abort_unless(Gate::allows($ability, [$report, $contributor]), 404);
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
