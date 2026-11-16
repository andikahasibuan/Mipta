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

it('menampilkan laporan penyaluran dari database dengan kolom data toko', function () {
    $uptd = User::factory()->create([
        'name' => 'Andi Pratama',
        'role' => Role::UptdPasar,
    ]);
    $distributor = Distributor::factory()->create(['name' => 'PT Sinar Pangan Nusantara']);
    $market = Market::factory()->create(['name' => 'Pasar Raya Padang']);
    $store = RecipientStore::factory()->create([
        'market_id' => $market->id,
        'name' => 'toko sinar jaya',
        'alamat' => 'jl. merdeka no. 5',
        'nib' => '1234567890123',
    ]);

    Distribution::factory()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'recipient_store_id' => $store->id,
        'uptd_user_id' => $uptd->id,
        'distribution_date' => now()->toDateString(),
        'cartons' => 250,
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.distribution.index'))
        ->assertOk()
        ->assertSee('Laporan Penyaluran')
        ->assertSeeInOrder(['NO', 'Tanggal Penyaluran', 'Nama Toko', 'Jumlah Disalurkan (Karton)', 'Alamat', 'Nomor NIB'])
        ->assertDontSee('>Distributor</th>', false)
        ->assertDontSee('>Pasar</th>', false)
        ->assertDontSee('>Keterangan</th>', false)
        ->assertDontSee('Tambah Data')
        ->assertSeeInOrder(['Toko Sinar Jaya', '250', 'Jl. Merdeka No. 5', '1234567890123'])
        ->assertSee(now()->format('d/m/Y'));
});

it('menampilkan data penyaluran terbaru pada nomor satu', function () {
    $latestStore = RecipientStore::factory()->create(['name' => 'Toko Paling Baru']);
    $olderStore = RecipientStore::factory()->create(['name' => 'Toko Lebih Lama']);

    Distribution::factory()->create([
        'recipient_store_id' => $olderStore->id,
        'distribution_date' => now()->subDays(2)->toDateString(),
    ]);

    Distribution::factory()->create([
        'recipient_store_id' => $latestStore->id,
        'distribution_date' => now()->toDateString(),
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.distribution.index'))
        ->assertOk()
        ->assertSeeInOrder([
            now()->format('d/m/Y'),
            'Toko Paling Baru',
            now()->subDays(2)->format('d/m/Y'),
            'Toko Lebih Lama',
        ]);
});

it('mengutamakan laporan yang paling baru dicatat saat tanggalnya sama', function () {
    $firstStore = RecipientStore::factory()->create(['name' => 'Toko Dicatat Awal']);
    $secondStore = RecipientStore::factory()->create(['name' => 'Toko Dicatat Akhir']);

    Distribution::factory()->create([
        'recipient_store_id' => $firstStore->id,
        'distribution_date' => now()->toDateString(),
    ]);

    Distribution::factory()->create([
        'recipient_store_id' => $secondStore->id,
        'distribution_date' => now()->toDateString(),
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.distribution.index'))
        ->assertOk()
        ->assertSeeInOrder(['Toko Dicatat Akhir', 'Toko Dicatat Awal']);
});

it('menampilkan tanda minus saat data toko tidak tersedia', function () {
    Distribution::factory()->create([
        'distribution_date' => now()->toDateString(),
        'recipient_store_id' => null,
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.distribution.index'))
        ->assertOk()
        ->assertSee('—');
});

it('menampilkan tombol ekspor nonaktif pada laporan penyaluran', function () {
    $response = $this->actingAs(User::factory()->create())
        ->get(route('admin.distribution.index'));

    $response->assertOk()
        ->assertSeeInOrder(['Ekspor Excel', 'Ekspor PDF']);

    expect(substr_count($response->getContent(), 'aria-disabled="true"'))->toBe(2);
});

it('tidak menampilkan aksi hapus pada laporan penyaluran admin', function () {
    Distribution::factory()->create([
        'distribution_date' => now()->toDateString(),
        'cartons' => 120,
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.distribution.index'))
        ->assertOk()
        ->assertDontSee('aria-label="Hapus"', false)
        ->assertDontSee('Aksi');
});

it('memfilter laporan penyaluran berdasarkan distributor, pasar, dan bulan', function () {
    $firstDistributor = Distributor::factory()->create(['name' => 'PT Alpha Niaga']);
    $secondDistributor = Distributor::factory()->create(['name' => 'PT Beta Pangan']);
    $firstMarket = Market::factory()->create(['name' => 'Pasar Alpha']);
    $secondMarket = Market::factory()->create(['name' => 'Pasar Beta']);
    $firstStore = RecipientStore::factory()->create([
        'market_id' => $firstMarket->id,
        'name' => 'Toko Filter Alpha',
    ]);
    $secondStore = RecipientStore::factory()->create([
        'market_id' => $secondMarket->id,
        'name' => 'Toko Filter Beta',
    ]);
    $previousStore = RecipientStore::factory()->create([
        'market_id' => $firstMarket->id,
        'name' => 'Toko Filter Bulan Lalu',
    ]);
    $uptd = User::factory()->create(['role' => Role::UptdPasar]);

    Distribution::factory()->create([
        'distributor_id' => $firstDistributor->id,
        'market_id' => $firstMarket->id,
        'recipient_store_id' => $firstStore->id,
        'uptd_user_id' => $uptd->id,
        'distribution_date' => now()->toDateString(),
        'cartons' => 100,
    ]);

    Distribution::factory()->create([
        'distributor_id' => $secondDistributor->id,
        'market_id' => $secondMarket->id,
        'recipient_store_id' => $secondStore->id,
        'uptd_user_id' => $uptd->id,
        'distribution_date' => now()->toDateString(),
        'cartons' => 200,
    ]);

    Distribution::factory()->create([
        'distributor_id' => $firstDistributor->id,
        'market_id' => $firstMarket->id,
        'recipient_store_id' => $previousStore->id,
        'uptd_user_id' => $uptd->id,
        'distribution_date' => now()->subMonthNoOverflow()->startOfMonth()->toDateString(),
        'cartons' => 300,
    ]);

    $admin = User::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.distribution.index', ['distributor' => $firstDistributor->id]))
        ->assertOk()
        ->assertSee('value="'.$firstDistributor->id.'" selected', false)
        ->assertSee('Toko Filter Alpha')
        ->assertDontSee('Toko Filter Beta')
        ->assertDontSee('Toko Filter Bulan Lalu');

    $this->get(route('admin.distribution.index', ['market' => $secondMarket->id]))
        ->assertOk()
        ->assertSee('value="'.$secondMarket->id.'" selected', false)
        ->assertSee('Toko Filter Beta')
        ->assertDontSee('Toko Filter Alpha');

    $this->get(route('admin.distribution.index', [
        'month' => now()->subMonthNoOverflow()->format('Y-m'),
    ]))
        ->assertOk()
        ->assertSee('Toko Filter Bulan Lalu')
        ->assertDontSee('Toko Filter Alpha');
});

it('mengabaikan bulan tidak valid dan kembali ke bulan berjalan', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('admin.distribution.index', ['month' => 'bukan-bulan']))
        ->assertOk()
        ->assertViewHas('filters', fn (array $filters): bool => $filters['month'] === now()->format('Y-m'))
        ->assertSee('value="'.now()->format('Y-m').'" selected', false);
});

it('mencari laporan penyaluran berdasarkan toko, distributor, dan pasar', function () {
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

    Distribution::factory()->create([
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
        ->assertSee('Toko Alpha')
        ->assertDontSee('Toko Beta');

    $this->get(route('admin.distribution.index', ['search' => 'PT Beta Pangan']))
        ->assertOk()
        ->assertSee('Toko Beta')
        ->assertDontSee('Toko Alpha');

    $this->get(route('admin.distribution.index', ['search' => 'Pasar Alpha']))
        ->assertOk()
        ->assertSee('Toko Alpha')
        ->assertDontSee('Toko Beta');
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

it('menampilkan keadaan kosong saat belum ada laporan penyaluran', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.distribution.index'))
        ->assertOk()
        ->assertSee('Belum ada laporan penyaluran pada periode ini');
});

it('menampilkan nama toko penerima dalam Title Case pada laporan penyaluran', function () {
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
        ->assertSee('Toko Atong')
        ->assertDontSee('TOKO ATONG');
});
