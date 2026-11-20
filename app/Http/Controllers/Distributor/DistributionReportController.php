<?php

namespace App\Http\Controllers\Distributor;

use App\Enums\DistributionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Distributor\DistributionReportRequest;
use App\Models\Distribution;
use App\Models\RecipientStore;
use App\Notifications\DistributionReportCreated;
use App\Notifications\DistributionReportUpdated;
use App\Services\NotificationDispatcher;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class DistributionReportController extends Controller
{
    public function __construct(private readonly NotificationDispatcher $notifications) {}

    /**
     * Tampilkan formulir laporan penyaluran distributor.
     */
    public function create(Request $request): View
    {
        return view('distributor.distribution-reports.create', $this->formData($request));
    }

    /**
     * Simpan laporan penyaluran baru.
     */
    public function store(DistributionReportRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $store = $this->ownedStore($request, (int) $validated['recipient_store_id']);
        $distributorName = $request->user()->distributor->name;

        DB::transaction(function () use ($request, $validated, $store, $distributorName): void {
            $distribution = Distribution::create([
                'distributor_id' => $request->user()->distributor_id,
                'market_id' => $store->market_id,
                'uptd_user_id' => null,
                'recipient_store_id' => $store->id,
                'distribution_date' => now()->toDateString(),
                'cartons' => $validated['cartons'],
                'status' => DistributionStatus::Submitted,
            ]);

            $this->notifications->sendToAdminAndKepalaDinas(
                new DistributionReportCreated(
                    $distribution->distribution_date->format('d/m/Y'),
                    $distributorName,
                    $distribution->id,
                ),
            );
        });

        return redirect()->route('distributor.dashboard')
            ->with('success', 'Laporan penyaluran berhasil dikirim.');
    }

    /**
     * Tampilkan formulir edit laporan penyaluran.
     */
    public function edit(Request $request, Distribution $distribution): View
    {
        $this->ensureOwned($distribution);

        return view('distributor.distribution-reports.edit', $this->formData($request, $distribution));
    }

    /**
     * Perbarui laporan penyaluran yang sudah terkirim.
     */
    public function update(DistributionReportRequest $request, Distribution $distribution): RedirectResponse
    {
        $validated = $request->validated();
        $store = $this->ownedStore($request, (int) $validated['recipient_store_id']);

        $distribution->fill([
            'market_id' => $store->market_id,
            'recipient_store_id' => $store->id,
            'cartons' => $validated['cartons'],
        ]);

        if ($distribution->isDirty()) {
            DB::transaction(function () use ($request, $distribution): void {
                $distribution->save();
                $this->notifyReportUpdated($request, $distribution);
            });

            return redirect()->route('distributor.report-history.index')
                ->with('success', 'Laporan penyaluran berhasil diperbarui.');
        }

        return redirect()->route('distributor.report-history.index');
    }

    /**
     * Data pendukung formulir laporan.
     *
     * @return array{stores: Collection<int, RecipientStore>, report: Distribution|null}
     */
    private function formData(Request $request, ?Distribution $report = null): array
    {
        $stores = RecipientStore::query()
            ->where('distributor_id', $request->user()->distributor_id)
            ->with('market:id,name')
            ->orderBy('name')
            ->get();

        return [
            'stores' => $stores,
            'report' => $report,
        ];
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
    private function ensureOwned(Distribution $distribution): void
    {
        abort_unless(Gate::allows('update', $distribution), 404);
    }

    /**
     * Kirim notifikasi perubahan laporan terkirim ke admin dan kepala dinas.
     */
    private function notifyReportUpdated(Request $request, Distribution $distribution): void
    {
        $this->notifications->sendToAdminAndKepalaDinas(
            new DistributionReportUpdated(
                $distribution->distribution_date->format('d/m/Y'),
                $request->user()->distributor->name,
                $distribution->id,
            ),
        );
    }
}
