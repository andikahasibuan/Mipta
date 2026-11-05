<?php

use App\Enums\Role;
use App\Models\Distribution;
use App\Models\Distributor;
use App\Models\Market;
use App\Models\RecipientStore;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('mengarahkan tamu dari laporan penyaluran ke halaman login', function () {
    $this->get(route('admin.distribution.index'))->assertRedirect(route('login'));
});

it('menampilkan laporan penyaluran dari database', function () {
    $uptd = User::factory()->create([
        'name' => 'Andi Pratama',
        'role' => Role::UptdPasar,
    ]);
    $distributor = Distributor::factory()->create(['name' => 'PT Sinar Pangan Nusantara']);
    $market = Market::factory()->create(['name' => 'Pasar Raya Padang']);

    Distribution::factory()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'uptd_user_id' => $uptd->id,
        'distribution_date' => now()->toDateString(),
        'cartons' => 250,
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.distribution.index'))
        ->assertOk()
        ->assertSee('Laporan Penyaluran')
        ->assertDontSee('Tambah Data')
        ->assertSee('PT Sinar Pangan Nusantara')
        ->assertSee('Pasar Raya Padang')
        ->assertSee('250 karton')
        ->assertSee(now()->format('d/m/Y'));
});

it('memfilter laporan penyaluran berdasarkan distributor, pasar, dan bulan', function () {
    $firstDistributor = Distributor::factory()->create(['name' => 'PT Alpha Niaga']);
    $secondDistributor = Distributor::factory()->create(['name' => 'PT Beta Pangan']);
    $firstMarket = Market::factory()->create(['name' => 'Pasar Alpha']);
    $secondMarket = Market::factory()->create(['name' => 'Pasar Beta']);
    $uptd = User::factory()->create(['role' => Role::UptdPasar]);

    Distribution::factory()->create([
        'distributor_id' => $firstDistributor->id,
        'market_id' => $firstMarket->id,
        'uptd_user_id' => $uptd->id,
        'distribution_date' => now()->toDateString(),
        'cartons' => 100,
    ]);

    Distribution::factory()->create([
        'distributor_id' => $secondDistributor->id,
        'market_id' => $secondMarket->id,
        'uptd_user_id' => $uptd->id,
        'distribution_date' => now()->toDateString(),
        'cartons' => 200,
    ]);

    Distribution::factory()->create([
        'distributor_id' => $firstDistributor->id,
        'market_id' => $firstMarket->id,
        'uptd_user_id' => $uptd->id,
        'distribution_date' => now()->subMonthNoOverflow()->startOfMonth()->toDateString(),
        'cartons' => 300,
    ]);

    $admin = User::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.distribution.index', ['distributor' => $firstDistributor->id]))
        ->assertOk()
        ->assertSee('value="'.$firstDistributor->id.'" selected', false)
        ->assertSee('100 karton')
        ->assertDontSee('200 karton')
        ->assertDontSee('300 karton');

    $this->get(route('admin.distribution.index', ['market' => $secondMarket->id]))
        ->assertOk()
        ->assertSee('value="'.$secondMarket->id.'" selected', false)
        ->assertSee('200 karton')
        ->assertDontSee('100 karton');

    $this->get(route('admin.distribution.index', [
        'month' => now()->subMonthNoOverflow()->format('Y-m'),
    ]))
        ->assertOk()
        ->assertSee('300 karton')
        ->assertDontSee('100 karton');
});

it('menampilkan keadaan kosong saat belum ada laporan penyaluran', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.distribution.index'))
        ->assertOk()
        ->assertSee('Belum ada laporan penyaluran pada periode ini');
});

it('menampilkan nama toko penerima pada laporan penyaluran', function () {
    $store = RecipientStore::factory()->create(['name' => 'Toko Atong']);

    Distribution::factory()->create([
        'market_id' => $store->market_id,
        'recipient_store_id' => $store->id,
        'distribution_date' => now()->toDateString(),
        'cartons' => 150,
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.distribution.index'))
        ->assertOk()
        ->assertSee('Toko Atong');
});

it('menghapus laporan penyaluran dari database', function () {
    $this->actingAs(User::factory()->create());
    $distribution = Distribution::factory()->create();

    $this->delete(route('admin.distribution.destroy', $distribution))
        ->assertRedirect(route('admin.distribution.index'));

    $this->assertDatabaseMissing('distributions', ['id' => $distribution->id]);
});
