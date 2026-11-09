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
function reportDistributorAccount(string $name = 'PT Distributor Laporan'): array
{
    $distributor = Distributor::factory()->create(['name' => $name]);
    $user = User::factory()->create([
        'role' => Role::Distributor,
        'distributor_id' => $distributor->id,
    ]);
    $market = Market::factory()->create(['name' => 'Pasar Raya Padang']);
    $store = RecipientStore::factory()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'name' => 'Toko Contoh',
        'alamat' => 'Jln. Merdeka No. 1',
        'nib' => '1234567890123',
    ]);

    return [$distributor, $user, $market, $store];
}

it('menampilkan formulir laporan penyaluran', function () {
    [, $user, , $store] = reportDistributorAccount();

    $this->actingAs($user)
        ->get(route('distributor.distribution-reports.create'))
        ->assertOk()
        ->assertSee('Laporan Penyaluran')
        ->assertSee('Formulir laporan penyaluran')
        ->assertSee('Tanggal Penyaluran')
        ->assertSee('Nama Toko')
        ->assertSee('Jumlah Penyaluran (karton)')
        ->assertSee('Simpan Draft')
        ->assertSee('Kirim Laporan')
        ->assertSee('Panduan pengisian')
        ->assertSee($store->name)
        ->assertDontSee('Catat penyaluran Minyakita kepada toko penerima')
        ->assertSee('data-market="Pasar Raya Padang"', false)
        ->assertSee('data-alamat="Jln. Merdeka No. 1"', false)
        ->assertSee('data-nib="1234567890123"', false)
        ->assertSee('action="'.route('distributor.distribution-reports.store').'"', false)
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
        ->assertSee('Anda belum memiliki data toko penerima')
        ->assertSee(route('distributor.stores.create'), false);
});

it('menyimpan draft laporan penyaluran', function () {
    [$distributor, $user, $market, $store] = reportDistributorAccount();

    $response = $this->actingAs($user)
        ->post(route('distributor.distribution-reports.store'), [
            'distribution_date' => now()->toDateString(),
            'recipient_store_id' => $store->id,
            'cartons' => 120,
            'action' => 'draft',
        ]);

    $report = Distribution::firstOrFail();

    $response->assertRedirect(route('distributor.distribution-reports.edit', $report));

    expect($report->distributor_id)->toBe($distributor->id)
        ->and($report->market_id)->toBe($market->id)
        ->and($report->recipient_store_id)->toBe($store->id)
        ->and($report->uptd_user_id)->toBeNull()
        ->and($report->cartons)->toBe(120)
        ->and($report->status)->toBe(DistributionStatus::Draft);
});

it('mengirim laporan penyaluran', function () {
    [$distributor, $user, $market, $store] = reportDistributorAccount();

    $this->actingAs($user)
        ->post(route('distributor.distribution-reports.store'), [
            'distribution_date' => now()->toDateString(),
            'recipient_store_id' => $store->id,
            'cartons' => 250,
            'action' => 'submit',
        ])
        ->assertRedirect(route('distributor.dashboard'));

    $this->assertDatabaseHas('distributions', [
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'recipient_store_id' => $store->id,
        'cartons' => 250,
        'status' => DistributionStatus::Submitted->value,
    ]);
});

it('memvalidasi input laporan penyaluran', function () {
    [, $user] = reportDistributorAccount();

    $this->actingAs($user)
        ->post(route('distributor.distribution-reports.store'), [
            'distribution_date' => now()->addDay()->toDateString(),
            'recipient_store_id' => '',
            'cartons' => 0,
            'action' => 'entah',
        ])
        ->assertSessionHasErrors(['distribution_date', 'recipient_store_id', 'cartons', 'action']);

    $this->assertDatabaseCount('distributions', 0);
});

it('menolak toko milik distributor lain', function () {
    [, $user] = reportDistributorAccount();
    [, , , $otherStore] = reportDistributorAccount('PT Distributor Lain');

    $this->actingAs($user)
        ->post(route('distributor.distribution-reports.store'), [
            'distribution_date' => now()->toDateString(),
            'recipient_store_id' => $otherStore->id,
            'cartons' => 100,
            'action' => 'submit',
        ])
        ->assertSessionHasErrors('recipient_store_id');

    $this->assertDatabaseCount('distributions', 0);
});

it('menampilkan form edit dan memperbarui draft', function () {
    [$distributor, $user, $market, $store] = reportDistributorAccount();

    $report = Distribution::factory()->draft()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'recipient_store_id' => $store->id,
        'distribution_date' => now()->toDateString(),
        'cartons' => 50,
    ]);

    $this->actingAs($user)
        ->get(route('distributor.distribution-reports.edit', $report))
        ->assertOk()
        ->assertSee('Formulir laporan penyaluran')
        ->assertSee($store->name)
        ->assertSee('value="50"', false);

    $this->put(route('distributor.distribution-reports.update', $report), [
        'distribution_date' => now()->toDateString(),
        'recipient_store_id' => $store->id,
        'cartons' => 75,
        'action' => 'draft',
    ])->assertRedirect(route('distributor.distribution-reports.edit', $report));

    expect($report->fresh()->cartons)->toBe(75)
        ->and($report->fresh()->status)->toBe(DistributionStatus::Draft);
});

it('mengirim draft menjadi laporan terkirim', function () {
    [$distributor, $user, $market, $store] = reportDistributorAccount();

    $report = Distribution::factory()->draft()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'recipient_store_id' => $store->id,
        'distribution_date' => now()->toDateString(),
        'cartons' => 60,
    ]);

    $this->actingAs($user)
        ->put(route('distributor.distribution-reports.update', $report), [
            'distribution_date' => now()->toDateString(),
            'recipient_store_id' => $store->id,
            'cartons' => 60,
            'action' => 'submit',
        ])
        ->assertRedirect(route('distributor.dashboard'));

    expect($report->fresh()->status)->toBe(DistributionStatus::Submitted);

    $this->get(route('distributor.distribution-reports.edit', $report))->assertNotFound();
});

it('tidak mengizinkan edit laporan terkirim atau milik distributor lain', function () {
    [$distributor, $user, $market, $store] = reportDistributorAccount();

    $submitted = Distribution::factory()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'recipient_store_id' => $store->id,
        'distribution_date' => now()->toDateString(),
        'cartons' => 10,
    ]);

    [, $otherUser] = reportDistributorAccount('PT Distributor Lain');
    $otherDraft = Distribution::factory()->draft()->create([
        'distributor_id' => $otherUser->distributor_id,
        'market_id' => $market->id,
        'distribution_date' => now()->toDateString(),
        'cartons' => 20,
    ]);

    $this->actingAs($user)
        ->get(route('distributor.distribution-reports.edit', $submitted))
        ->assertNotFound();

    $this->get(route('distributor.distribution-reports.edit', $otherDraft))
        ->assertNotFound();
});

it('menampilkan daftar draft tersimpan pada formulir', function () {
    [$distributor, $user, $market, $store] = reportDistributorAccount();

    Distribution::factory()->draft()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'recipient_store_id' => $store->id,
        'distribution_date' => now()->toDateString(),
        'cartons' => 30,
    ]);

    $this->actingAs($user)
        ->get(route('distributor.distribution-reports.create'))
        ->assertOk()
        ->assertSee('Draf Tersimpan')
        ->assertSee($store->name)
        ->assertSee('Edit draft');
});
