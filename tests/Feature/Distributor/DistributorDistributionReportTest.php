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
function reportDistributorAccount(string $name = 'PT Distributor Laporan', string $nib = '1234567890123'): array
{
    $distributor = Distributor::factory()->create(['name' => $name]);
    $user = User::factory()->create([
        'role' => Role::Distributor,
        'distributor_id' => $distributor->id,
    ]);
    $market = Market::firstOrCreate(
        ['name' => 'Pasar Raya Padang'],
        ['lokasi' => 'Jln. Merdeka'],
    );
    $store = RecipientStore::factory()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'name' => 'Toko Contoh',
        'alamat' => 'Jln. Merdeka No. 1',
        'nib' => $nib,
    ]);

    return [$distributor, $user, $market, $store];
}

it('menampilkan formulir laporan penyaluran', function () {
    [, $user, , $store] = reportDistributorAccount();

    $this->actingAs($user)
        ->get(route('distributor.distribution-reports.create'))
        ->assertOk()
        ->assertSee('Laporan')
        ->assertSee('Laporan Penyaluran')
        ->assertSee('Tanggal Penyaluran')
        ->assertSee('Nama Toko')
        ->assertSee('Jumlah Penyaluran (karton)')
        ->assertDontSee('Keterangan')
        ->assertDontSee('store-market', false)
        ->assertDontSee('Simpan Draft')
        ->assertDontSee('Draf Tersimpan')
        ->assertSee('Kirim Laporan')
        ->assertSee('Panduan pengisian')
        ->assertSee($store->name)
        ->assertDontSee('Catat penyaluran Minyakita kepada toko penerima')
        ->assertSee('data-alamat="Pasar Raya Padang, Jln. Merdeka No. 1"', false)
        ->assertSee('data-nib="1234567890123"', false)
        ->assertSee('action="'.route('distributor.distribution-reports.store').'"', false)
        ->assertSee('value="'.now()->toDateString().'"', false)
        ->assertDontSee('MIPTA / MANAJEMEN LAPORAN PENYALURAN');
});

it('menampilkan arahan menambah toko saat distributor belum punya toko', function () {
    $distributor = Distributor::factory()->create();
    $user = User::factory()->create([
        'role' => Role::Distributor,
        'distributor_id' => $distributor->id,
    ]);

    $this->actingAs($user)
        ->get(route('distributor.distribution-reports.create'))
        ->assertOk()
        ->assertSee('Anda belum memiliki data toko.')
        ->assertSee(route('distributor.stores.create'), false);
});

it('mengirim laporan penyaluran dengan tanggal hari ini dan mengabaikan tanggal mundur', function () {
    [$distributor, $user, $market, $store] = reportDistributorAccount();

    $this->actingAs($user)
        ->post(route('distributor.distribution-reports.store'), [
            'distribution_date' => now()->subDays(3)->toDateString(),
            'recipient_store_id' => $store->id,
            'cartons' => 90,
        ])
        ->assertRedirect(route('distributor.dashboard'));

    $this->assertDatabaseHas('distributions', [
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'recipient_store_id' => $store->id,
        'cartons' => 90,
        'status' => DistributionStatus::Submitted->value,
    ]);

    expect(Distribution::query()->firstOrFail()->distribution_date->toDateString())
        ->toBe(now()->toDateString());
});

it('mengirim notifikasi laporan penyaluran baru ke admin dan kepala dinas', function () {
    [, $user, , $store] = reportDistributorAccount('PT Rajawali Nusantara');

    $admin = User::factory()->create(['role' => Role::Admin]);
    $kepalaDinas = User::factory()->create(['role' => Role::KepalaDinas]);
    $inactiveAdmin = User::factory()->inactive()->create(['role' => Role::Admin]);
    $uptd = User::factory()->create(['role' => Role::UptdPasar]);
    $otherDistributor = User::factory()->distributor()->create();

    $distributionDate = now()->toDateString();

    $this->actingAs($user)
        ->post(route('distributor.distribution-reports.store'), [
            'distribution_date' => $distributionDate,
            'recipient_store_id' => $store->id,
            'cartons' => 90,
        ])
        ->assertRedirect(route('distributor.dashboard'));

    $expectedMessage = 'Ada laporan baru dari PT Rajawali Nusantara.';

    $this->assertDatabaseCount('notifications', 2);

    expect($admin->notifications()->first()?->data['title'])->toBe('Laporan penyaluran baru')
        ->and($admin->notifications()->first()?->data['message'])->toBe($expectedMessage)
        ->and($kepalaDinas->notifications()->first()?->data['message'])->toBe($expectedMessage)
        ->and($inactiveAdmin->notifications()->count())->toBe(0)
        ->and($uptd->notifications()->count())->toBe(0)
        ->and($otherDistributor->notifications()->count())->toBe(0);
});

it('memvalidasi input laporan penyaluran', function () {
    [, $user] = reportDistributorAccount();

    $this->actingAs($user)
        ->post(route('distributor.distribution-reports.store'), [
            'recipient_store_id' => '',
            'cartons' => 0,
        ])
        ->assertSessionHasErrors(['recipient_store_id', 'cartons']);

    $this->assertDatabaseCount('distributions', 0);
});

it('menolak toko milik distributor lain', function () {
    [, $user] = reportDistributorAccount();
    [, , , $otherStore] = reportDistributorAccount('PT Distributor Lain', '1234567890124');

    $this->actingAs($user)
        ->post(route('distributor.distribution-reports.store'), [
            'distribution_date' => now()->toDateString(),
            'recipient_store_id' => $otherStore->id,
            'cartons' => 100,
        ])
        ->assertSessionHasErrors('recipient_store_id');

    $this->assertDatabaseCount('distributions', 0);
});

it('menampilkan form edit dan memperbarui laporan tanpa mengubah tanggal', function () {
    [$distributor, $user, $market, $store] = reportDistributorAccount();
    $originalDate = now()->subWeek()->toDateString();

    $report = Distribution::factory()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'recipient_store_id' => $store->id,
        'distribution_date' => $originalDate,
        'cartons' => 50,
    ]);

    $this->actingAs($user)
        ->get(route('distributor.distribution-reports.edit', $report))
        ->assertOk()
        ->assertSee('Laporan Penyaluran')
        ->assertSee($store->name)
        ->assertSee('value="50"', false)
        ->assertSee('value="'.$originalDate.'"', false);

    $this->put(route('distributor.distribution-reports.update', $report), [
        'distribution_date' => now()->toDateString(),
        'recipient_store_id' => $store->id,
        'cartons' => 75,
    ])
        ->assertRedirect(route('distributor.report-history.index'))
        ->assertSessionHas('success', 'Laporan penyaluran berhasil diperbarui.');

    expect($report->fresh()->cartons)->toBe(75)
        ->and($report->fresh()->distribution_date->toDateString())->toBe($originalDate)
        ->and($report->fresh()->status)->toBe(DistributionStatus::Submitted);
});

it('memperbarui laporan terkirim dan statusnya tetap terkirim', function () {
    [$distributor, $user, $market, $store] = reportDistributorAccount();

    $report = Distribution::factory()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'recipient_store_id' => $store->id,
        'distribution_date' => now()->toDateString(),
        'cartons' => 40,
    ]);

    $this->actingAs($user)
        ->put(route('distributor.distribution-reports.update', $report), [
            'distribution_date' => now()->toDateString(),
            'recipient_store_id' => $store->id,
            'cartons' => 90,
        ])
        ->assertRedirect(route('distributor.report-history.index'))
        ->assertSessionHas('success', 'Laporan penyaluran berhasil diperbarui.');

    expect($report->fresh()->cartons)->toBe(90)
        ->and($report->fresh()->status)->toBe(DistributionStatus::Submitted);
});

it('tidak mengizinkan edit laporan milik distributor lain', function () {
    [, $user, $market] = reportDistributorAccount();

    [, $otherUser] = reportDistributorAccount('PT Distributor Lain', '1234567890124');
    $otherReport = Distribution::factory()->create([
        'distributor_id' => $otherUser->distributor_id,
        'market_id' => $market->id,
        'distribution_date' => now()->toDateString(),
        'cartons' => 20,
    ]);

    $this->actingAs($user)
        ->get(route('distributor.distribution-reports.edit', $otherReport))
        ->assertNotFound();

    $this->put(route('distributor.distribution-reports.update', $otherReport), [
        'distribution_date' => now()->toDateString(),
        'recipient_store_id' => $otherReport->recipient_store_id,
        'cartons' => 10,
    ])->assertNotFound();
});
