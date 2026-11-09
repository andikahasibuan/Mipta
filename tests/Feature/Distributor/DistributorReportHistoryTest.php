<?php

use App\Enums\DistributionStatus;
use App\Enums\Role;
use App\Models\Distribution;
use App\Models\Distributor;
use App\Models\Market;
use App\Models\RecipientStore;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Buat distributor, akun login, pasar, dan toko milik distributor.
 *
 * @return array{0: Distributor, 1: User, 2: Market, 3: RecipientStore}
 */
function reportHistoryAccount(string $name = 'PT Riwayat Penyaluran'): array
{
    $distributor = Distributor::factory()->create(['name' => $name]);
    $user = User::factory()->create([
        'role' => Role::Distributor,
        'distributor_id' => $distributor->id,
    ]);
    $market = Market::factory()->create(['name' => 'Pasar Riwayat']);
    $store = RecipientStore::factory()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'name' => 'Toko Riwayat',
    ]);

    return [$distributor, $user, $market, $store];
}

it('menampilkan riwayat laporan milik distributor dengan kolom lengkap', function () {
    [$distributor, $user, $market, $store] = reportHistoryAccount();
    [, $otherUser] = reportHistoryAccount('PT Distributor Lain');

    $report = Distribution::factory()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'recipient_store_id' => $store->id,
        'distribution_date' => now()->startOfMonth()->toDateString(),
        'cartons' => 120,
    ]);

    Distribution::factory()->draft()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'recipient_store_id' => $store->id,
        'distribution_date' => now()->startOfMonth()->toDateString(),
        'cartons' => 80,
    ]);

    Distribution::factory()->create([
        'distributor_id' => $otherUser->distributor_id,
        'market_id' => $market->id,
        'distribution_date' => now()->startOfMonth()->toDateString(),
        'cartons' => 999,
    ]);

    $this->actingAs($user)
        ->get(route('distributor.report-history.index'))
        ->assertOk()
        ->assertSee('Riwayat Laporan Penyaluran')
        ->assertSee('Daftar laporan penyaluran yang pernah dibuat oleh distributor')
        ->assertSeeInOrder(['ID Laporan', 'Tanggal', 'Nama Toko', 'Pasar', 'Jumlah Karton', 'Status Laporan', 'Aksi'])
        ->assertSee($report->code)
        ->assertSee('Toko Riwayat')
        ->assertSee('Pasar Riwayat')
        ->assertSee('120 karton')
        ->assertSee('80 karton')
        ->assertSee('Terkirim')
        ->assertSee('Draf')
        ->assertDontSee('999 karton')
        ->assertSee(route('distributor.report-history.destroy', $report), false)
        ->assertSee('value="'.now()->format('Y-m').'" selected', false);
});

it('memfilter riwayat laporan berdasarkan bulan, status, dan nama toko', function () {
    [$distributor, $user, $market, $store] = reportHistoryAccount();
    $this->actingAs($user);

    $previousMonth = now()->subMonthNoOverflow()->startOfMonth();

    $currentSubmitted = Distribution::factory()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'recipient_store_id' => $store->id,
        'distribution_date' => now()->startOfMonth()->toDateString(),
        'cartons' => 111,
    ]);

    $currentDraft = Distribution::factory()->draft()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'recipient_store_id' => $store->id,
        'distribution_date' => now()->startOfMonth()->addDay()->toDateString(),
        'cartons' => 222,
    ]);

    Distribution::factory()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'recipient_store_id' => $store->id,
        'distribution_date' => $previousMonth->toDateString(),
        'cartons' => 333,
    ]);

    $otherStore = RecipientStore::factory()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'name' => 'Toko Berbeda',
    ]);

    Distribution::factory()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'recipient_store_id' => $otherStore->id,
        'distribution_date' => now()->startOfMonth()->toDateString(),
        'cartons' => 444,
    ]);

    $this->get(route('distributor.report-history.index'))
        ->assertOk()
        ->assertSee('111 karton')
        ->assertSee('222 karton')
        ->assertSee('444 karton')
        ->assertDontSee('333 karton');

    $this->get(route('distributor.report-history.index', ['month' => 'all']))
        ->assertOk()
        ->assertSee('333 karton')
        ->assertSee('value="all" selected', false);

    $this->get(route('distributor.report-history.index', [
        'month' => $previousMonth->format('Y-m'),
    ]))
        ->assertOk()
        ->assertSee('333 karton')
        ->assertDontSee('111 karton');

    $this->get(route('distributor.report-history.index', ['status' => DistributionStatus::Draft->value]))
        ->assertOk()
        ->assertSee($currentDraft->code)
        ->assertSee('222 karton')
        ->assertDontSee($currentSubmitted->code);

    $this->get(route('distributor.report-history.index', ['search' => 'Berbeda']))
        ->assertOk()
        ->assertSee('444 karton')
        ->assertDontSee('111 karton')
        ->assertDontSee('222 karton');
});

it('menampilkan sepuluh riwayat laporan per halaman dengan pagination', function () {
    [$distributor, $user, $market, $store] = reportHistoryAccount();

    foreach (range(1, 11) as $index) {
        Distribution::factory()->create([
            'distributor_id' => $distributor->id,
            'market_id' => $market->id,
            'recipient_store_id' => $store->id,
            'distribution_date' => now()->startOfMonth()->toDateString(),
            'cartons' => 10,
        ]);
    }

    $this->actingAs($user)
        ->get(route('distributor.report-history.index'))
        ->assertOk()
        ->assertSee('Menampilkan 10 dari 11 laporan')
        ->assertSee('?page=2', false)
        ->assertSee('LP-011')
        ->assertDontSee('LP-001');

    $this->get(route('distributor.report-history.index', ['page' => 2]))
        ->assertOk()
        ->assertSee('LP-001')
        ->assertDontSee('LP-011');
});

it('menghapus laporan draft dan laporan terkirim milik sendiri', function () {
    [$distributor, $user, $market, $store] = reportHistoryAccount();

    $draft = Distribution::factory()->draft()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'recipient_store_id' => $store->id,
        'distribution_date' => now()->startOfMonth()->toDateString(),
        'cartons' => 50,
    ]);

    $submitted = Distribution::factory()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'recipient_store_id' => $store->id,
        'distribution_date' => now()->startOfMonth()->toDateString(),
        'cartons' => 60,
    ]);

    $this->actingAs($user)
        ->delete(route('distributor.report-history.destroy', $draft))
        ->assertRedirect(route('distributor.report-history.index'));

    $this->delete(route('distributor.report-history.destroy', $submitted))
        ->assertRedirect(route('distributor.report-history.index'));

    $this->assertDatabaseMissing('distributions', ['id' => $draft->id]);
    $this->assertDatabaseMissing('distributions', ['id' => $submitted->id]);
});

it('menolak menghapus riwayat laporan milik distributor lain', function () {
    [, $user] = reportHistoryAccount();
    [, $otherUser, $otherMarket] = reportHistoryAccount('PT Distributor Lain');

    $otherReport = Distribution::factory()->create([
        'distributor_id' => $otherUser->distributor_id,
        'market_id' => $otherMarket->id,
        'distribution_date' => now()->startOfMonth()->toDateString(),
    ]);

    $this->actingAs($user)
        ->delete(route('distributor.report-history.destroy', $otherReport))
        ->assertNotFound();

    $this->assertDatabaseHas('distributions', ['id' => $otherReport->id]);
});

it('mengirim notifikasi penghapusan ke admin dan kepala dinas', function () {
    [$distributor, $user, $market, $store] = reportHistoryAccount('PT Rajawali Nusantara');

    $admin = User::factory()->create(['role' => Role::Admin]);
    $kepalaDinas = User::factory()->create(['role' => Role::KepalaDinas]);
    $uptd = User::factory()->create(['role' => Role::UptdPasar]);
    $otherDistributor = User::factory()->distributor()->create();

    $report = Distribution::factory()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'recipient_store_id' => $store->id,
        'distribution_date' => now()->startOfMonth()->toDateString(),
        'cartons' => 75,
    ]);

    $expectedMessage = sprintf(
        'Laporan %s tanggal %s telah dihapus oleh PT Rajawali Nusantara.',
        $report->code,
        $report->distribution_date->format('d/m/Y'),
    );

    $this->actingAs($user)
        ->delete(route('distributor.report-history.destroy', $report))
        ->assertRedirect(route('distributor.report-history.index'));

    $this->assertDatabaseCount('notifications', 2);

    expect($admin->notifications()->first()?->data['message'])->toBe($expectedMessage)
        ->and($kepalaDinas->notifications()->first()?->data['message'])->toBe($expectedMessage)
        ->and($user->notifications()->count())->toBe(0)
        ->and($uptd->notifications()->count())->toBe(0)
        ->and($otherDistributor->notifications()->count())->toBe(0);
});
