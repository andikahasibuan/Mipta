<?php

namespace App\Http\Controllers\Uptd;

use App\Enums\FieldCondition;
use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\FieldDistributionReport;
use App\Models\User;
use App\Notifications\FieldDistributionReportDeleted;
use App\Services\UptdReportHistoryService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
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
     * Simpan laporan kondisi lapangan milik kontributor UPTD Pasar.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateReport($request);
        $contributor = $this->contributor($request);

        FieldDistributionReport::create([
            'market_id' => $contributor->market_id,
            'uptd_user_id' => $contributor->id,
            'report_date' => $validated['report_date'],
            'condition' => $validated['condition'],
            'description' => $validated['description'],
        ]);

        return redirect()->route('uptd.dashboard')
            ->with('success', 'Laporan kondisi lapangan berhasil disimpan.');
    }

    /**
     * Tampilkan formulir edit laporan milik kontributor UPTD Pasar.
     */
    public function edit(Request $request, FieldDistributionReport $report): View
    {
        $contributor = $this->contributor($request);

        $this->ensureOwnedBy($contributor, $report);

        $contributor->loadMissing('market');

        return view('uptd.reports.edit', [
            'report' => $report,
            'contributor' => $contributor,
            'conditions' => FieldCondition::cases(),
        ]);
    }

    /**
     * Perbarui laporan kondisi lapangan milik kontributor UPTD Pasar.
     */
    public function update(Request $request, FieldDistributionReport $report): RedirectResponse
    {
        $this->ensureOwnedBy($this->contributor($request), $report);

        $validated = $this->validateReport($request);

        $report->update([
            'report_date' => $validated['report_date'],
            'condition' => $validated['condition'],
            'description' => $validated['description'],
        ]);

        return redirect()->route('uptd.reports.index')
            ->with('success', 'Laporan kondisi lapangan berhasil diperbarui.');
    }

    /**
     * Hapus laporan kondisi lapangan dan kirim notifikasi ke admin.
     */
    public function destroy(Request $request, FieldDistributionReport $report): RedirectResponse
    {
        $contributor = $this->contributor($request);

        $this->ensureOwnedBy($contributor, $report);

        $report->loadMissing(['contributor:id,name', 'market:id,name']);

        $recipients = User::query()
            ->where('role', Role::Admin->value)
            ->where('is_active', true)
            ->get();

        $notification = new FieldDistributionReportDeleted(
            $report->report_date->format('d/m/Y'),
            $report->market?->name ?? '—',
            $report->contributor?->name ?? $contributor->name,
        );

        $report->delete();

        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, $notification);
        }

        return redirect()->route('uptd.reports.index')
            ->with('success', 'Laporan kondisi lapangan berhasil dihapus.');
    }

    /**
     * Validasi input laporan kondisi lapangan.
     *
     * @return array{report_date: string, condition: string, description: string}
     */
    private function validateReport(Request $request): array
    {
        return $request->validate([
            'report_date' => ['required', 'date', 'before_or_equal:today'],
            'condition' => ['required', Rule::enum(FieldCondition::class)],
            'description' => ['required', 'string', 'max:120'],
        ]);
    }

    /**
     * Pastikan laporan milik kontributor yang sedang dipilih.
     */
    private function ensureOwnedBy(User $contributor, FieldDistributionReport $report): void
    {
        abort_unless($report->uptd_user_id === $contributor->id, 404);
    }

    /**
     * Ambil kontributor aktif yang dipilih pada session.
     */
    private function contributor(Request $request): User
    {
        $contributor = $request->attributes->get('uptd_contributor');

        abort_unless($contributor instanceof User, 403);

        return $contributor;
    }
}
