<?php

namespace App\Http\Controllers\Distributor;

use App\Enums\DistributionStatus;
use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Distribution;
use App\Models\RecipientStore;
use App\Models\User;
use App\Notifications\DistributionReportUpdated;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DistributionReportController extends Controller
{
    /**
     * Tampilkan formulir laporan penyaluran distributor.
     */
    public function create(Request $request): View
    {
        return view('distributor.distribution-reports.create', $this->formData($request));
    }

    /**
     * Simpan laporan penyaluran baru sebagai draft atau terkirim.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateReport($request);
        $store = $this->ownedStore($request, (int) $validated['recipient_store_id']);

        $report = Distribution::create([
            'distributor_id' => $request->user()->distributor_id,
            'market_id' => $store->market_id,
            'uptd_user_id' => null,
            'recipient_store_id' => $store->id,
            'distribution_date' => $validated['distribution_date'],
            'cartons' => $validated['cartons'],
            'status' => $this->statusFromAction($validated['action']),
        ]);

        if ($validated['action'] === 'draft') {
            return redirect()->route('distributor.distribution-reports.edit', $report)
                ->with('success', 'Draf laporan berhasil disimpan.');
        }

        return redirect()->route('distributor.dashboard')
            ->with('success', 'Laporan penyaluran berhasil dikirim.');
    }

    /**
     * Tampilkan formulir edit laporan penyaluran.
     */
    public function edit(Request $request, Distribution $distribution): View
    {
        $this->ensureOwned($request, $distribution);

        return view('distributor.distribution-reports.edit', $this->formData($request, $distribution));
    }

    /**
     * Perbarui laporan penyaluran yang sudah terkirim atau draft.
     */
    public function update(Request $request, Distribution $distribution): RedirectResponse
    {
        $this->ensureOwned($request, $distribution);

        $wasSubmitted = $distribution->status === DistributionStatus::Submitted;

        $validated = $this->validateReport($request);
        $store = $this->ownedStore($request, (int) $validated['recipient_store_id']);

        $distribution->fill([
            'market_id' => $store->market_id,
            'recipient_store_id' => $store->id,
            'distribution_date' => $validated['distribution_date'],
            'cartons' => $validated['cartons'],
            'status' => $wasSubmitted
                ? DistributionStatus::Submitted
                : $this->statusFromAction($validated['action']),
        ]);

        $changed = $distribution->isDirty();

        if ($changed) {
            $distribution->save();
        }

        if ($wasSubmitted) {
            if ($changed) {
                $this->notifyReportUpdated($request, $distribution);

                return redirect()->route('distributor.report-history.index')
                    ->with('success', 'Laporan penyaluran berhasil diperbarui.');
            }

            return redirect()->route('distributor.report-history.index');
        }

        if ($validated['action'] === 'draft') {
            return redirect()->route('distributor.distribution-reports.edit', $distribution)
                ->with('success', 'Draf laporan berhasil diperbarui.');
        }

        return redirect()->route('distributor.dashboard')
            ->with('success', 'Laporan penyaluran berhasil dikirim.');
    }

    /**
     * Data pendukung formulir laporan.
     *
     * @return array{stores: Collection<int, RecipientStore>, report: Distribution|null, drafts: Collection<int, Distribution>}
     */
    private function formData(Request $request, ?Distribution $report = null): array
    {
        $stores = RecipientStore::query()
            ->where('distributor_id', $request->user()->distributor_id)
            ->with('market:id,name')
            ->orderBy('name')
            ->get();

        $drafts = Distribution::query()
            ->where('distributor_id', $request->user()->distributor_id)
            ->where('status', DistributionStatus::Draft->value)
            ->when($report !== null, fn ($query) => $query->where('id', '!=', $report->id))
            ->with(['store:id,name'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        return [
            'stores' => $stores,
            'report' => $report,
            'drafts' => $drafts,
        ];
    }

    /**
     * Validasi input laporan penyaluran.
     *
     * @return array{distribution_date: string, recipient_store_id: int, cartons: int, action: string}
     */
    private function validateReport(Request $request): array
    {
        return $request->validate([
            'distribution_date' => ['required', 'date', 'before_or_equal:today'],
            'recipient_store_id' => ['required', 'integer', 'exists:recipient_stores,id'],
            'cartons' => ['required', 'integer', 'min:1'],
            'action' => ['required', Rule::in(['draft', 'submit'])],
        ]);
    }

    /**
     * Pastikan toko yang dipilih milik distributor yang sedang login.
     */
    private function ownedStore(Request $request, int $storeId): RecipientStore
    {
        $store = RecipientStore::query()
            ->whereKey($storeId)
            ->where('distributor_id', $request->user()->distributor_id)
            ->first();

        if ($store === null) {
            throw ValidationException::withMessages([
                'recipient_store_id' => 'Toko penerima tidak valid untuk distributor ini.',
            ]);
        }

        return $store;
    }

    /**
     * Pastikan laporan milik distributor yang sedang login.
     */
    private function ensureOwned(Request $request, Distribution $distribution): void
    {
        abort_unless($distribution->distributor_id === $request->user()->distributor_id, 404);
    }

    /**
     * Kirim notifikasi perubahan laporan terkirim ke admin dan kepala dinas.
     */
    private function notifyReportUpdated(Request $request, Distribution $distribution): void
    {
        $recipients = User::query()
            ->whereIn('role', [Role::Admin->value, Role::KepalaDinas->value])
            ->where('is_active', true)
            ->get();

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send(
            $recipients,
            new DistributionReportUpdated(
                $distribution->code,
                $distribution->distribution_date->format('d/m/Y'),
                $request->user()->distributor->name,
            ),
        );
    }

    private function statusFromAction(string $action): DistributionStatus
    {
        return $action === 'submit'
            ? DistributionStatus::Submitted
            : DistributionStatus::Draft;
    }
}
