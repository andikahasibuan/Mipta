<?php

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
        'notes' => 'Pengiriman lengkap.',
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
        ->assertSeeInOrder(['NO', 'Tanggal', 'Nama Toko', 'Pasar', 'Jumlah Karton', 'Keterangan', 'Aksi'])
        ->assertSee('Toko Riwayat')
        ->assertSee('Pasar Riwayat')
        ->assertSee('120 karton')
        ->assertSee('Pengiriman lengkap.')
        ->assertDontSee('999 karton')
        ->assertSee(route('distributor.report-history.destroy', $report), false)
        ->assertSee(route('distributor.distribution-reports.edit', $report), false)
        ->assertSee('value="'.now()->format('Y-m').'" selected', false);
});

it('memfilter riwayat laporan berdasarkan bulan dan nama toko', function () {
    [$distributor, $user, $market, $store] = reportHistoryAccount();
    $this->actingAs($user);

    $previousMonth = now()->subMonthNoOverflow()->startOfMonth();

    Distribution::factory()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'recipient_store_id' => $store->id,
        'distribution_date' => now()->startOfMonth()->toDateString(),
        'cartons' => 111,
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

    $this->get(route('distributor.report-history.index', ['search' => 'Berbeda']))
        ->assertOk()
        ->assertSee('444 karton')
        ->assertDontSee('111 karton');
});

it('menampilkan sepuluh riwayat laporan per halaman dengan pagination', function () {
    [$distributor, $user, $market, $store] = reportHistoryAccount();

    foreach (range(1, 11) as $index) {
        Distribution::factory()->create([
            'distributor_id' => $distributor->id,
            'market_id' => $market->id,
            'recipient_store_id' => $store->id,
            'distribution_date' => now()->startOfMonth()->toDateString(),
            'cartons' => 100 + $index,
        ]);
    }

    $this->actingAs($user)
        ->get(route('distributor.report-history.index'))
        ->assertOk()
        ->assertSee('Menampilkan 10 dari 11 laporan')
        ->assertSee('?page=2', false)
        ->assertSee('110 karton')
        ->assertDontSee('111 karton');

    $this->get(route('distributor.report-history.index', ['page' => 2]))
        ->assertOk()
        ->assertSee('111 karton')
        ->assertDontSee('110 karton');
});

it('menghapus laporan terkirim milik sendiri', function () {
    [$distributor, $user, $market, $store] = reportHistoryAccount();

    $submitted = Distribution::factory()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'recipient_store_id' => $store->id,
        'distribution_date' => now()->startOfMonth()->toDateString(),
        'cartons' => 60,
    ]);

    $this->actingAs($user)
        ->delete(route('distributor.report-history.destroy', $submitted))
        ->assertRedirect(route('distributor.report-history.index'));

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
        'Laporan penyaluran tanggal %s telah dihapus oleh PT Rajawali Nusantara.',
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

it('mengirim notifikasi perubahan ke admin dan kepala dinas saat laporan terkirim diedit', function () {
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
        'Laporan penyaluran tanggal %s telah diperbarui oleh PT Rajawali Nusantara.',
        $report->distribution_date->format('d/m/Y'),
    );

    $this->actingAs($user)
        ->put(route('distributor.distribution-reports.update', $report), [
            'distribution_date' => $report->distribution_date->toDateString(),
            'recipient_store_id' => $store->id,
            'cartons' => 90,
        ])
        ->assertRedirect(route('distributor.report-history.index'));

    $this->assertDatabaseCount('notifications', 2);

    expect($admin->notifications()->first()?->data['message'])->toBe($expectedMessage)
        ->and($kepalaDinas->notifications()->first()?->data['message'])->toBe($expectedMessage)
        ->and($user->notifications()->count())->toBe(0)
        ->and($uptd->notifications()->count())->toBe(0)
        ->and($otherDistributor->notifications()->count())->toBe(0);
});

it('tidak mengirim notifikasi saat laporan terkirim diedit tanpa perubahan', function () {
    [$distributor, $user, $market, $store] = reportHistoryAccount();

    User::factory()->create(['role' => Role::Admin]);

    $report = Distribution::factory()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'recipient_store_id' => $store->id,
        'distribution_date' => now()->startOfMonth()->toDateString(),
        'cartons' => 75,
    ]);

    $this->actingAs($user)
        ->put(route('distributor.distribution-reports.update', $report), [
            'distribution_date' => $report->distribution_date->toDateString(),
            'recipient_store_id' => $store->id,
            'cartons' => 75,
        ])
        ->assertRedirect(route('distributor.report-history.index'))
        ->assertSessionMissing('success');

    $this->assertDatabaseCount('notifications', 0);
});
