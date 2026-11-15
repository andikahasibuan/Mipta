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
        ->assertSee('LP-001')
        ->assertDontSee('Tambah Data')
        ->assertSee('PT Sinar Pangan Nusantara')
        ->assertSee('Pasar Raya Padang')
        ->assertSee('250 karton')
        ->assertSee(now()->format('d/m/Y'));
});

it('menampilkan tombol hapus pada laporan penyaluran admin', function () {
    Distribution::factory()->create([
        'distribution_date' => now()->toDateString(),
        'cartons' => 120,
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.distribution.index'))
        ->assertOk()
        ->assertSee('aria-label="Hapus"', false);
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

it('mengabaikan bulan tidak valid dan kembali ke bulan berjalan', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('admin.distribution.index', ['month' => 'bukan-bulan']))
        ->assertOk()
        ->assertViewHas('filters', fn (array $filters): bool => $filters['month'] === now()->format('Y-m'))
        ->assertSee('value="'.now()->format('Y-m').'" selected', false);
});

it('mencari laporan penyaluran berdasarkan kode, toko, distributor, dan pasar', function () {
    $uptd = User::factory()->create(['role' => Role::UptdPasar]);
    $alphaDistributor = Distributor::factory()->create(['name' => 'PT Alpha Niaga']);
    $betaDistributor = Distributor::factory()->create(['name' => 'PT Beta Pangan']);
    $alphaMarket = Market::factory()->create(['name' => 'Pasar Alpha']);
    $betaMarket = Market::factory()->create(['name' => 'Pasar Beta']);
    $alphaStore = RecipientStore::factory()->create(['name' => 'Toko Alpha', 'market_id' => $alphaMarket->id]);
    $betaStore = RecipientStore::factory()->create(['name' => 'Toko Beta', 'market_id' => $betaMarket->id]);

    Distribution::factory()->create([
        'distributor_id' => $alphaDistributor->id,
        'market_id' => $alphaMarket->id,
        'recipient_store_id' => $alphaStore->id,
        'uptd_user_id' => $uptd->id,
        'distribution_date' => now()->toDateString(),
        'cartons' => 111,
    ]);

    $betaDistribution = Distribution::factory()->create([
        'distributor_id' => $betaDistributor->id,
        'market_id' => $betaMarket->id,
        'recipient_store_id' => $betaStore->id,
        'uptd_user_id' => $uptd->id,
        'distribution_date' => now()->toDateString(),
        'cartons' => 222,
    ]);

    $this->actingAs(User::factory()->create());

    $this->get(route('admin.distribution.index', ['search' => 'Toko Alpha']))
        ->assertOk()
        ->assertSee('111 karton')
        ->assertDontSee('222 karton');

    $this->get(route('admin.distribution.index', ['search' => 'PT Beta Pangan']))
        ->assertOk()
        ->assertSee('222 karton')
        ->assertDontSee('111 karton');

    $this->get(route('admin.distribution.index', ['search' => 'Pasar Alpha']))
        ->assertOk()
        ->assertSee('111 karton')
        ->assertDontSee('222 karton');

    $this->get(route('admin.distribution.index', ['search' => $betaDistribution->code]))
        ->assertOk()
        ->assertSee('222 karton')
        ->assertDontSee('111 karton');
});

it('menampilkan ringkasan total karton, jumlah toko penerima, dan jumlah laporan sesuai filter', function () {
    $uptd = User::factory()->create(['role' => Role::UptdPasar]);
    $firstDistributor = Distributor::factory()->create(['name' => 'PT Alpha Niaga']);
    $secondDistributor = Distributor::factory()->create(['name' => 'PT Beta Pangan']);
    $firstStore = RecipientStore::factory()->create(['name' => 'Toko Alpha']);
    $secondStore = RecipientStore::factory()->create(['name' => 'Toko Beta']);

    Distribution::factory()->create([
        'distributor_id' => $firstDistributor->id,
        'market_id' => $firstStore->market_id,
        'recipient_store_id' => $firstStore->id,
        'uptd_user_id' => $uptd->id,
        'distribution_date' => now()->toDateString(),
        'cartons' => 120,
    ]);

    Distribution::factory()->create([
        'distributor_id' => $secondDistributor->id,
        'market_id' => $firstStore->market_id,
        'recipient_store_id' => $firstStore->id,
        'uptd_user_id' => $uptd->id,
        'distribution_date' => now()->toDateString(),
        'cartons' => 130,
    ]);

    Distribution::factory()->create([
        'distributor_id' => $secondDistributor->id,
        'market_id' => $secondStore->market_id,
        'recipient_store_id' => $secondStore->id,
        'uptd_user_id' => $uptd->id,
        'distribution_date' => now()->toDateString(),
        'cartons' => 250,
    ]);

    Distribution::factory()->create([
        'distributor_id' => $secondDistributor->id,
        'uptd_user_id' => $uptd->id,
        'distribution_date' => now()->toDateString(),
        'cartons' => 50,
    ]);

    Distribution::factory()->create([
        'distributor_id' => $secondDistributor->id,
        'market_id' => $secondStore->market_id,
        'recipient_store_id' => $secondStore->id,
        'uptd_user_id' => $uptd->id,
        'distribution_date' => now()->subMonthNoOverflow()->startOfMonth()->toDateString(),
        'cartons' => 900,
    ]);

    Distribution::factory()->draft()->create([
        'distributor_id' => $secondDistributor->id,
        'market_id' => $secondStore->market_id,
        'recipient_store_id' => $secondStore->id,
        'uptd_user_id' => $uptd->id,
        'distribution_date' => now()->toDateString(),
        'cartons' => 9999,
    ]);

    $this->actingAs(User::factory()->create());

    $this->get(route('admin.distribution.index'))
        ->assertOk()
        ->assertSeeInOrder(['Jumlah Laporan', 'Total Penyaluran', 'Jumlah Toko Penerima'])
        ->assertViewHas('summary', fn (array $summary): bool => $summary['reports'] === 4
            && $summary['cartons'] === 550
            && $summary['recipient_stores'] === 2);

    $this->get(route('admin.distribution.index', ['distributor' => $firstDistributor->id]))
        ->assertOk()
        ->assertViewHas('summary', fn (array $summary): bool => $summary['reports'] === 1
            && $summary['cartons'] === 120
            && $summary['recipient_stores'] === 1);
});

it('menampilkan bulan terpilih pada subtitle card jumlah laporan', function () {
    $this->travelTo('2026-11-15');

    $this->actingAs(User::factory()->create());

    $this->get(route('admin.distribution.index'))
        ->assertOk()
        ->assertSee('<p class="mt-1 text-xs text-slate-500">November 2026</p>', false)
        ->assertViewHas('month_label', 'November 2026');

    $this->get(route('admin.distribution.index', ['month' => '2026-10']))
        ->assertOk()
        ->assertSee('<p class="mt-1 text-xs text-slate-500">Oktober 2026</p>', false)
        ->assertViewHas('month_label', 'Oktober 2026');
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

it('tidak menampilkan laporan draft pada halaman laporan penyaluran admin', function () {
    $distributor = Distributor::factory()->create();
    $market = Market::factory()->create();

    Distribution::factory()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'distribution_date' => now()->toDateString(),
        'cartons' => 111,
    ]);

    Distribution::factory()->draft()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'distribution_date' => now()->toDateString(),
        'cartons' => 4321,
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.distribution.index'))
        ->assertOk()
        ->assertSee('111 karton')
        ->assertDontSee('4.321 karton');
});
